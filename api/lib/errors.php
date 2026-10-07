<?php
declare(strict_types=1);
function install_error_handlers(): void {
    set_exception_handler(function(Throwable $e): void {
        $db=function_exists('get_db_if_connected')?get_db_if_connected():null;
        if($db && $db->inTransaction())$db->rollBack();
        if(function_exists('abort_idempotent_request'))abort_idempotent_request();
        if(PHP_SAPI==='cli'){fwrite(STDERR,get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().PHP_EOL);exit(1);}
        error_log(sprintf('[%s] %s in %s:%d',function_exists('request_id')?request_id():'no-request-id',$e->getMessage(),$e->getFile(),$e->getLine()));
        if(!headers_sent())header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);echo json_encode(['success'=>false,'message'=>'The server could not complete this request.','request_id'=>function_exists('request_id')?request_id():null]);
        exit(1);
    });
}
