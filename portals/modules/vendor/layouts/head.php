<?php
require_once __DIR__.'/guard.php';
$pageTitle = $pageTitle ?? 'Vendor Portal';
$activePage = $activePage ?? 'dashboard';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#A63D24">
  <meta name="referrer" content="strict-origin-when-cross-origin">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Conscious India Vendor</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/conscious/assets/css/bharatcraft.css?v=20261114">
  <link rel="stylesheet" href="/conscious/assets/css/conscious-pages.css">
  <link rel="stylesheet" href="/conscious/shared/assets/css/tokens.css">
  
  <link rel="stylesheet" href="assets/css/app.css?v=20261024">
  <link rel="stylesheet" href="assets/css/vendor-experience.css?v=20261024">
  <?php if (!empty($vendorPageCss)): ?><link rel="stylesheet" href="<?= htmlspecialchars($vendorPageCss, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
  <?php if (!empty($vendorOnboardingMode)): ?><link rel="stylesheet" href="assets/css/onboarding-v2.css"><?php endif; ?>
  <link rel="stylesheet" href="/conscious/shared/assets/css/customer-inspired-workspace.css?v=20261031-1">
  <link rel="stylesheet" href="/conscious/shared/assets/css/brand-typography.css?v=20261026">
  <link rel="stylesheet" href="/conscious/partner/assets/css/vendor-brand-system.css?v=20261122-3">
  <link rel="stylesheet" href="/conscious/partner/assets/css/vendor-shell-refresh.css?v=20261121-2">
  <link rel="stylesheet" href="/conscious/partner/assets/css/vendor-tabler-preview.css?v=20261122-3">
</head>
<body class="ci-role-vendor">
<a class="vc-skip" href="#main-content">Skip to main content</a>
<header class="vc-global-header">
  <div class="vc-global-brand">
    <button type="button" class="ci-sidebar-hamburger" id="ci-sidebar-hamburger" aria-label="Open navigation" aria-controls="ci-sidebar" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
    <a href="/conscious/" aria-label="Conscious India home"><img src="/conscious/assets/images/logo-square.png" alt=""><strong>Conscious India</strong></a>
    <span class="vc-global-divider" aria-hidden="true"></span><span class="vc-global-workspace">Seller workspace</span>
  </div>
  <nav class="vc-global-links" aria-label="Vendor shortcuts"><a href="/conscious/partner/catalog">Products</a><a href="/conscious/partner/orders">Orders</a><a href="/conscious/partner/support">Help</a></nav>
  <a class="vc-global-account" href="/conscious/partner/account" aria-label="Open your account"><span><?= htmlspecialchars($vendorInitials, ENT_QUOTES, 'UTF-8') ?></span><b><?= htmlspecialchars($vendorOwner, ENT_QUOTES, 'UTF-8') ?></b><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></a>
</header>
<div class="ci-dash-wrap">
<?php require __DIR__ . '/../components/sidebar.php'; ?>
<div class="ci-sidebar-overlay" id="ci-sidebar-overlay"></div>
<div class="ci-dash-main">
  <header class="ci-dash-topbar">
    <div class="vc-topbar-title">
      <div><span class="vc-topbar-kicker">YOUR WORKSPACE</span><h1 class="ci-dash-page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1><p class="ci-dash-page-sub"><?= htmlspecialchars($pageSubtitle ?? 'Manage your Conscious India store', ENT_QUOTES, 'UTF-8') ?></p></div>
    </div>
    <div class="vc-top-actions">
      <a class="vc-topbar-create" href="/conscious/partner/catalog#add-product" aria-label="Add a product to your store" title="Add a product to your store"><i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add product</span></a>
      <?php if (!empty($vendorSellingReady)): ?><a class="vc-icon-button" href="/conscious/partner/communication#notifications" aria-label="Notifications" title="Notifications"><i class="fa-regular fa-bell"></i></a><?php endif; ?>
    </div>
  </header>
  <main id="main-content" class="vc-main" tabindex="-1">
    <div id="vc-alert-region" class="vc-alert-region" role="status" aria-live="polite"></div>
