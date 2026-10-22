<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Creator Marketplace - Conscious Indi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .card { border: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .compare-bar { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 15px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); display: none; z-index: 1000; }
    </style>
</head>
<body>

<div class="container py-5">
    <h1 class="mb-4">Discover Creator Packages</h1>
    
    <!-- Filters -->
    <div class="card p-3 mb-4">
        <form id="filter-form" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="Search by creator name or package title...">
            </div>
            <div class="col-md-3">
                <select name="platform" class="form-select">
                    <option value="">All Platforms</option>
                    <option value="Instagram">Instagram</option>
                    <option value="YouTube">YouTube</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="max_price" class="form-control" placeholder="Max Price (₹)">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>

    <!-- Package Grid -->
    <div id="package-grid" class="row g-4">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

<!-- Comparison Bar -->
<div class="compare-bar" id="compare-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div><strong>Compare:</strong> <span id="compare-names"></span></div>
        <button class="btn btn-dark btn-sm" onclick="showComparison()">Compare Packages</button>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Book Package: <span id="modal-pkg-title"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="checkout-form">
            <input type="hidden" id="modal-pkg-id">
            <input type="hidden" id="modal-pkg-price">
            
            <div class="alert alert-info py-2 mb-4">
                <strong>Package Details:</strong>
                <ul class="mb-0 mt-1 pl-3" style="font-size: 0.9rem;">
                    <li>Deliverables: <span id="modal-pkg-deliv"></span></li>
                    <li>Turnaround: <span id="modal-pkg-turn"></span> days</li>
                    <li>Usage Rights: <span id="modal-pkg-usage"></span></li>
                </ul>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Brand Brief (Required)</label>
                <textarea id="vendor-brief" class="form-control" rows="4" required minlength="20" placeholder="Describe your product, target audience, key messages, and requirements..."></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div>
                    <span class="text-muted d-block" style="font-size: 0.85rem;">Total Amount</span>
                    <strong class="fs-4 text-success" id="modal-pkg-price-display"></strong>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" id="btn-pay">
                    Pay with Razorpay <i class="fa-solid fa-lock ms-1"></i>
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const API = '../../../api/vendor/';
    let compareList = [];
    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));

    // 1. Load Packages
    async function loadPackages(url = API + 'discover.php') {
        const grid = document.getElementById('package-grid');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            const res = await fetch(url);
            const data = await res.json();
            
            if (!data.packages || data.packages.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-muted">No packages found matching your criteria.</h4></div>';
                return;
            }
            
            grid.innerHTML = '';
            data.packages.forEach(pkg => {
                const avatar = pkg.avatar_url 
                    ? `<img src="${API}${pkg.avatar_url}" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">` 
                    : `<div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;">${pkg.full_name.charAt(0)}</div>`;
                
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                ${avatar}
                                <div><h6 class="mb-0 fw-bold">${pkg.full_name}</h6><small class="text-muted">@${pkg.handle}</small></div>
                            </div>
                            <h5 class="card-title fw-bold">${pkg.title}</h5>
                            <p class="text-muted small">${pkg.description.substring(0, 80)}...</p>
                            <div class="d-flex justify-content-between align-items-end mt-3">
                                <div>
                                    <div class="fs-4 fw-bold text-dark">₹${pkg.price_inr}</div>
                                    <small class="text-muted">${pkg.turnaround_days} days</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleCompare('${pkg.id}', '${pkg.title}')"><i class="fa-solid fa-code-compare"></i></button>
                                    <button class="btn btn-primary btn-sm" onclick='openCheckout(${JSON.stringify(pkg)})'>Book</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        } catch (e) {
            console.error(e);
        }
    }

    // 2. Search Filter
    document.getElementById('filter-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const params = new URLSearchParams(formData).toString();
        loadPackages(API + 'discover.php?' + params);
    });

    // 3. Comparison Logic
    function toggleCompare(id, title) {
        if (compareList.find(p => p.id === id)) {
            compareList = compareList.filter(p => p.id !== id);
        } else {
            if (compareList.length >= 3) return alert('You can compare up to 3 packages.');
            compareList.push({ id, title });
        }
        
        const bar = document.getElementById('compare-bar');
        bar.style.display = compareList.length > 0 ? 'block' : 'none';
        document.getElementById('compare-names').textContent = compareList.map(p => p.title).join(', ');
    }

    function showComparison() {
        alert("Comparing: \n" + compareList.map(p => p.title).join('\n'));
        // A full implementation would open a side-by-side modal here
    }

    // 4. Open Checkout Modal
    function openCheckout(pkg) {
        document.getElementById('modal-pkg-id').value = pkg.id;
        document.getElementById('modal-pkg-title').textContent = pkg.title;
        document.getElementById('modal-pkg-price-display').textContent = '₹' + pkg.price_inr;
        document.getElementById('modal-pkg-deliv').textContent = pkg.deliverables_json.join(', ');
        document.getElementById('modal-pkg-turn').textContent = pkg.turnaround_days;
        document.getElementById('modal-pkg-usage').textContent = pkg.usage_rights;
        checkoutModal.show();
    }

    // 5. Handle Checkout
    document.getElementById('checkout-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btn-pay');
        btn.disabled = true;
        
        try {
            const res = await fetch(API + 'checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    package_id: document.getElementById('modal-pkg-id').value,
                    brief: document.getElementById('vendor-brief').value
                })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);

            // RAZORPAY INTEGRATION
            const options = {
                key: "YOUR_RAZORPAY_KEY_ID", // Replace with your actual Key ID
                amount: data.order.amount_paise,
                currency: "INR",
                name: "Conscious Indi",
                description: "Package Payment",
                order_id: data.order.razorpay_order_id,
                handler: function (response) {
                    // Send payment ID to backend for verification
                    fetch(API + 'verify-payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature,
                            internal_order_id: data.order.id
                        })
                    }).then(r => r.json()).then(res => {
                        if(res.success) {
                            alert('Payment successful! The creator will be notified.');
                            window.location.reload();
                        } else {
                            alert('Verification failed: ' + res.message);
                        }
                    });
                }
            };
            const rzp = new Razorpay(options);
            rzp.open();

        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
        }
    });

    loadPackages();
</script>
</body>
</html>