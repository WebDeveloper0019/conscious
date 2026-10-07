<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';send_cors_headers();$principal=require_principal(['influencer']);$stmt=get_db()->prepare('SELECT id,full_name,email,mobile,handle,bio,platforms_json,audience_json,status,verification_status,created_at FROM influencers WHERE id=:id');$stmt->execute(['id'=>$principal['id']]);$creator=$stmt->fetch();if(!$creator)json_response(401,['success'=>false,'message'=>'Account not found.']);json_response(200,['success'=>true,'influencer'=>$creator,'csrf_token'=>issue_csrf_token()]);
