<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/helpers.php';send_cors_headers();require_principal(['influencer']);revoke_current_session();json_response(200,['success'=>true,'message'=>'Signed out.']);
