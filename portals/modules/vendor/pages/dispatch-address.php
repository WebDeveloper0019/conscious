<section class="vc-workflow-intro compact single-task" aria-labelledby="dispatch-intro-title">
  <div class="vc-workflow-icon"><i class="fa-solid fa-location-dot"></i></div>
  <div><span class="vc-section-kicker">FULFILMENT LOCATION</span><h2 id="dispatch-intro-title">Where orders are prepared and collected</h2><p>This operational address is used for fulfilment and pickup. It is separate from your public store profile.</p></div>
</section>
<section class="vc-page-actions"><div><span class="vc-status info" id="vs-status">Loading your dispatch address…</span></div></section>
<section class="vc-card vc-focused-form">
  <div class="vc-card-head"><div><h2>Primary dispatch address</h2><span class="vc-help">Used for pickup and fulfilment</span></div></div>
  <form class="vc-card-body vc-form-stack" id="vs-dispatch">
    <div class="vc-field"><label>Address Line 1</label><input class="vc-input" name="address_line1" required minlength="10" maxlength="180" placeholder="e.g., Workshop 12, Artisan Lane"><small class="vc-help">Enter the physical location where packages are prepared or collected.</small></div>
    <div class="vc-field"><label>Address Line 2 (Optional)</label><input class="vc-input" name="address_line2" maxlength="180" placeholder="e.g., Near City Market"></div>
    <div class="vc-grid two">
      <div class="vc-field"><label>City</label><input class="vc-input" name="city" required minlength="2" maxlength="80" list="india-city-suggestions" placeholder="Start typing or choose a city"></div>
      <div class="vc-field"><label>District (Optional)</label><input class="vc-input" name="district" maxlength="80" list="india-city-suggestions" placeholder="Start typing or choose a district"></div>
      <div class="vc-field"><label>State or UT</label><select class="vc-select" name="state" required><option value="">Choose state or UT</option><?php require __DIR__.'/../components/india-states-options.php'; ?></select></div>
      <div class="vc-field"><label>PIN Code</label><input class="vc-input" name="pincode" required pattern="[1-9][0-9]{5}" maxlength="6" inputmode="numeric" placeholder="e.g., 302001"></div>
    </div>
    <p class="vc-help">Delivery zones, location rates, exclusions and COD rules are configured separately in Shipping &amp; delivery.</p>
    <div class="vc-form-actions"><span class="vc-save-confirmation" data-save-confirmation role="status" hidden></span><button class="vc-btn primary" type="submit"><i class="fa-solid fa-location-dot"></i> Save dispatch address</button></div>
  </form>
</section>
<?php require __DIR__.'/../components/india-city-suggestions.php'; ?>
