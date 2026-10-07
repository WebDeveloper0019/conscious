<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}require_once __DIR__.'/../lib/helpers.php';
if(CLAMAV_BINARY===''||!is_file(CLAMAV_BINARY)){fwrite(STDERR,"CLAMAV_BINARY is not configured. Files remain pending and cannot be verified.\n");exit(1);}
$stmt=get_db()->query('SELECT id,storage_key FROM file_assets WHERE scan_status="pending" AND deleted_at IS NULL ORDER BY created_at LIMIT 100');$files=$stmt->fetchAll();
foreach($files as $file){$path=rtrim(PRIVATE_STORAGE_PATH,'/\\').DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$file['storage_key']);if(!is_file($path)){get_db()->prepare('UPDATE file_assets SET scan_status="missing" WHERE id=:id')->execute(['id'=>$file['id']]);continue;}$command=escapeshellarg(CLAMAV_BINARY).' --no-summary '.escapeshellarg($path);exec($command,$output,$code);$status=$code===0?'clean':($code===1?'infected':'scan_error');get_db()->prepare('UPDATE file_assets SET scan_status=:status WHERE id=:id')->execute(['status'=>$status,'id'=>$file['id']]);fwrite(STDOUT,$file['id'].' '.$status.PHP_EOL);}
