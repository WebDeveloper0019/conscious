<div class="avr-shell">
  <a class="avr-back" href="/conscious/admin/vendors"><i class="fa-solid fa-chevron-left"></i> Back to vendor applications</a>
  <div class="ac-callout info" id="avr-message" role="status">Loading the complete application…</div>
  <section class="ac-card av-review">
    <header class="av-review-header"><div><span class="ac-kicker">FULL APPLICATION</span><h2 id="avr-title">Vendor application</h2><p id="avr-summary"></p></div><span id="avr-status"></span></header>
    <div class="av-review-body">
      <div class="av-identity-grid" id="avr-identity"></div>
      <div class="av-guidance"><i class="fa-solid fa-shield-halved"></i><p><b>Review the complete submission.</b> Check the profile information, every onboarding section, and every clean document before deciding.</p></div>
      <div class="av-section-heading"><div><span class="ac-kicker">ONBOARDING DETAILS</span><h3>Submitted information</h3></div><span id="avr-section-count"></span></div>
      <div class="avr-sections" id="avr-sections"></div>
      <div class="av-section-heading"><div><span class="ac-kicker">DOCUMENTS</span><h3>Identity and business proof</h3></div><span id="avr-document-count"></span></div>
      <div class="av-document-list" id="avr-documents"></div>
      <div class="av-decision-box" id="avr-decision">
        <div class="av-decision-readiness" id="avr-readiness" role="status"></div>
        <div class="av-review-actions" id="avr-actions"></div>
      </div>
    </div>
  </section>
  <dialog class="ac-dialog av-reason-dialog" id="avr-reason-dialog">
    <form id="avr-reason-form" novalidate>
      <div class="ac-dialog-head"><div><span class="ac-kicker">VENDOR-VISIBLE DECISION</span><h2 id="avr-reason-title">Provide a reason</h2></div><button class="ac-btn small" id="avr-reason-close" type="button" aria-label="Cancel and close"><i class="fa-solid fa-xmark"></i></button></div>
      <div class="ac-body"><p class="av-reason-description" id="avr-reason-description"></p><label class="ac-field" for="avr-reason"><span>Reason shown to the vendor</span><textarea class="ac-textarea" id="avr-reason" minlength="10" maxlength="1000" placeholder="Explain exactly what must be corrected and what the vendor should submit next."></textarea><small class="av-field-error" id="avr-reason-error" role="alert" hidden></small></label></div>
      <div class="ac-form-actions av-dialog-actions"><button class="ac-btn" id="avr-reason-cancel" type="button">Cancel</button><button class="ac-btn primary" id="avr-reason-confirm" type="submit">Confirm</button></div>
    </form>
  </dialog>
  <dialog class="ac-dialog av-confirm-dialog" id="avr-confirm-dialog">
    <form id="avr-confirm-form">
      <div class="ac-dialog-head">
        <div><span class="ac-kicker">FINAL ADMIN DECISION</span><h2 id="avr-confirm-title">Approve vendor application</h2></div>
        <button class="ac-btn small" id="avr-confirm-close" type="button" aria-label="Cancel and close"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="ac-body">
        <div class="av-confirm-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
        <p id="avr-confirm-description">Confirm that the required information and documents have been reviewed.</p>
        <div class="ac-callout warning" id="avr-confirm-warning" hidden></div>
      </div>
      <div class="ac-form-actions av-dialog-actions">
        <button class="ac-btn" id="avr-confirm-cancel" type="button">Cancel</button>
        <button class="ac-btn primary" id="avr-confirm-submit" type="submit"><i class="fa-solid fa-check"></i> Approve and activate</button>
      </div>
    </form>
  </dialog>
</div>
