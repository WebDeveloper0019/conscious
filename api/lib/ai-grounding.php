<?php
declare(strict_types=1);

/** Public database facts stay separate from the language-model prompt. */
function puji_intent(string $message): string
{
    $text = mb_strtolower($message, 'UTF-8');
    if (preg_match('/\b(?:my|own)\s+(?:order|orders|shipment|parcel|delivery|refund)\b|\b(?:orders|track|tracking|shipment|parcel)\b|\border\s+(?:status|number|details)\b|\bwhere is my\b|ci-[0-9]{6}-[a-f0-9]{8}/u', $text)) return 'order';
    if (preg_match('/\b(event|events|exhibition|workshop|ticket|tickets|pop-up|popup|stall)\b/u', $text)) return 'event';
    if (preg_match('/\b(register|sign ?up|become|join|sell|partner|contact|support|help centre|return policy|shipping policy|about ci|mission|privacy|terms)\b/u', $text)) return 'site';
    if (preg_match('/\b(product|products|shop|buy|purchase|price|prices|stock|available|find|search|looking for|do you have|pottery|ceramic|jewellery|jewelry|textile|handloom|bamboo|gift|craft|painting|madhubani|woodwork|decor|cotton|pashmina|terracotta|necklace|mug|vase|basket)\b/u', $text)) return 'product';
    return 'general';
}

/** @return list<string> */
function puji_search_terms(string $message): array
{
    preg_match_all('/[\p{L}\p{N}]{3,}/u', mb_strtolower($message, 'UTF-8'), $matches);
    $ignored = array_fill_keys([
        'show','find','tell','about','what','which','where','when','with','have','some','your','please','more','how','you','for',
        'current','latest','live','available','products','product','events','event','shop','buy','purchase',
        'price','prices','stock','cost','much','there','their','want','looking','today','upcoming','tickets',
        'ticket','workshop','exhibition','order','orders','track','tracking','artisan','handmade','conscious',
    ], true);
    $terms = [];
    foreach ($matches[0] as $word) {
        if (!isset($ignored[$word]) && !in_array($word, $terms, true)) $terms[] = $word;
        if (count($terms) === 4) break;
    }
    return $terms;
}

function puji_base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/api/ai/chat.php'), 3)), '/');
}

function puji_money(float $value): string
{
    return '₹' . rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
}

function puji_like(string $term): string
{
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
}

/** Current public site routes, without fabricated policy details. */
function puji_site(string $message): array
{
    $text = mb_strtolower($message, 'UTF-8');
    $base = puji_base_path();
    if (preg_match('/\b(influencer|creator)\b/u', $text)) {
        return ['reply'=>'You can apply as a Conscious India creator here. The registration page explains the next steps.','results'=>[
            ['type'=>'link','title'=>'Creator registration','url'=>$base.'/join?type=influencer']]];
    }
    if (preg_match('/\b(sell|vendor|artisan|brand|partner|register|sign ?up|become|join)\b/u', $text)) {
        return ['reply'=>'Brands and artisans can start a vendor application here and choose the correct account type.','results'=>[
            ['type'=>'link','title'=>'Register as a partner','url'=>$base.'/join']]];
    }
    if (preg_match('/\b(return|refund)\b/u', $text)) {
        return ['reply'=>'Please read the current return terms before making a request. Signed-in customers can open their returns dashboard.','results'=>[
            ['type'=>'link','title'=>'Returns information','url'=>$base.'/returns'],
            ['type'=>'link','title'=>'My returns','url'=>$base.'/account/returns']]];
    }
    if (preg_match('/\b(shipping|delivery)\b/u', $text)) {
        return ['reply'=>'Shipping options and charges depend on the seller and delivery address. Check the policy and your checkout quote for the current amount.','results'=>[
            ['type'=>'link','title'=>'Shipping policy','url'=>$base.'/shipping-policy']]];
    }
    if (preg_match('/\b(about|mission)\b/u', $text)) {
        return ['reply'=>'Here is the story and mission of Conscious India.','results'=>[
            ['type'=>'link','title'=>'About CI','url'=>$base.'/about'],
            ['type'=>'link','title'=>'Our mission','url'=>$base.'/mission']]];
    }
    if (preg_match('/\b(privacy|terms)\b/u', $text)) {
        return ['reply'=>'You can read the current site policies here.','results'=>[
            ['type'=>'link','title'=>'Privacy policy','url'=>$base.'/privacy'],
            ['type'=>'link','title'=>'Terms of use','url'=>$base.'/terms']]];
    }
    return ['reply'=>'Our support team can help with your question.','results'=>[
        ['type'=>'link','title'=>'Help centre','url'=>$base.'/help'],
        ['type'=>'link','title'=>'Contact us','url'=>$base.'/contact']]];
}

/** @return array{reply:string,results:array} */
function puji_products(PDO $db, string $message): array
{
    $terms = puji_search_terms($message);
    $visibility = defined('APP_ENV') && APP_ENV === 'local'
        ? 'v.verification_status IN ("approved","demo_fixture")'
        : 'v.verification_status="approved"';
    $sql = 'SELECT p.id,p.published_snapshot_json,c.name AS category_name,v.display_name AS vendor_name,
        (SELECT f.id FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id
         WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL
         ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) AS image_id,
        (SELECT COALESCE(SUM(CASE WHEN i.stock_quantity>i.reserved_quantity THEN i.stock_quantity-i.reserved_quantity ELSE 0 END),0)
         FROM product_variants pv JOIN inventory_levels i ON i.variant_id=pv.id
         WHERE pv.product_id=p.id AND pv.is_active=1) AS available_quantity
        FROM products p JOIN vendors v ON v.id=p.vendor_id LEFT JOIN categories c ON c.id=p.category_id
        WHERE p.published_snapshot_json IS NOT NULL AND p.status NOT IN ("suspended","archived")
          AND v.status="active" AND ' . $visibility;
    $params = [];
    if ($terms !== []) {
        $conditions = [];
        foreach ($terms as $index => $term) {
            $conditions[] = "(p.published_snapshot_json LIKE :snapshot{$index} ESCAPE '!' OR c.name LIKE :category{$index} ESCAPE '!' OR v.display_name LIKE :vendor{$index} ESCAPE '!')";
            $params["snapshot{$index}"] = $params["category{$index}"] = $params["vendor{$index}"] = puji_like($term);
        }
        $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
    }
    $sql .= ' ORDER BY p.published_at DESC,p.created_at DESC LIMIT 24';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $matches = [];
    foreach ($rows as $index => $row) {
        $snapshot = json_decode((string)$row['published_snapshot_json'], true);
        if (!is_array($snapshot) || empty($snapshot['name'])) continue;
        $name = mb_substr((string)$snapshot['name'], 0, 180);
        $category = mb_substr((string)($row['category_name'] ?? ''), 0, 100);
        $vendor = mb_substr((string)($row['vendor_name'] ?? ''), 0, 120);
        $material = mb_substr((string)($snapshot['material'] ?? ''), 0, 100);
        $score = 0;
        foreach ($terms as $term) {
            if (mb_stripos($name, $term) !== false) $score += 4;
            if (mb_stripos($category, $term) !== false) $score += 3;
            if (mb_stripos($material, $term) !== false) $score += 2;
            if (mb_stripos($vendor, $term) !== false) $score++;
        }
        $quantity = max(0, (int)$row['available_quantity']);
        $price = max(0, (float)($snapshot['selling_price'] ?? 0));
        $base = puji_base_path();
        $matches[] = [
            'score' => $score, 'index' => $index,
            'result' => [
                'type' => 'product', 'title' => $name,
                'subtitle' => implode(' · ', array_filter([$category, $vendor])),
                'meta' => puji_money($price) . ' · ' . ($quantity > 0 ? $quantity . ' currently available' : 'Out of stock'),
                'url' => $base . '/product-detail.php?id=' . rawurlencode((string)$row['id']),
                'image' => $row['image_id'] ? $base . '/api/files.php?id=' . rawurlencode((string)$row['image_id']) : '',
            ],
        ];
    }
    usort($matches, static fn(array $a, array $b): int => $b['score'] <=> $a['score'] ?: $a['index'] <=> $b['index']);
    $results = array_column(array_slice($matches, 0, 5), 'result');
    return $results === []
        ? ['reply' => 'I could not find a currently published product matching that request. Please check the marketplace for the latest listings.', 'results' => [['type'=>'link','title'=>'Browse the marketplace','url'=>puji_base_path().'/shop']]]
        : ['reply' => 'These are current published listings from our marketplace. Stock and price are checked again at checkout.', 'results' => $results];
}

function puji_event_date(string $utc): string
{
    try {
        return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('j M Y, g:i A');
    } catch (Throwable) {
        return 'Date to be confirmed';
    }
}

/** @return array{reply:string,results:array} */
function puji_events(PDO $db, string $message): array
{
    $terms = puji_search_terms($message);
    $now = gmdate('Y-m-d H:i:s');
    $sql = 'SELECT e.id,e.title,e.event_type,e.venue_type,e.venue_name,e.starts_at,e.booking_opens_at,e.booking_closes_at,
        (SELECT f.id FROM event_media em JOIN file_assets f ON f.id=em.file_asset_id
         WHERE em.event_id=e.id AND f.visibility="public" AND f.scan_status="clean" AND f.deleted_at IS NULL
         ORDER BY em.sort_order LIMIT 1) AS image_id
        FROM events e WHERE e.status="published" AND e.ends_at>=:now';
    $params = ['now' => $now];
    if ($terms !== []) {
        $conditions = [];
        foreach ($terms as $index => $term) {
            $conditions[] = "(e.title LIKE :title{$index} ESCAPE '!' OR e.short_description LIKE :description{$index} ESCAPE '!' OR e.event_type LIKE :type{$index} ESCAPE '!' OR e.venue_name LIKE :venue{$index} ESCAPE '!')";
            $params["title{$index}"] = $params["description{$index}"] = $params["type{$index}"] = $params["venue{$index}"] = puji_like($term);
        }
        $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
    }
    $sql .= ' ORDER BY e.starts_at LIMIT 5';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = [];
    $ticket = $db->prepare('SELECT price,capacity,sold_quantity,reserved_quantity FROM event_ticket_types
        WHERE event_id=:event AND status="active" AND sales_start_at<=:now_start AND sales_end_at>=:now_end
        ORDER BY price LIMIT 10');
    foreach ($stmt->fetchAll() as $row) {
        $ticket->execute(['event'=>$row['id'],'now_start'=>$now,'now_end'=>$now]);
        $available = [];
        foreach ($ticket->fetchAll() as $option) {
            if ((int)$option['capacity'] > (int)$option['sold_quantity'] + (int)$option['reserved_quantity']) $available[] = (float)$option['price'];
        }
        $bookingOpen = (string)$row['booking_opens_at'] <= $now && (string)$row['booking_closes_at'] >= $now;
        $booking = !$bookingOpen || $available === [] ? 'Booking unavailable' : (min($available) <= 0 ? 'Free tickets available' : 'Tickets from ' . puji_money(min($available)));
        $base = puji_base_path();
        $results[] = [
            'type' => 'event', 'title' => mb_substr((string)$row['title'], 0, 180),
            'subtitle' => puji_event_date((string)$row['starts_at']) . ' · ' . ($row['venue_type'] === 'online' ? 'Online' : (string)($row['venue_name'] ?: 'Venue to be announced')),
            'meta' => $booking,
            'url' => $base . '/event-detail-live.php?id=' . rawurlencode((string)$row['id']),
            'image' => $row['image_id'] ? $base . '/api/files.php?id=' . rawurlencode((string)$row['image_id']) : '',
        ];
    }
    return $results === []
        ? ['reply'=>'I could not find a currently published upcoming event matching that request. Please check the events page for updates.','results'=>[['type'=>'link','title'=>'Browse events','url'=>puji_base_path().'/events']]]
        : ['reply'=>'These are the currently published events. Open an event to confirm the latest booking details.','results'=>$results];
}

/** Private order facts are returned directly to their owner and never sent to the AI provider. */
function puji_orders(PDO $db, string $userId, string $message): array
{
    $base = puji_base_path();
    $params = ['user'=>$userId];
    $sql = 'SELECT id,order_number,status,created_at FROM orders WHERE user_id=:user';
    if (preg_match('/CI-[0-9]{6}-[A-F0-9]{8}/i', $message, $match)) {
        $sql .= ' AND order_number=:number';
        $params['number'] = strtoupper($match[0]);
    }
    $sql .= ' ORDER BY created_at DESC,id DESC LIMIT 3';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $shipments = $db->prepare('SELECT sp.status,sp.courier_name,sp.tracking_reference,sp.expected_delivery_date
        FROM shipment_packages sp JOIN vendor_order_groups vg ON vg.id=sp.vendor_group_id
        WHERE vg.order_id=:order ORDER BY sp.created_at DESC LIMIT 3');
    $results = [];
    foreach ($stmt->fetchAll() as $row) {
        $shipments->execute(['order'=>$row['id']]);
        $status = 'Order ' . ucwords(str_replace('_', ' ', (string)$row['status']));
        $packageLines = [];
        foreach ($shipments->fetchAll() as $package) {
            $line = ucwords(str_replace('_', ' ', (string)$package['status']));
            if ($package['courier_name']) $line .= ' · ' . mb_substr((string)$package['courier_name'], 0, 80);
            if ($package['tracking_reference']) $line .= ' · Tracking ' . mb_substr((string)$package['tracking_reference'], 0, 80);
            $packageLines[] = $line;
        }
        $results[] = [
            'type'=>'order', 'title'=>(string)$row['order_number'],
            'subtitle'=>puji_event_date((string)$row['created_at']),
            'meta'=>$status . ($packageLines ? ' · ' . implode(' / ', $packageLines) : ''),
            'url'=>$base . '/account/orders?orderNumber=' . rawurlencode((string)$row['order_number']),
            'image'=>'',
        ];
    }
    return $results === []
        ? ['reply'=>'I could not find that order in your account. You can check your full order history in your dashboard.','results'=>[['type'=>'link','title'=>'Open my orders','url'=>$base.'/account/orders']]]
        : ['reply'=>'Here is the latest status recorded for your order' . (count($results) > 1 ? 's' : '') . '. Open the order for full details.','results'=>$results];
}
