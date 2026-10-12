<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

send_cors_headers();

$userId = auth_user_id(require_auth());
$db = get_db();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

/*
 * Pagination
 */
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

/*
 * Activity filter
 *
 * Supported:
 * all
 * orders
 * reviews
 * wishlist
 * coupons
 * account
 * support
 */
$filter = strtolower(trim((string)($_GET['filter'] ?? 'all')));

$allowedFilters = [
    'all',
    'orders',
    'reviews',
    'wishlist',
    'coupons',
    'account',
    'support'
];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

/*
 * Build filter against audit action / subject information.
 *
 * We intentionally filter using existing audit_logs data.
 * We do not create fake activities.
 */
$where = [
    'actor_type = :actor_type',
    'actor_id = :actor_id'
];

$params = [
    'actor_type' => 'customer',
    'actor_id' => $userId
];

if ($filter !== 'all') {
    switch ($filter) {
        case 'orders':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('order', 'orders')
                OR LOWER(action) LIKE '%order%'
            )";
            break;

        case 'reviews':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('review', 'reviews')
                OR LOWER(action) LIKE '%review%'
            )";
            break;

        case 'wishlist':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('wishlist', 'product')
                OR LOWER(action) LIKE '%wishlist%'
            )";
            break;

        case 'coupons':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('coupon', 'coupons')
                OR LOWER(action) LIKE '%coupon%'
            )";
            break;

        case 'support':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('support', 'ticket', 'support_ticket')
                OR LOWER(action) LIKE '%support%'
                OR LOWER(action) LIKE '%ticket%'
            )";
            break;

        case 'account':
            $where[] = "(
                LOWER(COALESCE(subject_type, '')) IN ('user', 'customer', 'account', 'profile')
                OR LOWER(action) LIKE '%profile%'
                OR LOWER(action) LIKE '%password%'
                OR LOWER(action) LIKE '%account%'
                OR LOWER(action) LIKE '%address%'
                OR LOWER(action) LIKE '%login%'
                OR LOWER(action) LIKE '%logout%'
            )";
            break;
    }
}

/*
 * Fetch one extra record so the frontend knows whether
 * another page exists.
 */
$sql = "
    SELECT
        id,
        actor_type,
        actor_id,
        action,
        subject_type,
        subject_id,
        metadata_json,
        created_at
    FROM audit_logs
    WHERE " . implode(' AND ', $where) . "
    ORDER BY created_at DESC
    LIMIT :limit_plus_one OFFSET :offset
";

$stmt = $db->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

$stmt->bindValue(':limit_plus_one', $limit + 1, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasMore = count($rows) > $limit;

if ($hasMore) {
    array_pop($rows);
}

/*
 * Convert audit records into frontend-friendly activities.
 */
$activities = [];

foreach ($rows as $row) {
    $metadata = [];

    if (!empty($row['metadata_json'])) {
        $decoded = json_decode((string)$row['metadata_json'], true);

        if (is_array($decoded)) {
            $metadata = $decoded;
        }
    }

    $action = (string)$row['action'];
    $subjectType = (string)($row['subject_type'] ?? '');
    $subjectId = (string)($row['subject_id'] ?? '');

    $actionText = ucwords(
        str_replace(
            ['_', '-'],
            ' ',
            strtolower($action)
        )
    );

    /*
     * Default presentation.
     */
    $title = $actionText !== '' ? $actionText : 'Activity';
    $description = '';
    $category = 'account';
    $icon = 'fa-clock-rotate-left';
    $iconClass = 'navy';

    $combined = strtolower(
        $action . ' ' .
        $subjectType . ' ' .
        json_encode($metadata)
    );

    /*
     * Orders
     */
    if (
        strpos($combined, 'order') !== false ||
        in_array(strtolower($subjectType), ['order', 'orders'], true)
    ) {
        $category = 'orders';
        $icon = 'fa-bag-shopping';
        $iconClass = 'saffron';
    }

    /*
     * Reviews
     */
    elseif (
        strpos($combined, 'review') !== false ||
        in_array(strtolower($subjectType), ['review', 'reviews'], true)
    ) {
        $category = 'reviews';
        $icon = 'fa-star';
        $iconClass = 'green';
    }

    /*
     * Wishlist
     */
    elseif (
        strpos($combined, 'wishlist') !== false ||
        strtolower($subjectType) === 'wishlist'
    ) {
        $category = 'wishlist';
        $icon = 'fa-heart';
        $iconClass = 'saffron';
    }

    /*
     * Coupons
     */
    elseif (
        strpos($combined, 'coupon') !== false ||
        strtolower($subjectType) === 'coupon'
    ) {
        $category = 'coupons';
        $icon = 'fa-ticket';
        $iconClass = 'green';
    }

    /*
     * Support
     */
    elseif (
        strpos($combined, 'support') !== false ||
        strpos($combined, 'ticket') !== false
    ) {
        $category = 'support';
        $icon = 'fa-headset';
        $iconClass = 'navy';
    }

    /*
     * Account
     */
    else {
        $category = 'account';
        $icon = 'fa-user';
        $iconClass = 'navy';
    }

    /*
     * Try to create a useful description from metadata.
     */
    if (!empty($metadata['description'])) {
        $description = (string)$metadata['description'];
    } elseif (!empty($metadata['message'])) {
        $description = (string)$metadata['message'];
    } elseif ($subjectId !== '') {
        $description = $actionText . ' — ' . $subjectId;
    } else {
        $description = $actionText;
    }

    $activities[] = [
        'id' => $row['id'],
        'action' => $action,
        'category' => $category,
        'title' => $title,
        'description' => $description,
        'icon' => $icon,
        'iconClass' => $iconClass,
        'subjectType' => $subjectType !== '' ? $subjectType : null,
        'subjectId' => $subjectId !== '' ? $subjectId : null,
        'createdAt' => $row['created_at'],
        'metadata' => $metadata
    ];
}

json_response(200, [
    'success' => true,
    'activities' => $activities,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'hasMore' => $hasMore
    ]
]);