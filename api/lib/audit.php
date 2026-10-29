<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';

if (!function_exists('request_id')) {
    function request_id(): string { static $id=null; return $id??=uuid_string(); }
}

if (!function_exists('audit_event')) {
    function audit_event(string $action,?string $subjectType=null,?string $subjectId=null,array $metadata=[],?array $actor=null): void {
        $actor=$actor??(function_exists('current_auth_session')?current_auth_session(false):null);
        $stmt=get_db()->prepare('INSERT INTO audit_logs (actor_type,actor_id,action,subject_type,subject_id,request_id,ip_hash,metadata_json,created_at) VALUES (:actor_type,:actor_id,:action,:subject_type,:subject_id,:request_id,:ip,:metadata,UTC_TIMESTAMP())');
        $stmt->execute(['actor_type'=>$actor['principal_type']??$actor['type']??null,'actor_id'=>$actor['principal_id']??$actor['id']??null,'action'=>$action,'subject_type'=>$subjectType,'subject_id'=>$subjectId,'request_id'=>request_id(),'ip'=>request_fingerprint(request_ip()),'metadata'=>$metadata?json_encode($metadata,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):null]);
    }
}

if (!function_exists('record_status_change')) {
    function record_status_change(string $entityType,string $entityId,?string $from,string $to,?string $reason=null,array $metadata=[],?array $actor=null): void {
        $actor=$actor??(function_exists('current_auth_session')?current_auth_session(false):null);
        $stmt=get_db()->prepare('INSERT INTO status_history (entity_type,entity_id,from_status,to_status,actor_type,actor_id,reason,metadata_json,created_at) VALUES (:type,:id,:from_status,:to_status,:actor_type,:actor_id,:reason,:metadata,UTC_TIMESTAMP())');
        $stmt->execute(['type'=>$entityType,'id'=>$entityId,'from_status'=>$from,'to_status'=>$to,'actor_type'=>$actor['principal_type']??$actor['type']??null,'actor_id'=>$actor['principal_id']??$actor['id']??null,'reason'=>$reason,'metadata'=>$metadata?json_encode($metadata,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):null]);
    }
}

if (!function_exists('assert_transition')) {
    function assert_transition(string $current,string $next,array $map): void {
        if(!in_array($next,$map[$current]??[],true))json_response(409,['success'=>false,'message'=>"Status cannot change from {$current} to {$next}.",'code'=>'INVALID_STATUS_TRANSITION']);
    }
}