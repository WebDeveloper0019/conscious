<?php $customerId = trim((string) ($_GET['id'] ?? '')); ?>
<a class="ac-record-back" href="/conscious/admin/customers"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to customers</a>
<div class="ac-callout info mt-3" id="ac-customer-status" role="status">Loading customer record.</div>
<section class="ac-record-layout mt-3" id="ac-customer-record" data-record-id="<?= htmlspecialchars($customerId, ENT_QUOTES, 'UTF-8') ?>">
  <div class="ac-card ac-record-primary">
    <div class="ac-head"><div><span class="ac-kicker">CUSTOMER RECORD</span><h2 id="ac-customer-name">Customer review</h2></div><span class="ac-status" id="ac-customer-state"></span></div>
    <div class="ac-body"><div class="ac-record-facts" id="ac-customer-facts"></div><div class="ac-callout mt-3">Contact details remain masked on this operational screen. Sensitive data access requires a recorded purpose and is separately audited.</div></div>
  </div>
  <aside class="ac-card ac-record-aside">
    <div class="ac-head"><h2>Account decision</h2></div>
    <div class="ac-body">
      <div class="ac-field"><label for="ac-customer-reason">Reason for account action</label><textarea class="ac-textarea" id="ac-customer-reason" minlength="10" maxlength="1000" placeholder="Describe specific evidence and policy basis."></textarea></div>
      <div class="ac-form-actions"><button class="ac-btn" type="button" data-customer-status="active">Restore active</button><button class="ac-btn" type="button" data-customer-status="restricted">Restrict</button><button class="ac-btn danger" type="button" data-customer-status="blocked">Block</button></div>
      <p class="mt-2">Repeated-return flags require human review. <a href="/conscious/admin/returns">Review return flags</a></p>
    </div>
  </aside>
</section>
