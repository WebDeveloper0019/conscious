<?php $productId = trim((string) ($_GET['id'] ?? '')); ?>
<a class="ac-record-back" href="/conscious/admin/catalog"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to products</a>
<div class="ac-callout info mt-3" id="admin-catalog-message" role="status">Loading product submission.</div>
<section class="ac-record-layout mt-3" id="admin-catalog-detail" data-record-id="<?= htmlspecialchars($productId, ENT_QUOTES, 'UTF-8') ?>">
  <div class="ac-card ac-record-primary">
    <div class="ac-head"><div><span class="ac-kicker">PRODUCT RECORD</span><h2 id="admin-catalog-name">Product review</h2></div><span class="ac-status" id="admin-catalog-state"></span></div>
    <div class="ac-body">
      <section class="ac-record-section"><h3>Submitted details</h3><div class="ac-record-facts" id="admin-catalog-summary"></div></section>
      <section class="ac-record-section"><h3>Images and scan status</h3><div id="admin-catalog-images"></div></section>
    </div>
  </div>
  <aside class="ac-card ac-record-aside">
    <div class="ac-head"><h2>Review decision</h2></div>
    <div class="ac-body">
      <div class="ac-field"><label for="admin-catalog-notes">Vendor-visible review note</label><textarea class="ac-textarea" id="admin-catalog-notes" minlength="10" maxlength="1000" placeholder="Describe the decision and required corrections precisely."></textarea></div>
      <div class="ac-form-actions" id="admin-catalog-actions"></div>
    </div>
  </aside>
</section>
