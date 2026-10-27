<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/_ai_client.php';

header('Content-Type: application/json; charset=utf-8');

// Must be a logged-in vendor or admin
session_name(AUTH_COOKIE);
session_start();
$role = $_SESSION['role'] ?? '';
if (empty($_SESSION['user_id']) || !in_array($role, ['vendor', 'artisan', 'admin'], true)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorised']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$text   = trim((string)($body['text'] ?? ''));
$action = trim((string)($body['action'] ?? ''));

$allowed_actions = [
    'improve',
    'shorten',
    'translate_hindi',
    'translate_english',
    'suggest_tags',
    'generate_description',
];

if ($text === '' || !in_array($action, $allowed_actions, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

if (mb_strlen($text) > 3000) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Text too long. Maximum 3000 characters.']);
    exit;
}

$prompts = [
    'improve' =>
        "You are a copywriter for an Indian sustainable marketplace called Conscious Indi. " .
        "Improve the following vendor or artisan story to be warm, authentic, and compelling. " .
        "Keep it in the same language as the input. Do not add fictional details. " .
        "Return only the improved text, nothing else.\n\nText:\n{TEXT}",

    'shorten' =>
        "Shorten the following text to under 100 words while keeping the most important details. " .
        "Return only the shortened text, nothing else.\n\nText:\n{TEXT}",

    'translate_hindi' =>
        "Translate the following text to Hindi. " .
        "Return only the Hindi translation, nothing else.\n\nText:\n{TEXT}",

    'translate_english' =>
        "Translate the following text to English. " .
        "Return only the English translation, nothing else.\n\nText:\n{TEXT}",

    'suggest_tags' =>
        "You are a product tagger for a sustainable Indian marketplace. " .
        "From the following text, suggest 5 to 8 relevant tags as a comma-separated list. " .
        "Tags must be short (1 to 3 words each). Focus on craft type, material, region, and sustainability angle. " .
        "Return only the comma-separated tags, nothing else.\n\nText:\n{TEXT}",

    'generate_description' =>
        "You are a product copywriter for Conscious Indi, a conscious Indian marketplace. " .
        "Write a compelling product description between 120 and 180 words based on the details below. " .
        "Be warm and specific. Highlight the artisan craft, the making process, and the sustainability angle. " .
        "Do not invent details not present in the input. " .
        "Return only the description, nothing else.\n\nProduct details:\n{TEXT}",
];

$prompt = str_replace('{TEXT}', $text, $prompts[$action]);
$result = ai_complete($prompt);

if ($result === null) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'AI service is unavailable right now. Please try again in a moment.']);
    exit;
}

echo json_encode(['success' => true, 'result' => $result, 'action' => $action]);
