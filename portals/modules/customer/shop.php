<?php
declare(strict_types=1);
$query=isset($_SERVER['QUERY_STRING'])&&$_SERVER['QUERY_STRING']!==''?'?'.$_SERVER['QUERY_STRING']:'';
header('Location: ../../../shop'.$query,true,302);
exit;
