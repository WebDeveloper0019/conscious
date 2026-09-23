<?php
$navGroups = [
  'Overview' => [['dashboard.php','dashboard','fa-chart-line','Dashboard'],['dashboard.php#tasks','tasks','fa-list-check','Tasks'],['dashboard.php#health','health','fa-heart-pulse','Account Health']],
  'Store' => [['store.php','store','fa-store','Store & Branding'],['sustainability.php','sustainability','fa-seedling','Sustainability'],['store.php#policies','policies','fa-file-shield','Policies'],['store.php#availability','availability','fa-calendar-days','Availability'],['store.php#locations','locations','fa-location-dot','Locations']],
  'Catalogue' => [['catalog.php','catalog','fa-box','Products'],['catalog.php#add-product','add-product','fa-plus-circle','Add Product'],['catalog.php#collections','collections','fa-layer-group','Collections'],['catalog.php#bulk','bulk','fa-file-import','Bulk Import'],['catalog.php#certificates','certificates','fa-certificate','Certificates']],
  'Inventory' => [['inventory.php','inventory','fa-warehouse','Inventory'],['inventory.php#adjustments','adjustments','fa-sliders','Adjustments'],['inventory.php#batches','batches','fa-boxes-stacked','Batches & Expiry']],
  'Orders' => [['orders.php','orders','fa-bag-shopping','Orders'],['delivery.php','delivery','fa-truck','Manual Delivery'],['returns.php','returns','fa-rotate-left','Returns & Replacements']],
  'Growth' => [['promotions.php','promotions','fa-ticket','Promotions'],['events.php','events','fa-calendar-star','Events']],
  'Finance' => [['finance.php','finance','fa-indian-rupee-sign','Earnings & Ledger'],['finance.php#payouts','payouts','fa-money-check-dollar','Payouts'],['finance.php#cod','cod','fa-money-bill-wave','COD Reconciliation']],
  'Insights' => [['analytics.php','analytics','fa-chart-column','Analytics & Reports']],
  'Communication' => [['communication.php','communication','fa-comments','Messages & Reviews'],['support.php','support','fa-headset','Support & Disputes']],
  'Account' => [['account.php','account','fa-id-card','Verification & Settings'],['account.php#staff','staff','fa-users-gear','Staff & Permissions'],['account.php#security','security','fa-shield-halved','Security'],['account.php#audit','audit','fa-clock-rotate-left','Audit History']],
];
if (!empty($vendorOnboardingMode)) {
  $navGroups = ['Application' => [['onboarding.php','account','fa-clipboard-check','Onboarding checklist']], 'Help' => [['support.php','support','fa-headset','Help & support']]];
}
?>
<aside class="ci-dash-sidebar" id="ci-sidebar" aria-label="Vendor navigation">
  <div class="ci-sidebar-brand"><div class="ci-sidebar-brand-icon"><i class="fa-solid fa-store"></i></div><div class="ci-sidebar-brand-text"><div class="ci-sidebar-brand-name">Conscious Indi</div><div class="ci-sidebar-brand-role">Vendor Portal</div></div></div>
  <div class="ci-sidebar-user"><div class="ci-sidebar-avatar">RS</div><div class="ci-sidebar-user-info"><div class="ci-sidebar-user-name"><?= !empty($vendorOnboardingMode) ? 'New Vendor Application' : 'Rajasthan Heritage' ?></div><div class="ci-sidebar-user-email"><span class="vc-online<?= !empty($vendorOnboardingMode) ? ' pending' : '' ?>"></span> <?= !empty($vendorOnboardingMode) ? 'Setup in progress' : 'Store active' ?></div></div></div>
  <nav class="ci-sidebar-nav">
    <?php foreach ($navGroups as $group => $items): ?>
      <div class="ci-sidebar-section-label"><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></div>
      <?php foreach ($items as [$href,$key,$icon,$label]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="ci-sidebar-nav-item<?= $activePage === $key ? ' active' : '' ?>"><i class="fa-solid <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span><?php if ($key === 'orders'): ?><span class="ci-sidebar-nav-badge">7</span><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="vc-sidebar-foot"><a href="../../../index.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-arrow-left"></i><span>Back to Store</span></a></div>
</aside>
