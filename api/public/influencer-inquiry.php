<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(405, ['success' => false, 'message' => 'Method not allowed.']);
}

// Rate limit to prevent spam (max 5 inquiries per 10 mins per IP)
if (rate_limit('inquiry|' . request_ip(), 5, 600)) {
    json_response(429, ['success' => false, 'message' => 'Too many inquiries. Please try again later.']);
}

$body = get_json_body();
$handle = trim($body['handle'] ?? '');
$name = trim($body['name'] ?? '');
$phone = trim($body['phone'] ?? '');
$email = trim($body['email'] ?? '');
$package = trim($body['package'] ?? '');
$message = trim($body['message'] ?? '');

if (!$handle || !$name || !$phone || !$email || !$package) {
    json_response(422, ['success' => false, 'message' => 'Please fill in all required fields.']);
}

if (!is_valid_email($email)) {
    json_response(422, ['success' => false, 'message' => 'Please enter a valid email address.']);
}

$db = get_db();

// Find the influencer by handle
$stmt = $db->prepare('SELECT id FROM influencers WHERE handle = :h AND status IN ("active", "pending_review") LIMIT 1');
$stmt->execute(['h' => strtolower($handle)]);
$influencerId = $stmt->fetchColumn();

if (!$influencerId) {
    json_response(404, ['success' => false, 'message' => 'Creator not found.']);
}

// Save the inquiry
$db->prepare('INSERT INTO influencer_inquiries (id, influencer_id, sender_name, sender_email, sender_phone, package_name, message, status, created_at) 
              VALUES (:id, :inf, :name, :email, :phone, :pkg, :msg, "new", UTC_TIMESTAMP())')
   ->execute([
       'id' => uuid_string(),
       'inf' => $influencerId,
       'name' => $name,
       'email' => $email,
       'phone' => $phone,
       'pkg' => $package,
       'msg' => $message
   ]);

// Optional: Insert a notification for the creator
try {
    $db->prepare('INSERT INTO influencer_notifications (id, influencer_id, title, message, read_at, created_at) 
                  VALUES (:id, :inf, "New Partnership Inquiry", :msg, NULL, UTC_TIMESTAMP())')
       ->execute([
           'id' => uuid_string(), 
           'inf' => $influencerId, 
           'msg' => "$name sent an inquiry for $package."
       ]);
} catch(Throwable $e) {}

json_response(201, ['success' => true, 'message' => 'Your inquiry has been sent to the creator!']);