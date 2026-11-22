<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/../lib/object-storage.php';
if(CLAMAV_BINARY===''||!is_file(CLAMAV_BINARY)){fwrite(STDERR,"CLAMAV_BINARY is not configured. Files remain pending and cannot be verified.\n");exit(1);}

$stmt=get_db()->query('SELECT id,storage_provider,storage_key FROM file_assets WHERE scan_status="pending" AND deleted_at IS NULL ORDER BY created_at LIMIT 100');
$files=$stmt->fetchAll();
foreach($files as $file){
    $temporary=null;
    try{
        if(file_asset_provider($file)==='r2'){
            $temporary=tempnam(sys_get_temp_dir(),'ci-scan-');
            if($temporary===false)throw new RuntimeException('Temporary scan file could not be created.');
            $contents=file_asset_read($file);
            if($contents===null||file_put_contents($temporary,$contents)===false)throw new RuntimeException('R2 object could not be prepared for scanning.');
            $path=$temporary;
        }else{
            $path=file_asset_local_path($file);
            if($path===null){get_db()->prepare('UPDATE file_assets SET scan_status="missing" WHERE id=:id')->execute(['id'=>$file['id']]);continue;}
        }
        $output=[];$command=escapeshellarg(CLAMAV_BINARY).' --no-summary '.escapeshellarg($path);exec($command,$output,$code);
        $status=$code===0?'clean':($code===1?'infected':'scan_error');
    }catch(Throwable $error){error_log('File scan preparation failed: '.$error->getMessage());$status='scan_error';}
    finally{if($temporary!==null&&is_file($temporary))@unlink($temporary);}
    get_db()->prepare('UPDATE file_assets SET scan_status=:status WHERE id=:id')->execute(['status'=>$status,'id'=>$file['id']]);
    fwrite(STDOUT,$file['id'].' '.$status.PHP_EOL);
}
