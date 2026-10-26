<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/admin.php';
require_once __DIR__.'/../lib/notifications.php';
send_cors_headers();
$context=require_admin('influencers.manage');
$db=get_db();
$method=$_SERVER['REQUEST_METHOD']??'GET';
if($method==='GET'){
    $stmt=$db->query('SELECT c.*,(SELECT COUNT(*) FROM influencer_referral_links l WHERE l.campaign_id=c.id) influencer_count FROM influencer_campaigns c ORDER BY c.created_at DESC');
    json_response(200,['success'=>true,'campaigns'=>$stmt->fetchAll()]);
}
if($method==='POST'){
    $body=get_json_body();
    $action=trim((string)($body['action']??'create'));
    if($action==='assign'){
        $campaign=trim((string)($body['campaign_id']??''));
        $influencer=trim((string)($body['influencer_id']??''));
        $destination=trim((string)($body['destination_url']??''));
        if(!str_starts_with($destination,'/')||str_starts_with($destination,'//')||preg_match('/[\\\x00-\x1f\x7f]/',$destination)||strlen($destination)>500)
            json_response(422,['success'=>false,'message'=>'Use a safe, same-site destination path beginning with /.']);
        $check=$db->prepare('SELECT id FROM influencers WHERE id=:id AND status="active"');
        $check->execute(['id'=>$influencer]);
        if(!$check->fetch())json_response(422,['success'=>false,'message'=>'Creator must be approved and active.']);
        $check=$db->prepare('SELECT id FROM influencer_campaigns WHERE id=:id AND status="active" AND starts_at<=UTC_TIMESTAMP() AND ends_at>=UTC_TIMESTAMP()');
        $check->execute(['id'=>$campaign]);
        if(!$check->fetch())json_response(422,['success'=>false,'message'=>'Choose a currently active campaign.']);
        $code=strtoupper(trim((string)($body['code']??'')));
        if($code==='')$code='CI'.strtoupper(bin2hex(random_bytes(6)));
        if(!preg_match('/^[A-Z0-9_-]{6,32}$/',$code))json_response(422,['success'=>false,'message'=>'Referral code must contain 6–32 letters, numbers, underscores or hyphens.']);
        $id=uuid_string();
        try{
            $db->prepare('INSERT INTO influencer_referral_links (id,influencer_id,campaign_id,code,destination_url,status,created_at) VALUES (:id,:owner,:campaign,:code,:url,"active",UTC_TIMESTAMP())')->execute(['id'=>$id,'owner'=>$influencer,'campaign'=>$campaign,'code'=>$code,'url'=>$destination]);
        }catch(PDOException $error){
            if((string)$error->getCode()==='23000')json_response(409,['success'=>false,'message'=>'This referral code or assignment already exists.']);
            throw $error;
        }
        audit_event('admin.influencer_campaign_assigned','influencer_campaign',$campaign,['influencer_id'=>$influencer,'referral_code'=>$code]);
        json_response(201,['success'=>true,'message'=>'Creator assigned to campaign.','referral_code'=>$code]);
    }
    $name=trim((string)($body['name']??''));
    $description=trim((string)($body['description']??''));
    $type=trim((string)($body['commission_type']??''));
    $value=round((float)($body['commission_value']??0),2);
    $start=strtotime((string)($body['starts_at']??''));
    $end=strtotime((string)($body['ends_at']??''));
    if(mb_strlen($name)<3||mb_strlen($description)<20||!in_array($type,['percent','fixed'],true)||$value<=0||($type==='percent'&&$value>100)||$start===false||$end===false||$end<=$start)
        json_response(422,['success'=>false,'message'=>'Campaign details are invalid.']);
    $status=in_array($body['status']??'draft',['draft','active','paused'],true)?$body['status']:'draft';
    $id=uuid_string();
    $db->prepare('INSERT INTO influencer_campaigns (id,name,description,commission_type,commission_value,attribution_days,starts_at,ends_at,eligibility_json,disclosure_text,status,created_by,created_at,updated_at) VALUES (:id,:name,:description,:type,:value,:days,:starts,:ends,:eligibility,:disclosure,:status,:admin,UTC_TIMESTAMP(),UTC_TIMESTAMP())')->execute(['id'=>$id,'name'=>$name,'description'=>$description,'type'=>$type,'value'=>$value,'days'=>max(1,min(90,(int)($body['attribution_days']??30))),'starts'=>gmdate('Y-m-d H:i:s',$start),'ends'=>gmdate('Y-m-d H:i:s',$end),'eligibility'=>json_encode((array)($body['eligibility']??[])),'disclosure'=>trim((string)($body['disclosure_text']??'Paid partnership with Conscious India.')),'status'=>$status,'admin'=>$context['admin']['id']]);
    audit_event('admin.influencer_campaign_created','influencer_campaign',$id,['status'=>$status]);
    json_response(201,['success'=>true,'message'=>'Campaign created.','campaign_id'=>$id]);
}
if($method==='PATCH'){
    $body=get_json_body();$id=trim((string)($body['campaign_id']??''));$action=trim((string)($body['action']??''));$reason=trim((string)($body['reason']??''));
    if(!in_array($action,['pause','resume'],true)||mb_strlen($reason)<10)json_response(422,['success'=>false,'message'=>'Choose a campaign action and provide a detailed reason.']);
    $stmt=$db->prepare('SELECT status,starts_at,ends_at FROM influencer_campaigns WHERE id=:id LIMIT 1');$stmt->execute(['id'=>$id]);$campaign=$stmt->fetch();
    if(!$campaign)json_response(404,['success'=>false,'message'=>'Campaign not found.']);
    if($action==='pause'&&$campaign['status']!=='active')json_response(409,['success'=>false,'message'=>'Only active campaigns can be paused.']);
    if($action==='resume'&&($campaign['status']!=='paused'||strtotime($campaign['starts_at'])>time()||strtotime($campaign['ends_at'])<time()))json_response(409,['success'=>false,'message'=>'Only a paused campaign within its scheduled dates can be resumed.']);
    $next=$action==='pause'?'paused':'active';
    $db->prepare('UPDATE influencer_campaigns SET status=:status,updated_at=UTC_TIMESTAMP() WHERE id=:id')->execute(['status'=>$next,'id'=>$id]);
    $members=$db->prepare('SELECT DISTINCT influencer_id FROM influencer_referral_links WHERE campaign_id=:id');$members->execute(['id'=>$id]);
    foreach($members->fetchAll() as $member)create_notification('influencer',$member['influencer_id'],'campaign_update','Campaign '.$next,'A campaign assigned to you is now '.$next.'.',null,['campaign_id'=>$id]);
    audit_event('admin.influencer_campaign_'.$action,'influencer_campaign',$id,['reason'=>$reason]);
    json_response(200,['success'=>true,'message'=>'Campaign '.$next.'.','status'=>$next]);
}
json_response(405,['success'=>false,'message'=>'Method not allowed.']);
