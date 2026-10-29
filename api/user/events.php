<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/authorization.php';

send_cors_headers();

// Check if the user is logged in
$principal = require_principal(['customer']);
$userId = $principal['id'];

$db = get_db();

// Fetch all published events
$stmt = $db->query("SELECT * FROM events WHERE status='published' AND ends_at >= UTC_TIMESTAMP() ORDER BY starts_at ASC");
$events = $stmt->fetchAll();

// Return the real events to the frontend
json_response(200, [
    'success' => true, 
    'events' => $events
]); 