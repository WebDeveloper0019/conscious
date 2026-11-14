<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || !extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "Run with PHP CLI and pdo_sqlite.\n");
    exit(1);
}
define('APP_ENV', 'production');
$_SERVER['SCRIPT_NAME'] = '/api/ai/chat.php';
require __DIR__ . '/../api/lib/ai-grounding.php';

function check(bool $condition, string $label): void {
    if (!$condition) throw new RuntimeException('FAIL ' . $label);
    echo 'PASS ', $label, PHP_EOL;
}

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
foreach ([
    'CREATE TABLE vendors(id TEXT,status TEXT,verification_status TEXT,display_name TEXT)',
    'CREATE TABLE categories(id INTEGER,name TEXT)',
    'CREATE TABLE products(id TEXT,vendor_id TEXT,category_id INTEGER,published_snapshot_json TEXT,status TEXT,published_at TEXT,created_at TEXT)',
    'CREATE TABLE file_assets(id TEXT,visibility TEXT,scan_status TEXT,deleted_at TEXT)',
    'CREATE TABLE product_images(product_id TEXT,file_asset_id TEXT,is_primary INTEGER,sort_order INTEGER)',
    'CREATE TABLE product_variants(id TEXT,product_id TEXT,is_active INTEGER)',
    'CREATE TABLE inventory_levels(variant_id TEXT,stock_quantity INTEGER,reserved_quantity INTEGER)',
    'CREATE TABLE events(id TEXT,title TEXT,event_type TEXT,short_description TEXT,venue_type TEXT,venue_name TEXT,starts_at TEXT,ends_at TEXT,booking_opens_at TEXT,booking_closes_at TEXT,status TEXT)',
    'CREATE TABLE event_media(event_id TEXT,file_asset_id TEXT,sort_order INTEGER)',
    'CREATE TABLE event_ticket_types(event_id TEXT,status TEXT,sales_start_at TEXT,sales_end_at TEXT,price REAL,capacity INTEGER,sold_quantity INTEGER,reserved_quantity INTEGER)',
    'CREATE TABLE orders(id INTEGER,user_id TEXT,order_number TEXT,status TEXT,created_at TEXT)',
    'CREATE TABLE vendor_order_groups(id TEXT,order_id INTEGER)',
    'CREATE TABLE shipment_packages(id TEXT,vendor_group_id TEXT,status TEXT,courier_name TEXT,tracking_reference TEXT,expected_delivery_date TEXT,created_at TEXT)',
] as $sql) $db->exec($sql);

$db->exec("INSERT INTO vendors VALUES ('v1','active','approved','Kaveri Crafts'),('v2','active','pending','Unapproved Shop')");
$db->exec("INSERT INTO categories VALUES (1,'Pottery & Ceramics')");
$snapshot = json_encode(['name'=>'Blue Pottery Mug','selling_price'=>1280,'material'=>'Ceramic'], JSON_THROW_ON_ERROR);
$hidden = json_encode(['name'=>'Secret Pottery Item','selling_price'=>999], JSON_THROW_ON_ERROR);
$stmt = $db->prepare('INSERT INTO products VALUES (?,?,?,?,?,?,?)');
$stmt->execute(['p1','v1',1,$snapshot,'approved','2026-11-14 00:00:00','2026-11-14 00:00:00']);
$stmt->execute(['p2','v1',1,null,'draft',null,'2026-11-14 00:00:00']);
$stmt->execute(['p3','v2',1,$hidden,'approved','2026-11-14 00:00:00','2026-11-14 00:00:00']);
$db->exec("INSERT INTO product_variants VALUES ('pv1','p1',1)");
$db->exec("INSERT INTO inventory_levels VALUES ('pv1',8,2)");
$db->exec("INSERT INTO file_assets VALUES ('image1','public','clean',NULL),('image2','public','pending',NULL)");
$db->exec("INSERT INTO product_images VALUES ('p1','image1',1,0),('p1','image2',0,1)");

check(puji_intent('Show available products') === 'product', 'product intent');
check(puji_intent('Find Madhubani work') === 'product', 'product names can trigger database lookup');
check(puji_intent('Show upcoming events') === 'event', 'event intent');
check(puji_intent('How do I track my order?') === 'order', 'order intent');
check(puji_intent('How do I order a gift?') === 'product', 'shopping question does not reveal order history');
check(puji_intent('How can I sell as an artisan?') === 'site', 'partner navigation intent');
check(puji_intent('Tell me your story') === 'general', 'general AI intent');
check(puji_site('How can I sell as an artisan?')['results'][0]['url'] === '/portals/modules/vendor/auth/register.php', 'site guidance uses the current vendor registration route');
$products = puji_products($db, 'Show available pottery products');
check(count($products['results']) === 1 && $products['results'][0]['title'] === 'Blue Pottery Mug', 'only approved published products appear');
check(str_contains($products['results'][0]['meta'], '6 currently available') && str_contains($products['results'][0]['meta'], '₹1,280'), 'published price and live unreserved stock are shown');
check($products['results'][0]['image'] === '/api/files.php?id=image1', 'only a clean public image appears');
$secretSearch = puji_products($db, 'Secret Pottery Item');
check(!str_contains(json_encode($secretSearch, JSON_THROW_ON_ERROR), 'Secret Pottery Item'), 'pending vendor inventory is not exposed');

$past = gmdate('Y-m-d H:i:s', time()-86400);
$future = gmdate('Y-m-d H:i:s', time()+86400);
$later = gmdate('Y-m-d H:i:s', time()+172800);
$stmt = $db->prepare('INSERT INTO events VALUES (?,?,?,?,?,?,?,?,?,?,?)');
$stmt->execute(['e1','Natural Dye Workshop','workshop','Learn natural dyes','physical','Delhi Craft Hall',$future,$later,$past,$later,'published']);
$stmt->execute(['e2','Private Event','workshop','Hidden','physical','Delhi',$future,$later,$past,$later,'draft']);
$ticket = $db->prepare('INSERT INTO event_ticket_types VALUES (?,?,?,?,?,?,?,?)');
$ticket->execute(['e1','active',$past,$later,500,10,2,1]);
$events = puji_events($db, 'Show upcoming dye workshops');
check(count($events['results']) === 1 && $events['results'][0]['title'] === 'Natural Dye Workshop', 'only published future events appear');
check(str_contains($events['results'][0]['meta'], '₹500'), 'bookable ticket price comes from current capacity');
check(puji_events($db, 'Events in Delhi')['results'][0]['title'] === 'Natural Dye Workshop', 'event search includes venue city');

$db->exec("INSERT INTO orders VALUES (1,'customer-a','CI-261114-ABCDEF12','confirmed','2026-11-14 00:00:00'),(2,'customer-b','CI-261114-12345678','shipped','2026-11-14 00:00:00')");
$db->exec("INSERT INTO vendor_order_groups VALUES ('g1',1),('g2',2)");
$db->exec("INSERT INTO shipment_packages VALUES ('s1','g1','dispatched','Manual Courier','TRACK-A',NULL,'2026-11-14 00:00:00'),('s2','g2','delivered','Other Courier','TRACK-B',NULL,'2026-11-14 00:00:00')");
$orders = puji_orders($db, 'customer-a', 'Track my order');
check(count($orders['results']) === 1 && str_contains($orders['results'][0]['meta'], 'TRACK-A'), 'customer sees their own shipment status');
check(!str_contains(json_encode($orders, JSON_THROW_ON_ERROR), 'TRACK-B'), 'other customer shipment is absent');
$foreign = puji_orders($db, 'customer-a', 'Track CI-261114-12345678');
check($foreign['results'][0]['type'] === 'link', 'another customer order number is not disclosed');
$_SERVER['SCRIPT_NAME'] = '/conscious/api/ai/chat.php';
check(puji_base_path() === '/conscious', 'local base path is preserved for links');
