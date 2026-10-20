<?php
declare(strict_types=1);
require_once __DIR__.'/notifications.php';

/** Flags repeated requests for human review; never rejects or restricts a customer automatically. */
function flag_repeated_customer_returns(PDO $db,string $customerId,string $vendorId,string $returnId): void {
    $threshold=3;$days=30;
    foreach([['scope'=>'platform','vendor'=>null],['scope'=>$vendorId,'vendor'=>$vendorId]] as $target){
        $params=['customer'=>$customerId];
        $where='user_id=:customer AND requested_at>=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 30 DAY)';
        if($target['vendor']!==null){$where.=' AND vendor_id=:vendor';$params['vendor']=$vendorId;}
        $stmt=$db->prepare('SELECT COUNT(*) FROM return_requests WHERE '.$where);$stmt->execute($params);$count=(int)$stmt->fetchColumn();
        if($count<$threshold)continue;
        $lookup=$db->prepare('SELECT id,status FROM customer_return_review_flags WHERE customer_id=:customer AND scope_key=:scope FOR UPDATE');
        $lookup->execute(['customer'=>$customerId,'scope'=>$target['scope']]);$prior=$lookup->fetch();
        if($prior){
            $db->prepare('UPDATE customer_return_review_flags SET request_count=:count,last_return_id=:return,status="open",updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['count'=>$count,'return'=>$returnId,'id'=>$prior['id']]);
        }else{
            $id=uuid_string();$db->prepare('INSERT INTO customer_return_review_flags (id,customer_id,scope_key,vendor_id,request_count,window_days,threshold_count,status,last_return_id,first_flagged_at,updated_at) VALUES (:id,:customer,:scope,:vendor,:count,:days,:threshold,"open",:return,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'customer'=>$customerId,'scope'=>$target['scope'],'vendor'=>$target['vendor'],'count'=>$count,'days'=>$days,'threshold'=>$threshold,'return'=>$returnId]);
        }
        if($count!==$threshold)continue;
        if($target['vendor']!==null){
            create_notification('vendor',$vendorId,'repeat_return_review','Repeated returns need review','This customer has submitted 3 return requests for your store within 30 days. Review each request and its evidence; do not reject automatically.','../returns.php',['customer_id'=>$customerId]);
        }else{
            $admins=$db->query('SELECT id FROM admin_users WHERE status="active"')->fetchAll(PDO::FETCH_COLUMN);
            foreach($admins as $adminId)create_notification('admin',(string)$adminId,'repeat_return_review','Customer return pattern needs review','A customer has submitted 3 return requests within 30 days. Review the requests and reasons before taking action.','../customers.php',['customer_id'=>$customerId]);
        }
    }
}
