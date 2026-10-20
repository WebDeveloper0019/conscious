<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/commerce.php';
require_once __DIR__.'/../lib/india.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$vendor = require_vendor(true)['vendor'];
$state = canonical_india_state((string)($_GET['state'] ?? ''));
$city = trim((string)($_GET['city'] ?? ''));
$pincode = trim((string)($_GET['pincode'] ?? ''));
$orderValue = filter_var($_GET['order_value'] ?? null, FILTER_VALIDATE_FLOAT);
$weight = filter_var($_GET['weight_grams'] ?? null, FILTER_VALIDATE_INT);

if ($state === null || mb_strlen($city) < 2 || mb_strlen($city) > 100
    || !preg_match('/^[1-9][0-9]{5}$/', $pincode)
    || $orderValue === false || $orderValue < 0 || $orderValue > 10000000
    || $weight === false || $weight < 1 || $weight > 500000) {
    json_response(422, ['success' => false, 'message' => 'Enter a valid Indian state, city, PIN, order value and package weight.']);
}

$quote = shipping_for_vendor($vendor['id'], ['state' => $state, 'city' => $city, 'pincode' => $pincode], (float)$orderValue, (int)$weight);
json_response(200, ['success' => true, 'quote' => $quote]);
