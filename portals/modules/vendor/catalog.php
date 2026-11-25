<?php
$pageTitle = 'Catalogue';
$pageSubtitle = 'Create, review and manage your own products';
$activePage = 'catalog';
$catalogStandaloneEditor = false;
$catalogInitialProductId = '';
$vendorPageCss = 'assets/css/catalog-live.css?v=20261126-7';
$vendorPageScript = 'assets/js/catalog-live.js?v=20261126-7';
require __DIR__.'/layouts/head.php';
require __DIR__.'/pages/catalog-live.php';
require __DIR__.'/layouts/foot.php';
