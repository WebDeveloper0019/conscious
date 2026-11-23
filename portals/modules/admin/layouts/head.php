<?php
require __DIR__.'/guard.php';
$pageTitle = $pageTitle ?? 'Admin';
$pageSubtitle = $pageSubtitle ?? 'Conscious India platform operations';
$activePage = $activePage ?? 'dashboard';
$adminIsLive = !empty($adminPageScript) || !empty($adminPageScripts);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <meta name="theme-color" content="#294333">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Conscious India Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/conscious/assets/css/bharatcraft.css?v=20261114">
  <link rel="stylesheet" href="/conscious/assets/css/conscious-pages.css">
  <link rel="stylesheet" href="/conscious/shared/assets/css/tokens.css">
  <link rel="stylesheet" href="/conscious/admin/assets/css/app.css?v=20261122-1">
  <link rel="stylesheet" href="/conscious/shared/assets/css/customer-inspired-workspace.css?v=20261031-1">
  <link rel="stylesheet" href="/conscious/shared/assets/css/brand-typography.css?v=20261026">
  <link rel="stylesheet" href="/conscious/admin/assets/css/admin-clean.css?v=20261123-1">
</head>
<body class="ci-role-admin" data-admin-live="<?= $adminIsLive ? 'true' : 'false' ?>">
<a class="ac-skip" href="#main-content">Skip to main content</a>
<div class="ci-dash-wrap">
  <?php require __DIR__.'/../components/sidebar.php'; ?>
  <div class="ci-sidebar-overlay" data-overlay></div>
  <div class="ci-dash-main">
    <header class="ac-global-header">
      <div class="ac-global-brand">
        <button class="ac-menu" type="button" data-menu aria-label="Collapse admin navigation" aria-controls="ac-sidebar" aria-expanded="true"><i class="fa-solid fa-bars"></i></button>
        <a href="/conscious/" aria-label="Conscious India home"><img src="/conscious/assets/images/logo-square.png" alt=""><strong>Conscious India</strong></a>
        <span class="ac-global-divider" aria-hidden="true"></span>
        <span class="ac-global-workspace">Admin workspace</span>
      </div>
      <nav class="ac-global-links" aria-label="Admin shortcuts">
        <a href="/conscious/admin/">Dashboard</a>
        <a href="/conscious/admin/queue">Work queue</a>
        <a href="/conscious/admin/support">Support</a>
      </nav>
      <a class="ac-global-account" href="/conscious/admin/access" aria-label="Open your admin account">
        <span><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></span>
        <b><?= htmlspecialchars($adminDisplayName, ENT_QUOTES, 'UTF-8') ?></b>
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
      </a>
    </header>
    <div class="ac-page-canvas">
    <header class="ci-dash-topbar">
      <div class="ac-title-group">
        <div>
          <span class="ac-workspace-label">ADMIN OPERATIONS</span>
          <h1 class="ci-dash-page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
          <p class="ci-dash-page-sub"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      </div>
      <div class="ac-top">
        <a class="ac-icon" href="/conscious/admin/audit" aria-label="Open audit log" title="Audit log"><i class="fa-solid fa-shield-halved"></i></a>
      </div>
    </header>
    <main class="ac-main" id="main-content" tabindex="-1">
      <div id="ac-alert" aria-live="polite"></div>
