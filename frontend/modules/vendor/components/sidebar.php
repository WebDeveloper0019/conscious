<?php
$navGroups = [
  'Home' => [['dashboard.php','dashboard','fa-house','Overview']],
  'Your store' => [['store.php','store','fa-store','Store profile'],['sustainability.php','sustainability','fa-seedling','Sustainability']],
  'Sell' => [['catalog.php','catalog','fa-box-open','Products'],['inventory.php','inventory','fa-warehouse','Stock'],['orders.php','orders','fa-bag-shopping','Orders'],['delivery.php','delivery','fa-truck','Delivery'],['returns.php','returns','fa-rotate-left','Returns']],
  'Grow' => [['promotions.php','promotions','fa-ticket','Offers'],['events.php','events','fa-calendar-star','Events'],['analytics.php','analytics','fa-chart-column','Reports']],
  'Money' => [['finance.php','finance','fa-indian-rupee-sign','Earnings & payouts'],['upgrade.php','upgrade','fa-arrow-trend-up','Upgrade plan']],
  'Help' => [['communication.php','communication','fa-comments','Customers'],['support.php','support','fa-headset','Help & support']],
  'Settings' => [['account.php','account','fa-gear','Account settings']],
];
if (!empty($vendorOnboardingMode)) {
  $navGroups = ['Application' => [['onboarding.php','account','fa-clipboard-check','Onboarding checklist']], 'Help' => [['support.php','support','fa-headset','Help & support']]];
}
?>
<aside class="ci-dash-sidebar" id="ci-sidebar" aria-label="Vendor navigation">
  <div class="ci-sidebar-brand"><div class="ci-sidebar-brand-icon"><i class="fa-solid fa-store"></i></div><div class="ci-sidebar-brand-text"><div class="ci-sidebar-brand-name">Conscious Indi</div><div class="ci-sidebar-brand-role">Vendor Portal</div></div></div>
  <div class="ci-sidebar-user"><div class="ci-sidebar-avatar"><?= htmlspecialchars($vendorInitials, ENT_QUOTES, 'UTF-8') ?></div><div class="ci-sidebar-user-info"><div class="ci-sidebar-user-name"><?= htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') ?></div><div class="ci-sidebar-user-email"><span class="vc-online<?= $vendorView['status'] !== 'active' ? ' pending' : '' ?>"></span> <?= $vendorView['verification_status'] === 'demo_fixture' ? 'Local demo vendor' : ($vendorView['status'] === 'active' ? 'Store active' : 'Application in progress') ?></div></div></div>
  <nav class="ci-sidebar-nav">
    <?php foreach ($navGroups as $group => $items): ?>
      <div class="ci-sidebar-section-label"><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></div>
      <?php foreach ($items as [$href,$key,$icon,$label]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="ci-sidebar-nav-item<?= $activePage === $key ? ' active' : '' ?>"><i class="fa-solid <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="vc-sidebar-foot"><a href="../../../index.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-arrow-left"></i><span>Back to Store</span></a></div>
</aside>
