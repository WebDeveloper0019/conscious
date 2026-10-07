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
    $version=basename($file,'.sql'); $sql=file_get_contents($file); $checksum=hash('sha256',(string)$sql);
    if(isset($applied[$version])){
        if(!hash_equals((string)$applied[$version],$checksum)){fwrite(STDERR,"Migration checksum changed: {$version}\n");exit(1);} continue;
    }
    try{
        $db->exec((string)$sql);
        $stmt=$db->prepare('INSERT INTO schema_migrations (version,checksum,applied_at) VALUES (:version,:checksum,UTC_TIMESTAMP())');
        $stmt->execute(['version'=>$version,'checksum'=>$checksum]);
        fwrite(STDOUT,"Applied {$version}\n");
    }catch(Throwable $e){fwrite(STDERR,"Failed {$version}: {$e->getMessage()}\n");exit(1);}
}
fwrite(STDOUT,"Database is up to date.\n");
