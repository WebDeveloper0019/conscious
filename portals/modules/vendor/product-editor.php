<?php
declare(strict_types=1);

$catalogStandaloneEditor = true;
$catalogInitialProductId = trim((string)($_GET['id'] ?? ''));
$isNewProduct = $catalogInitialProductId === '';
$pageTitle = $isNewProduct ? 'Add product' : 'Edit product';
$pageSubtitle = $isNewProduct ? 'Create a complete product listing step by step' : 'Update the product details, photos and variants';
$activePage = 'catalog';
$vendorPageCss = 'assets/css/catalog-live.css?v=20261126-7';
$vendorPageScript = 'assets/js/catalog-live.js?v=20261126-7';
require __DIR__.'/layouts/head.php';
require __DIR__.'/pages/catalog-live.php';
require __DIR__.'/layouts/foot.php';
