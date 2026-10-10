<?php
declare(strict_types=1);

require_once __DIR__.'/lib/helpers.php';
send_cors_headers();

$principal=require_principal(['customer','vendor','admin','influencer']);
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';

if($method==='GET'){
  
$unread = isset($_GET['unread'])
    ? filter_var($_GET['unread'], FILTER_VALIDATE_BOOLEAN)
    : null;

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$typeFilter = trim((string)($_GET['type'] ?? ''));

   
$sql = "SELECT id, type, title, message, action_url, metadata_json, read_at, created_at
        FROM notifications
        WHERE recipient_type = :recipient_type
          AND recipient_id = :recipient_id";

$params = [
    'recipient_type' => $principal['type'],
    'recipient_id' => $principal['id']
];

if ($unread === true) {
    $sql .= " AND read_at IS NULL";
} elseif ($unread === false) {
    $sql .= " AND read_at IS NOT NULL";
}

if ($typeFilter !== '') {
    $sql .= " AND type = :notification_type";
    $params['notification_type'] = $typeFilter;
}

$countSql = "SELECT COUNT(*)
             FROM notifications
             WHERE recipient_type = :recipient_type
               AND recipient_id = :recipient_id";

$countParams = [
    'recipient_type' => $principal['type'],
    'recipient_id' => $principal['id']
];

if ($unread === true) {
    $countSql .= " AND read_at IS NULL";
} elseif ($unread === false) {
    $countSql .= " AND read_at IS NOT NULL";
}

if ($typeFilter !== '') {
    $countSql .= " AND type = :notification_type";
    $countParams['notification_type'] = $typeFilter;
}

$countStmt = $db->prepare($countSql);
$countStmt->execute($countParams);
$total = (int) $countStmt->fetchColumn();

$sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$unreadStmt = $db->prepare(
    "SELECT COUNT(*)
     FROM notifications
     WHERE recipient_type = :type
       AND recipient_id = :id
       AND read_at IS NULL"
);

$unreadStmt->execute([
    'type' => $principal['type'],
    'id' => $principal['id']
]);

$unreadCount = (int) $unreadStmt->fetchColumn();


    foreach($rows as &$row){
        $row['metadata']=$row['metadata_json'] ? json_decode((string)$row['metadata_json'],true) : null;
        unset($row['metadata_json']);
    }
    unset($row);

json_response(200, [
    'success' => true,
    'notifications' => $rows,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'totalPages' => (int) ceil($total / $limit)
    ],
    'unreadCount' => $unreadCount
]);
}

if($method==='PATCH'){
    $body=get_json_body();
    $id=trim((string)($body['id']??''));
    if($id===''){
        json_response(422,['success'=>false,'message'=>'Notification id is required.']);
    }

    if($id==='all'){
        $stmt=$db->prepare(
            "UPDATE notifications
             SET read_at=COALESCE(read_at,UTC_TIMESTAMP())
             WHERE recipient_type=:type AND recipient_id=:owner AND read_at IS NULL"
        );
        $stmt->execute(['type'=>$principal['type'],'owner'=>$principal['id']]);
        json_response(200,['success'=>true,'message'=>'All notifications marked as read.','updated'=>$stmt->rowCount()]);
    }

    if(!preg_match('/^[A-Za-z0-9_-]{10,100}$/',$id)){
        json_response(422,['success'=>false,'message'=>'Invalid notification id.']);
    }

    $stmt=$db->prepare(
        "UPDATE notifications
         SET read_at=COALESCE(read_at,UTC_TIMESTAMP())
         WHERE id=:id AND recipient_type=:type AND recipient_id=:owner"
    );
    $stmt->execute(['id'=>$id,'type'=>$principal['type'],'owner'=>$principal['id']]);
    if($stmt->rowCount()===0){
        $check=$db->prepare("SELECT id,read_at FROM notifications WHERE id=:id AND recipient_type=:type AND recipient_id=:owner");
        $check->execute(['id'=>$id,'type'=>$principal['type'],'owner'=>$principal['id']]);
        $row=$check->fetch();
        if(!$row)json_response(404,['success'=>false,'message'=>'Notification not found.']);
        json_response(200,['success'=>true,'message'=>'Notification was already read.','alreadyRead'=>true]);
    }
    json_response(200,['success'=>true,'message'=>'Notification marked as read.','notificationId'=>$id]);
}

json_response(405,['success'=>false,'message'=>'Method not allowed.']);
