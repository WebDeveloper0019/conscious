<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
send_cors_headers();
$db=get_db();
$id=trim((string)($_GET['id']??''));
if($id!==''){
    $stmt=$db->prepare('SELECT e.*,h.display_name AS host_name FROM events e JOIN event_hosts h ON h.id=e.host_id WHERE e.id=:id AND e.status="published"');
    $stmt->execute(['id'=>$id]);
    $event=$stmt->fetch();
    if(!$event)json_response(404,['success'=>false,'message'=>'Event not found.']);
    unset($event['online_url_encrypted']);
    $tickets=$db->prepare('SELECT id,name,description,price,capacity,sold_quantity,reserved_quantity,min_per_booking,max_per_booking,sales_start_at,sales_end_at,(capacity-sold_quantity-reserved_quantity) available_quantity FROM event_ticket_types WHERE event_id=:event AND status="active"');
    $tickets->execute(['event'=>$id]);
    $event['ticket_types']=$tickets->fetchAll();
    json_response(200,['success'=>true,'event'=>$event]);
}
$type=trim((string)($_GET['type']??''));
$stmt=$db->prepare('SELECT e.id,e.title,e.slug,e.event_type,e.short_description,e.venue_type,e.venue_name,e.starts_at,e.ends_at,h.display_name AS host_name,COALESCE(MIN(t.price),0) AS from_price FROM events e JOIN event_hosts h ON h.id=e.host_id LEFT JOIN event_ticket_types t ON t.event_id=e.id AND t.status="active" WHERE e.status="published" AND e.ends_at>=UTC_TIMESTAMP() AND (:type_filter="" OR e.event_type=:type_value) GROUP BY e.id ORDER BY e.starts_at LIMIT 100');
$stmt->execute(['type_filter'=>$type,'type_value'=>$type]);
json_response(200,['success'=>true,'events'=>$stmt->fetchAll()]);
