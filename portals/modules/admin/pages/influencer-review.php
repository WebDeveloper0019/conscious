<?php $creatorId = trim((string) ($_GET['id'] ?? '')); ?>
<a class="ac-record-back" href="/conscious/admin/influencers"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to creators</a>
<div class="ac-callout info mt-3" id="if-review-message" role="status">Loading creator application.</div>
<section class="ac-record-layout mt-3" id="if-review-record" data-record-id="<?= htmlspecialchars($creatorId, ENT_QUOTES, 'UTF-8') ?>">
  <div class="ac-card ac-record-primary">
    <div class="ac-head"><div><span class="ac-kicker">CREATOR APPLICATION</span><h2 id="if-review-name">Creator review</h2><p id="if-review-profile"></p></div><span class="ac-status" id="if-review-state"></span></div>
    <div class="ac-body"><section class="ac-record-section"><h3>Submitted documents</h3><div id="if-review-documents"></div></section></div>
  </div>
  <aside class="ac-card ac-record-aside">
    <div class="ac-head"><h2>Review decision</h2></div>
    <div class="ac-body"><div class="ac-field"><label for="if-review-reason">Review note or rejection reason</label><textarea class="ac-textarea" id="if-review-reason" maxlength="500" placeholder="Explain what needs to change or why the application is rejected."></textarea></div><div class="ac-form-actions"><button class="ac-btn primary" type="button" data-if-decision="approve">Approve</button><button class="ac-btn" type="button" data-if-decision="request_changes">Request changes</button><button class="ac-btn danger" type="button" data-if-decision="reject">Reject</button><button class="ac-btn" type="button" data-if-decision="suspend">Suspend</button><button class="ac-btn" type="button" data-if-decision="reactivate">Reactivate</button></div></div>
  </aside>
</section>
