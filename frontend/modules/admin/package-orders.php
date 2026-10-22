<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Influencer Order Coordination</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="p-4 bg-light">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Influencer Package Orders</h2>
        <button class="btn btn-outline-primary" onclick="loadOrders()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>
    <div id="orders-list" class="row g-4">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

<script>
    // Point to the NEW package-orders.php file
    const API_URL = '../../../api/admin/package-orders.php';

    async function loadOrders() {
        const grid = document.getElementById('orders-list');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            const res = await fetch(API_URL);
            const data = await res.json();
            
            if (!data.orders || data.orders.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-muted">No active paid package orders to coordinate.</h4></div>';
                return;
            }
            
            grid.innerHTML = '';
            data.orders.forEach(o => {
                const col = document.createElement('div');
                col.className = 'col-md-6';
                col.innerHTML = `
                    <div class="card shadow-sm border-0 border-start border-4 border-success">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-1">${o.package_title}</h5>
                            <p class="text-muted small mb-2">Vendor: ${o.vendor_name || 'Test Vendor'} | Creator: @${o.influencer_handle}</p>
                            
                            <div class="alert alert-secondary py-2 mt-3 mb-3" style="font-size: 0.9rem;">
                                <strong>Brief:</strong> ${o.brief}
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-success fs-5">₹${o.amount_inr}</strong>
                                <div class="d-flex gap-2">
                                    <button onclick="processOrder('${o.id}', 'refund')" class="btn btn-outline-danger btn-sm">Refund</button>
                                    <button onclick="processOrder('${o.id}', 'complete')" class="btn btn-success btn-sm">Mark Completed & Pay Creator</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        } catch (e) {
            grid.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-danger">Failed to load.</h4></div>';
            console.error(e);
        }
    }

    async function processOrder(id, action) {
        if (!confirm(`Are you sure you want to ${action} this order?`)) return;
        
        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, action })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            alert(data.message);
            loadOrders();
        } catch (e) {
            alert('Error: ' + e.message);
        }
    }

    loadOrders();
</script>
</body>
</html>