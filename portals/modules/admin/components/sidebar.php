<?php
$groups = [
    'Overview' => ['icon' => 'fa-table-columns', 'items' => [
        ['index.php', 'dashboard', 'fa-house', 'Operations overview'],
        ['queue.php', 'queue', 'fa-list-check', 'Unified work queue'],
    ]],
    'Review & approval' => ['icon' => 'fa-circle-check', 'items' => [
        ['vendors.php', 'vendors', 'fa-store', 'Vendor applications'],
        ['catalog.php', 'catalog', 'fa-box', 'Products & claims'],
        ['influencers.php', 'influencers', 'fa-bullhorn', 'Creator reviews'],
        ['moderation.php', 'moderation', 'fa-star', 'Content moderation'],
    ]],
    'Operations' => ['icon' => 'fa-gears', 'items' => [
        ['orders.php', 'orders', 'fa-bag-shopping', 'Orders & delivery'],
        ['returns.php', 'returns', 'fa-rotate-left', 'Returns & refunds'],
        ['customers.php', 'customers', 'fa-users', 'Customers'],
        ['support.php', 'support', 'fa-headset', 'Support & disputes'],
    ]],
    'Finance & risk' => ['icon' => 'fa-chart-line', 'items' => [
        ['finance.php', 'finance', 'fa-indian-rupee-sign', 'Payouts & reconciliation'],
        ['bank-accounts.php', 'banks', 'fa-building-columns', 'Vendor bank review'],
        ['reports.php', 'reports', 'fa-chart-column', 'Reports'],
    ]],
    'Growth & content' => ['icon' => 'fa-seedling', 'items' => [
        ['events.php', 'events', 'fa-calendar-days', 'Events'],
        ['marketing.php', 'marketing', 'fa-ticket', 'Marketing'],
        ['content.php', 'content', 'fa-pen-to-square', 'Content'],
        ['banners.php', 'banners', 'fa-images', 'Home hero banners'],
    ]],
    'Governance' => ['icon' => 'fa-shield-halved', 'items' => [
        ['access.php', 'access', 'fa-user-shield', 'Roles & access'],
        ['audit.php', 'audit', 'fa-clock-rotate-left', 'Audit logs'],
        ['settings.php', 'settings', 'fa-gears', 'System settings'],
    ]],
];
?>
<aside class="ci-dash-sidebar" id="ac-sidebar" aria-label="Admin navigation">
  <a class="ci-sidebar-brand" href="/conscious/admin/" aria-label="Admin operations home">
    <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
    <div class="ci-sidebar-brand-text"><div class="ci-sidebar-brand-name">Workspace</div><div class="ci-sidebar-brand-role">Admin tools</div></div>
  </a>
  <div class="ci-sidebar-user">
    <div class="ci-sidebar-avatar"><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="ci-sidebar-user-info"><div class="ci-sidebar-user-name"><?= htmlspecialchars($adminDisplayName, ENT_QUOTES, 'UTF-8') ?></div><div class="ci-sidebar-user-email">Protected admin session</div></div>
  </div>
  <nav class="ci-sidebar-nav">
    <?php foreach ($groups as $group => $groupData): ?>
      <?php
      $groupIsActive = false;
      foreach ($groupData['items'] as $item) {
          if ($activePage === $item[1]) { $groupIsActive = true; break; }
      }
      $groupId = 'admin-nav-' . strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $group));
      ?>
      <section class="ac-nav-group<?= $groupIsActive ? ' is-open' : '' ?>" data-nav-group>
        <button class="ac-nav-group-toggle" type="button" data-nav-group-toggle aria-expanded="<?= $groupIsActive ? 'true' : 'false' ?>" aria-controls="<?= $groupId ?>">
          <i class="fa-solid <?= htmlspecialchars($groupData['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
          <span><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></span>
          <i class="fa-solid fa-chevron-down ac-nav-chevron" aria-hidden="true"></i>
        </button>
        <div class="ac-nav-group-items" id="<?= $groupId ?>"<?= $groupIsActive ? '' : ' hidden' ?>>
          <?php foreach ($groupData['items'] as [$href, $key, $icon, $label]): ?>
            <?php $route = pathinfo($href, PATHINFO_FILENAME) === 'index' ? '' : pathinfo($href, PATHINFO_FILENAME); ?>
            <a class="ci-sidebar-nav-item<?= $activePage === $key ? ' active' : '' ?>" href="/conscious/admin/<?= htmlspecialchars($route, ENT_QUOTES, 'UTF-8') ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>>
              <i class="fa-solid <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </nav>
  <div class="ac-side-foot">
    <a class="ci-sidebar-nav-item" href="/conscious/"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>View public website</span></a>
    <a class="ci-sidebar-nav-item" href="/conscious/admin/login"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Sign out</span></a>
  </div>
</aside>
