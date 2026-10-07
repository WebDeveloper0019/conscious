<?php
declare(strict_types=1);
function store_private_upload(array $file,string $ownerType,string $ownerId,string $purpose,array $allowedMime,int $maxBytes=MAX_UPLOAD_BYTES): array {
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)json_response(422,['success'=>false,'message'=>'The selected file could not be uploaded.']);
    $size=(int)($file['size']??0);if($size<1||$size>$maxBytes)json_response(422,['success'=>false,'message'=>'The selected file size is not allowed.']);
    $tmp=(string)($file['tmp_name']??'');if(!is_uploaded_file($tmp))json_response(422,['success'=>false,'message'=>'The upload is invalid.']);
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp)?:'';$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf','text/csv'=>'csv','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx'];
    if(!in_array($mime,$allowedMime,true)||!isset($extensions[$mime]))json_response(422,['success'=>false,'message'=>'This file type is not allowed.']);
    if(str_starts_with($mime,'image/')){$image=@getimagesize($tmp);if(!$image||$image[0]>8000||$image[1]>8000)json_response(422,['success'=>false,'message'=>'The image dimensions are not allowed.']);}
    $id=uuid_string();$relative=gmdate('Y/m').'/'.bin2hex(random_bytes(20)).'.'.$extensions[$mime];$absolute=rtrim(PRIVATE_STORAGE_PATH,'/\\').DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relative);$directory=dirname($absolute);
    if(!is_dir($directory)&&!mkdir($directory,0750,true)&&!is_dir($directory))throw new RuntimeException('Private upload directory could not be created.');
    if(!move_uploaded_file($tmp,$absolute))throw new RuntimeException('Uploaded file could not be stored.');
    $sha=hash_file('sha256',$absolute);$stmt=get_db()->prepare('INSERT INTO file_assets (id,owner_type,owner_id,purpose,storage_key,original_name,mime_type,extension,size_bytes,sha256,visibility,scan_status,created_at) VALUES (:id,:owner_type,:owner_id,:purpose,:key,:name,:mime,:extension,:size,:sha,"private","pending",UTC_TIMESTAMP())');
    try{$stmt->execute(['id'=>$id,'owner_type'=>$ownerType,'owner_id'=>$ownerId,'purpose'=>$purpose,'key'=>$relative,'name'=>mb_substr(basename((string)($file['name']??'file')),0,255),'mime'=>$mime,'extension'=>$extensions[$mime],'size'=>$size,'sha'=>$sha]);}catch(Throwable $e){@unlink($absolute);throw $e;}
    return ['id'=>$id,'mime_type'=>$mime,'size_bytes'=>$size,'scan_status'=>'pending'];
}
