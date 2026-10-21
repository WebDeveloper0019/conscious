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