<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/_ai_client.php';

header('Content-Type: application/json; charset=utf-8');

// Simple IP-based rate limiting: 20 messages per hour per IP
$ip       = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . (AI_RATE_LIMIT_SALT));
$rlFile   = sys_get_temp_dir() . '/ci_chat_rl_' . $ip . '.json';
$now      = time();
$window   = 3600;
$maxReqs  = 20;

$rl = (is_file($rlFile) && is_readable($rlFile))
    ? (json_decode(file_get_contents($rlFile), true) ?? [])
    : [];

if (empty($rl['window_start']) || ($now - $rl['window_start']) > $window) {
    $rl = ['count' => 0, 'window_start' => $now];
}

if ($rl['count'] >= $maxReqs) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many messages. Please wait a moment before trying again.']);
    exit;
}

$rl['count']++;
file_put_contents($rlFile, json_encode($rl), LOCK_EX);

$body           = json_decode(file_get_contents('php://input'), true) ?? [];
$message        = trim((string)($body['message'] ?? ''));
$productContext = trim((string)($body['product_context'] ?? ''));

if ($message === '' || mb_strlen($message) > 500) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid message']);
    exit;
}

$system = "You are a helpful shopping assistant for Conscious Indi, India's conscious living marketplace. " .
    "You help customers discover handcrafted, sustainable, and artisan-made products. " .
    "Be warm, concise, and helpful. Answer in the same language the customer writes in. " .
    "If asked about a specific product, use only the product context provided — never invent details. " .
    "If you cannot answer something, say so honestly and suggest the customer contact support. " .
    "Never make up prices, stock levels, delivery times, or return policies. " .
    "If the question is unrelated to shopping or products on this platform, politely redirect.";

if ($productContext !== '') {
    $system .= "\n\nProduct context:\n" . mb_substr($productContext, 0, 800);
}

$prompt = $system . "\n\nCustomer: " . $message . "\n\nAssistant:";
$result = ai_complete($prompt, 256);

if ($result === null) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'AI is unavailable right now. For help, please use the Contact Us page.',
    ]);
    exit;
}

echo json_encode(['success' => true, 'reply' => $result]);
