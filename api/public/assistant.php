<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}
if (rate_limit('public-assistant|' . request_ip(), 60, 60)) {
    header('Retry-After: 60');
    json_response(429, ['success' => false, 'message' => 'Too many searches. Please wait a minute and try again.']);
}

function assistant_text(string $key, int $maximum = 100): string {
    $value = trim((string)($_GET[$key] ?? ''));
    if (mb_strlen($value) > $maximum) json_response(422, ['success' => false, 'message' => 'A search filter is too long.']);
    return $value;
}

function assistant_number(string $key): ?float {
    if (!isset($_GET[$key]) || $_GET[$key] === '') return null;
    $value = filter_var($_GET[$key], FILTER_VALIDATE_FLOAT);
    if ($value === false || $value < 0 || $value > 10000000) json_response(422, ['success' => false, 'message' => 'Enter a valid price.']);
    return (float)$value;
}

function assistant_date(string $key): string {
    $value = assistant_text($key, 10);
    if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
        json_response(422, ['success' => false, 'message' => 'Enter a valid date.']);
    }
    return $value;
}

function assistant_normalize(string $value): string {
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    return trim((string)preg_replace('/\s+/', ' ', strtolower((string)preg_replace('/[^a-zA-Z0-9]+/', ' ', $ascii))));
}

function assistant_score(string $query, string $document): int {
    $query = assistant_normalize($query);
    $document = assistant_normalize($document);
    if ($query === '') return 1;
    if (str_contains($document, $query)) return 120 - min(40, (int)(strlen($document) / 30));
    $documentWords = array_values(array_filter(explode(' ', $document)));
    $score = 0;
    foreach (array_values(array_filter(explode(' ', $query))) as $term) {
        $best = 0.0;
        foreach ($documentWords as $word) {
            if ($word === $term) { $best = 1.0; break; }
            $longest = max(strlen($term), strlen($word));
            if ($longest < 3) continue;
            $similarity = 1 - (levenshtein($term, $word) / $longest);
            if ($similarity > $best) $best = $similarity;
        }
        if ($best < (strlen($term) <= 4 ? 0.72 : 0.64)) return 0;
        $score += (int)round($best * 25);
    }
    return $score;
}

function assistant_public_image(?string $id): ?string {
    return $id ? '../files.php?id=' . rawurlencode($id) : null;
}

$kind = assistant_text('kind', 20);
$query = assistant_text('q', 80);
$limit = max(1, min(8, (int)($_GET['limit'] ?? 6)));
$db = get_db();

if ($kind === 'products') {
    $category = assistant_text('category', 80);
    $vendor = assistant_text('vendor', 100);
    $origin = assistant_text('origin', 80);
    $availability = assistant_text('availability', 20);
    $minimum = assistant_number('min_price');
    $maximum = assistant_number('max_price');
    if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
        json_response(422, ['success' => false, 'message' => 'Minimum price cannot exceed maximum price.']);
    }
    if (!in_array($availability, ['', 'in_stock'], true)) {
        json_response(422, ['success' => false, 'message' => 'Unsupported availability filter.']);
    }

    $vendorApproval = APP_ENV === 'local'
        ? 'v.verification_status IN ("approved","demo_fixture")'
        : 'v.verification_status="approved"';
    $sql = 'SELECT p.id,p.name,p.slug,p.short_description,p.selling_price,p.mrp,p.material,p.origin_state,
                   p.published_at,p.published_snapshot_json,c.name AS category_name,
                   v.display_name AS vendor_name,v.vendor_type,v.verification_status,
                   (SELECT va.city FROM vendor_addresses va WHERE va.vendor_id=v.id ORDER BY va.is_primary DESC,va.created_at LIMIT 1) vendor_city,
                   (SELECT va.state FROM vendor_addresses va WHERE va.vendor_id=v.id ORDER BY va.is_primary DESC,va.created_at LIMIT 1) vendor_state,
                   (SELECT f.id FROM product_images pi JOIN file_assets f ON f.id=pi.file_asset_id
                    WHERE pi.product_id=p.id AND f.visibility="public" AND f.scan_status="clean"
                      AND f.deleted_at IS NULL ORDER BY pi.is_primary DESC,pi.sort_order LIMIT 1) image_id,
                   COALESCE((SELECT SUM(GREATEST(i.stock_quantity-i.reserved_quantity,0))
                    FROM product_variants pv JOIN inventory_levels i ON i.variant_id=pv.id
                    WHERE pv.product_id=p.id AND pv.is_active=1),0) available_quantity
            FROM products p JOIN vendors v ON v.id=p.vendor_id
            LEFT JOIN categories c ON c.id=p.category_id
            WHERE p.published_snapshot_json IS NOT NULL
              AND p.status NOT IN ("suspended","archived") AND v.status="active" AND ' . $vendorApproval;
    $params = [];
    if ($category !== '') { $sql .= ' AND c.name LIKE :category'; $params['category'] = '%' . $category . '%'; }
    if ($vendor !== '') { $sql .= ' AND v.display_name LIKE :vendor'; $params['vendor'] = '%' . $vendor . '%'; }
    if ($origin !== '') {
        $sql .= ' AND (p.origin_state LIKE :origin OR EXISTS (SELECT 1 FROM vendor_addresses va WHERE va.vendor_id=v.id AND (va.city LIKE :origin_city OR va.state LIKE :origin_state)))';
        $params['origin'] = '%' . $origin . '%';
        $params['origin_city'] = '%' . $origin . '%';
        $params['origin_state'] = '%' . $origin . '%';
    }
    $sql .= ' ORDER BY p.published_at DESC,p.created_at DESC LIMIT 240';
    $statement = $db->prepare($sql);
    $statement->execute($params);
    $matches = [];
    foreach ($statement->fetchAll() as $row) {
        $snapshot = json_decode((string)$row['published_snapshot_json'], true) ?: [];
        $row = array_replace($row, $snapshot);
        $price = (float)$row['selling_price'];
        $stock = max(0, (int)$row['available_quantity']);
        if ($minimum !== null && $price < $minimum) continue;
        if ($maximum !== null && $price > $maximum) continue;
        if ($availability === 'in_stock' && $stock < 1) continue;
        $score = assistant_score($query, implode(' ', [
            $row['name'] ?? '', $row['short_description'] ?? '', $row['category_name'] ?? '',
            $row['vendor_name'] ?? '', $row['material'] ?? '', $row['origin_state'] ?? '',
            $row['vendor_city'] ?? '', $row['vendor_state'] ?? '', $row['tags_json'] ?? ''
        ]));
        if ($score < 1) continue;
        $matches[] = ['score' => $score, 'published' => (string)($row['published_at'] ?? ''), 'item' => [
            'id' => (string)$row['id'],
            'name' => (string)$row['name'],
            'description' => (string)($row['short_description'] ?? ''),
            'category' => (string)($row['category_name'] ?? ''),
            'price' => $price,
            'mrp' => (float)($row['mrp'] ?? 0),
            'vendor' => (string)$row['vendor_name'],
            'vendor_type' => (string)$row['vendor_type'],
            'origin' => (string)($row['origin_state'] ?? ''),
            'maker_location' => implode(', ', array_values(array_filter([$row['vendor_city'] ?? '', $row['vendor_state'] ?? '']))),
            'available_quantity' => $stock,
            'availability' => $stock > 0 ? 'In stock' : 'Currently unavailable',
            'image_url' => assistant_public_image($row['image_id'] ?? null),
            'url' => 'product-detail.html?id=' . rawurlencode((string)$row['id']),
        ]];
    }
    usort($matches, fn(array $a, array $b): int => $b['score'] <=> $a['score'] ?: strcmp($b['published'], $a['published']));
    json_response(200, [
        'success' => true,
        'kind' => 'products',
        'items' => array_column(array_slice($matches, 0, $limit), 'item'),
        'match_count' => count($matches),
    ]);
}

if ($kind === 'events') {
    $location = assistant_text('location', 100);
    $type = assistant_text('type', 50);
    $from = assistant_date('date_from');
    $to = assistant_date('date_to');
    if ($from !== '' && $to !== '' && $from > $to) json_response(422, ['success' => false, 'message' => 'Start date cannot be after end date.']);
    $sql = 'SELECT e.id,e.title,e.slug,e.event_type,e.short_description,e.venue_type,e.venue_name,e.address_json,
                   e.starts_at,e.ends_at,e.timezone,h.display_name AS host_name,
                   COALESCE(MIN(t.price),0) AS from_price,
                   COALESCE(SUM(GREATEST(t.capacity-t.sold_quantity-t.reserved_quantity,0)),0) AS available_quantity,
                   (SELECT f.id FROM event_media em JOIN file_assets f ON f.id=em.file_asset_id
                    WHERE em.event_id=e.id AND f.visibility="public" AND f.scan_status="clean"
                      AND f.deleted_at IS NULL ORDER BY em.sort_order LIMIT 1) image_id
            FROM events e JOIN event_hosts h ON h.id=e.host_id
            LEFT JOIN event_ticket_types t ON t.event_id=e.id AND t.status="active"
            WHERE e.status="published" AND e.ends_at>=UTC_TIMESTAMP()';
    $params = [];
    if ($type !== '') { $sql .= ' AND e.event_type=:type'; $params['type'] = $type; }
    if ($from !== '') { $sql .= ' AND e.starts_at>=:date_from'; $params['date_from'] = $from . ' 00:00:00'; }
    if ($to !== '') { $sql .= ' AND e.starts_at<=:date_to'; $params['date_to'] = $to . ' 23:59:59'; }
    $sql .= ' GROUP BY e.id ORDER BY e.starts_at LIMIT 160';
    $statement = $db->prepare($sql);
    $statement->execute($params);
    $matches = [];
    foreach ($statement->fetchAll() as $row) {
        $address = json_decode((string)($row['address_json'] ?? ''), true) ?: [];
        $place = implode(', ', array_values(array_filter([
            $row['venue_name'] ?? '', $address['city'] ?? '', $address['state'] ?? ''
        ])));
        if ($location !== '' && assistant_score($location, $place) < 1) continue;
        $score = assistant_score($query, implode(' ', [
            $row['title'], $row['event_type'], $row['short_description'], $row['host_name'], $place
        ]));
        if ($score < 1) continue;
        $matches[] = ['score' => $score, 'starts' => (string)$row['starts_at'], 'item' => [
            'id' => (string)$row['id'],
            'title' => (string)$row['title'],
            'description' => (string)$row['short_description'],
            'event_type' => (string)$row['event_type'],
            'venue_type' => (string)$row['venue_type'],
            'location' => $place !== '' ? $place : ((string)$row['venue_type'] === 'online' ? 'Online' : 'Venue to be announced'),
            'starts_at' => (string)$row['starts_at'],
            'ends_at' => (string)$row['ends_at'],
            'timezone' => (string)$row['timezone'],
            'host' => (string)$row['host_name'],
            'from_price' => (float)$row['from_price'],
            'available_quantity' => max(0, (int)$row['available_quantity']),
            'image_url' => assistant_public_image($row['image_id'] ?? null),
            'url' => 'event-detail-live.php?id=' . rawurlencode((string)$row['id']),
        ]];
    }
    usort($matches, fn(array $a, array $b): int => $b['score'] <=> $a['score'] ?: strcmp($a['starts'], $b['starts']));
    json_response(200, [
        'success' => true,
        'kind' => 'events',
        'items' => array_column(array_slice($matches, 0, $limit), 'item'),
        'match_count' => count($matches),
    ]);
}

json_response(422, ['success' => false, 'message' => 'Choose product or event discovery.']);
