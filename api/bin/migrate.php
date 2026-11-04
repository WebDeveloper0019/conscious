<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../lib/db.php';

$db=get_db();
$db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) NOT NULL PRIMARY KEY, checksum CHAR(64) NOT NULL, applied_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
$migrationDir=__DIR__.'/../database/migrations';
$files=glob($migrationDir.'/*.sql') ?: []; sort($files,SORT_STRING);
$applied=$db->query('SELECT version,checksum FROM schema_migrations')->fetchAll(PDO::FETCH_KEY_PAIR);

foreach($files as $file){
    $version=basename($file,'.sql'); $sql=file_get_contents($file);
    $normalizedSql=str_replace(["\r\n","\r"],"\n",(string)$sql);
    $checksum=hash('sha256',$normalizedSql);
    if(isset($applied[$version])){
        // Older Windows installations stored CRLF hashes. Accept only newline
        // equivalents; any actual SQL change must still stop the upgrade.
        $knownHashes=[$checksum,hash('sha256',(string)$sql),hash('sha256',str_replace("\n","\r\n",$normalizedSql))];
        // This migration was corrected for MariaDB's CHAR padding rules after
        // some installations had already applied the original equivalent SQL.
        if ($version === '0036_cart_active_only_unique'
            && $checksum === '7fb485d02668c67631cbc588f04636e7bd83d5a778871cb6cc3736b88a2c4f32') {
            $knownHashes[] = '1c5262e729dfaf111e498818346afd5644d4b92090b6b5e7d5427849b83611ae';
            $knownHashes[] = '165fca27057563880542a768ee54d6f9a7b0345da493b3665f19a83024860984';
        }
        if(!in_array((string)$applied[$version],$knownHashes,true)){fwrite(STDERR,"Migration checksum changed: {$version}\n");exit(1);} continue;
    }
    try{
        $db->exec((string)$sql);
        $stmt=$db->prepare('INSERT INTO schema_migrations (version,checksum,applied_at) VALUES (:version,:checksum,UTC_TIMESTAMP())');
        $stmt->execute(['version'=>$version,'checksum'=>$checksum]);
        fwrite(STDOUT,"Applied {$version}\n");
    }catch(Throwable $e){fwrite(STDERR,"Failed {$version}: {$e->getMessage()}\n");exit(1);}
}
fwrite(STDOUT,"Database is up to date.\n");
