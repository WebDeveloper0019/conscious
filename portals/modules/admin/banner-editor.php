<?php
$bannerId = trim((string) ($_GET['id'] ?? ''));
$pageTitle = $bannerId === '' ? 'Add banner' : 'Edit banner';
$pageSubtitle = 'Manage the storefront hero message, image and destination';
$activePage = 'banners';
$adminPageScript = 'assets/js/banner-editor.js?v=20261123-1';
require __DIR__ . '/layouts/head.php';
require __DIR__ . '/pages/banner-editor.php';
require __DIR__ . '/layouts/foot.php';
