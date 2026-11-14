<?php
declare(strict_types=1);
$id=trim((string)($_GET['id']??$_GET['product']??''));
header('Location: /conscious/product-detail'.($id!==''?'?id='.rawurlencode($id):''),true,302);
exit;
