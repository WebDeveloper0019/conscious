<?php
declare(strict_types=1);
function create_notification(string $recipientType,string $recipientId,string $type,string $title,string $message,?string $actionUrl=null,array $metadata=[]): string {
    $id=uuid_string();$stmt=get_db()->prepare('INSERT INTO notifications (id,recipient_type,recipient_id,type,title,message,action_url,metadata_json,created_at) VALUES (:id,:recipient_type,:recipient_id,:type,:title,:message,:action_url,:metadata,UTC_TIMESTAMP())');
    $stmt->execute(['id'=>$id,'recipient_type'=>$recipientType,'recipient_id'=>$recipientId,'type'=>$type,'title'=>$title,'message'=>$message,'action_url'=>$actionUrl,'metadata'=>$metadata?json_encode($metadata,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):null]);return $id;
}
