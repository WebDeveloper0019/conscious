<div data-live-returns>
  <section class="cp-list-head"><div><span class="cx-kicker">After-purchase care</span><h2>Returns & refunds</h2><p>Request a return or replacement for an eligible delivered item and follow vendor decisions.</p></div><button class="cc-btn primary" type="button" data-return-open><i class="fa-solid fa-plus"></i> Start a request</button></section>
  <section class="cp-return-list" data-return-list aria-live="polite"><p>Loading your return requests…</p></section>
  <section class="cp-policy-cards"><article><i class="fa-solid fa-calendar-check"></i><h3>Check the item policy</h3><p>The vendor policy captured at checkout applies. Review it before submitting your request.</p></article><article><i class="fa-solid fa-camera"></i><h3>Share useful evidence</h3><p>Add clear photos for damage, defects or incorrect items. Files remain private while scanned.</p></article><article><i class="fa-solid fa-money-check-dollar"></i><h3>Follow the resolution</h3><p>Vendor review and any refund decision are shown against your request and order.</p></article></section>
  <dialog class="cc-dialog" data-return-dialog><div class="cc-dialog-head"><h2>Start a return or replacement</h2><button class="cc-btn small" type="button" data-return-close aria-label="Close request form"><i class="fa-solid fa-xmark"></i></button></div><form class="cc-card-body" data-return-form>
    <label class="cc-field">Delivered item <select class="cc-select" name="order_item_id" required><option value="">Choose a delivered item</option></select></label>
    <p data-return-policy></p>
    <label class="cc-field">Request <select class="cc-select" name="request_type" required><option value="return">Return and refund review</option><option value="replacement">Replacement review</option></select></label>
    <label class="cc-field">Quantity <input class="cc-input" name="quantity" type="number" min="1" max="1" value="1" required></label>
    <label class="cc-field">Reason <select class="cc-select" name="reason_code" required><option value="">Choose a reason</option><option value="damaged">Damaged on arrival</option><option value="defective">Defective item</option><option value="wrong_item">Incorrect item</option><option value="not_as_described">Different from description</option><option value="other">Other</option></select></label>
    <label class="cc-field">What happened? <textarea class="cc-textarea" name="description" minlength="20" maxlength="1200" required></textarea></label>
    <button class="cc-btn primary" type="submit">Submit for vendor review</button><p role="status" data-return-form-status></p>
  </form></dialog>
  <dialog class="cc-dialog" data-evidence-dialog><div class="cc-dialog-head"><h2>Add photographic evidence</h2><button class="cc-btn small" type="button" data-evidence-close aria-label="Close evidence form"><i class="fa-solid fa-xmark"></i></button></div><form class="cc-card-body" data-evidence-form>
    <p>JPEG, PNG or WEBP, up to 5 MB each. Uploaded files stay private and await a security scan.</p>
    <label class="cc-field">Photograph <input class="cc-input" type="file" name="file" accept="image/jpeg,image/png,image/webp" required></label>
    <label class="cc-field">Caption (optional) <input class="cc-input" name="caption" maxlength="255"></label>
    <button class="cc-btn primary" type="submit">Upload photo</button><p role="status" data-evidence-form-status></p>
  </form></dialog>
</div>
