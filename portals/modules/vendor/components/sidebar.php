<?php
$navGroups = [
  'Workspace' => [['dashboard.php','dashboard','fa-house','Overview']],
  'Set up your store' => [['store.php','store','fa-store','Store profile'],['account.php','account','fa-shield-halved','Application & account'],['sustainability.php','sustainability','fa-seedling','Claims & evidence']],
  'Sell & fulfil' => [['catalog.php','catalog','fa-box-open','Products'],['inventory.php','inventory','fa-warehouse','Inventory'],['orders.php','orders','fa-bag-shopping','Orders'],['delivery.php','delivery','fa-truck-fast','Shipping & delivery'],['returns.php','returns','fa-rotate-left','Returns']],
  'Grow' => [['promotions.php','promotions','fa-ticket','Offers'],['packages.php','packages','fa-bullhorn','Creator partnerships'],['events.php','events','fa-calendar-days','Events'],['analytics.php','analytics','fa-chart-column','Reports'],['upgrade.php','upgrade','fa-arrow-trend-up','Plans']],
  'Money' => [['finance.php','finance','fa-indian-rupee-sign','Earnings & payouts']],
  'Support' => [['communication.php','communication','fa-comments','Customer messages'],['support.php','support','fa-headset','Help centre']],
];
if (!empty($vendorOnboardingMode)) {
  $navGroups = ['Application' => [['onboarding.php','account','fa-clipboard-check','Onboarding checklist']], 'Help' => [['support.php','support','fa-headset','Help & support']]];
}
?>
<aside class="ci-dash-sidebar" id="ci-sidebar" aria-label="Vendor navigation">
  <a class="ci-sidebar-brand" href="../../../index.html" aria-label="Conscious India home"><img class="ci-sidebar-logo" src="../../../assets/images/logo-square.png" alt=""><div class="ci-sidebar-brand-text"><div class="ci-sidebar-brand-name">Conscious India</div><div class="ci-sidebar-brand-role">Vendor workspace</div></div></a>
  <div class="ci-sidebar-user"><div class="ci-sidebar-avatar"><?= htmlspecialchars($vendorInitials, ENT_QUOTES, 'UTF-8') ?></div><div class="ci-sidebar-user-info"><div class="ci-sidebar-user-name"><?= htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') ?></div><div class="ci-sidebar-user-email"><span class="vc-online<?= $vendorView['status'] !== 'active' ? ' pending' : '' ?>"></span> <?= $vendorView['verification_status'] === 'demo_fixture' ? 'Local demo vendor' : ($vendorView['status'] === 'active' ? 'Store active' : 'Application in progress') ?></div></div></div>
  <nav class="ci-sidebar-nav">
    <?php foreach ($navGroups as $group => $items): ?>
      <div class="ci-sidebar-section-label"><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></div>
      <?php foreach ($items as [$href,$key,$icon,$label]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="ci-sidebar-nav-item<?= $activePage === $key ? ' active' : '' ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><i class="fa-solid <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="vc-sidebar-foot"><a href="../../../index.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-arrow-left"></i><span>View public website</span></a></div>
</aside>
