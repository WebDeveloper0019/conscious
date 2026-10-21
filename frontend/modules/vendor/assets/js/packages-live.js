(function() {
    'use strict';
    
    var API_BASE = '../../../../api/'; // Adjust if your path is different
    var grid = document.getElementById('vendor-package-grid');
    var checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    var form = document.getElementById('vendor-brief-form');
    
    // 1. Fetch and render packages
    async function loadPackages() {
        try {
            var res = await fetch(API_BASE + 'public/packages.php', { headers: { Accept: 'application/json' } });
            var data = await res.json();
            
            document.getElementById('vendor-package-loading').remove();
            
            if (!data.packages || data.packages.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No active packages found. Check back later!</p></div>';
                return;
            }
            
            data.packages.forEach(function(pkg) {
                var col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                
                var avatarHtml = pkg.avatar_url 
                    ? `<img src="${API_BASE}public/${pkg.avatar_url}" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">` 
                    : `<div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:14px;">${pkg.full_name.charAt(0)}</div>`;
                
                var platforms = (pkg.platforms_json || []).map(p => `<span class="badge bg-light text-dark border me-1">${p}</span>`).join('');
                var deliverables = (pkg.deliverables_json || []).map(d => `<li>${d}</li>`).join('');

                col.innerHTML = `
                    <div class="card h-100 shadow-sm border-0" style="border-top: 4px solid var(--saffron, #ff9933) !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                ${avatarHtml}
                                <div>
                                    <h6 class="mb-0 fw-bold">${pkg.full_name}</h6>
                                    <small class="text-muted">@${pkg.handle}</small>
                                </div>
                            </div>
                            <h5 class="card-title fw-bold fs-6 mb-2">${pkg.title}</h5>
                            <p class="card-text text-muted" style="font-size: 0.85rem;">${pkg.description.substring(0, 80)}...</p>
                            
                            <div class="mb-2">${platforms}</div>
                            <ul class="text-muted pl-3 mb-3" style="font-size: 0.8rem;">
                                ${deliverables}
                            </ul>
                            
                            <div class="d-flex justify-content-between align-items-end mt-auto">
                                <div>
                                    <div class="fs-4 fw-bold text-dark">₹${pkg.price_inr}</div>
                                    <small class="text-muted">${pkg.turnaround_days} days delivery</small>
                                </div>
                                <button class="btn btn-outline-primary btn-sm book-btn" 
                                    data-id="${pkg.id}" 
                                    data-title="${pkg.title}" 
                                    data-price="${pkg.price_inr}"
                                    data-deliv="${(pkg.deliverables_json || []).join(', ')}"
                                    data-turn="${pkg.turnaround_days}"
                                    data-usage="${pkg.usage_rights}"
                                    data-cancel="${pkg.cancellation_terms}">Book Now</button>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
            
            // Attach click events to buttons
            document.querySelectorAll('.book-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('modal-pkg-id').value = this.dataset.id;
                    document.getElementById('modal-pkg-title').textContent = this.dataset.title;
                    document.getElementById('modal-pkg-deliv').textContent = this.dataset.deliv;
                    document.getElementById('modal-pkg-turn').textContent = this.dataset.turn;
                    document.getElementById('modal-pkg-usage').textContent = this.dataset.usage;
                    document.getElementById('modal-pkg-cancel').textContent = this.dataset.cancel;
                    document.getElementById('modal-pkg-price-display').textContent = '₹' + this.dataset.price;
                    checkoutModal.show();
                });
            });
            
        } catch (e) {
            document.getElementById('vendor-package-loading').innerHTML = '<p class="text-danger">Failed to load packages. Please try again.</p>';
            console.error(e);
        }
    }
    
    // 2. Handle Checkout Submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        var btn = document.getElementById('btn-checkout');
        var brief = document.getElementById('vendor-brief').value.trim();
        var pkgId = document.getElementById('modal-pkg-id').value;
        
        if (brief.length < 20) {
            alert('Please provide a more detailed brief (at least 20 characters).');
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = 'Processing...';
        
        try {
            var res = await fetch(API_BASE + 'vendor/checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ package_id: pkgId, brief: brief }),
                credentials: 'same-origin'
            });
            
            var data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Checkout failed.');
            
            // --- RAZORPAY INTEGRATION GOES HERE ---
            // Normally, you would now initialize Razorpay using the order ID and amount.
            alert('Order Created! ID: ' + data.order.id + '\n(Ready for Razorpay integration)');
            
            checkoutModal.hide();
            form.reset();
            
        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Proceed to Payment <i class="fa-solid fa-arrow-right ms-1"></i>';
        }
    });

    // Initialize
    loadPackages();
})();