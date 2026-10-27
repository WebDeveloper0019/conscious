<<<<<<< HEAD:frontend/modules/vendor/pages/packages.php
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Discover Creator Packages</h2>
        <div class="input-group" style="max-width: 300px;">
            <input type="search" id="vendor-search" class="form-control" placeholder="Search creators or platforms...">
            <button class="btn btn-outline-secondary" type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </div>

    <div id="vendor-package-grid" class="row g-4">
        <!-- Packages will load here -->
        <div class="col-12 text-center py-5" id="vendor-package-loading">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading packages...</p>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Book Package: <span id="modal-pkg-title"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="vendor-brief-form">
            <input type="hidden" id="modal-pkg-id">
            <input type="hidden" id="modal-pkg-price">
            
            <div class="alert alert-info py-2">
                <strong>Package Terms:</strong>
                <ul class="mb-0 mt-1 pl-3" style="font-size: 0.9rem;">
                    <li>Deliverables: <span id="modal-pkg-deliv"></span></li>
                    <li>Turnaround: <span id="modal-pkg-turn"></span> days</li>
                    <li>Usage Rights: <span id="modal-pkg-usage"></span></li>
                    <li>Cancellation: <span id="modal-pkg-cancel"></span></li>
                </ul>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Brand Brief (Required)</label>
                <textarea id="vendor-brief" class="form-control" rows="4" required placeholder="Describe your product, target audience, key messages, and any specific requirements for the creator..."></textarea>
                <div class="form-text">Creators need this information to create the best content for you.</div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div>
                    <span class="text-muted d-block" style="font-size: 0.85rem;">Total Amount</span>
                    <strong class="fs-4 text-success" id="modal-pkg-price-display"></strong>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" id="btn-checkout">
                    Proceed to Payment <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
=======
<section class="vc-card">
  <div class="vc-card-head"><div><h2>Creator packages</h2><p>Book a reviewed service from an approved creator. The price, GST, deliverables and cancellation terms are confirmed before secure payment.</p></div></div>
  <div class="vc-card-body">
    <label class="vc-field" for="vendor-package-search">Search by creator, platform or service
      <input class="vc-input" id="vendor-package-search" type="search" placeholder="For example: Instagram, reel, sustainable living">
    </label>
    <p id="vendor-package-message" role="status" aria-live="polite">Loading approved packages…</p>
    <div id="vendor-package-grid" class="row g-3"></div>
  </div>
</section>

<section class="vc-card mt-4">
  <div class="vc-card-head"><div><h2>Your creator collaborations</h2><p>Track payment and delivery status. Cancellation and dispute requests are reviewed by the Conscious India coordination team.</p></div></div>
  <div class="vc-card-body">
    <p id="vendor-package-orders-message" role="status" aria-live="polite">Loading your collaborations.</p>
    <div id="vendor-package-orders" class="row g-3"></div>
  </div>
</section>

<dialog class="vc-dialog" id="vendor-package-dialog" aria-labelledby="vendor-package-dialog-title">
  <form class="vc-dialog-body" id="vendor-package-form">
    <div class="vc-dialog-head">
      <div><small>Review before payment</small><h2 id="vendor-package-dialog-title">Creator package</h2></div>
      <button type="button" class="vc-btn small" data-close-package-dialog>Close</button>
    </div>
    <div class="vc-callout" id="vendor-package-quote"></div>
    <div class="vc-form-grid mt-3">
      <label class="vc-field">Campaign brief
        <textarea class="vc-input" name="brief" rows="5" minlength="20" maxlength="5000" required placeholder="Explain the campaign goal, audience, key message and expected content."></textarea>
      </label>
      <label class="vc-field">Campaign contact name
        <input class="vc-input" name="contact_name" minlength="2" maxlength="140" required>
      </label>
      <label class="vc-field">Contact email
        <input class="vc-input" name="contact_email" type="email" required>
      </label>
      <label class="vc-field">Contact mobile
        <input class="vc-input" name="contact_mobile" inputmode="numeric" pattern="[0-9 ]{10,15}">
      </label>
      <label class="vc-field">Products to feature (one per line)
        <textarea class="vc-input" name="target_products" rows="3" maxlength="1000"></textarea>
      </label>
      <label class="vc-field">Preferred dates (one per line)
        <textarea class="vc-input" name="requested_dates" rows="3" maxlength="500" placeholder="For example: 15 November 2026"></textarea>
      </label>
    </div>
    <label class="d-flex gap-2 align-items-start mt-3"><input name="accept_terms" type="checkbox" required> <span>I accept the package deliverables, usage rights and cancellation terms shown above.</span></label>
    <p id="vendor-package-form-message" class="mt-3" role="status" aria-live="assertive"></p>
    <div class="vc-form-actions">
      <button type="button" class="vc-btn" data-close-package-dialog>Cancel</button>
      <button type="submit" class="vc-btn primary">Reserve and pay securely</button>
    </div>
  </form>
</dialog>
>>>>>>> main:portals/modules/vendor/pages/packages.php
