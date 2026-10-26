<?php 
$pageTitle='Influencer Orders';
$pageSubtitle='Coordinate and process service orders';
$activePage='package-orders';
require __DIR__.'/layouts/head.php'; 
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h5 mb-0 text-dark">Active Service Orders</h3>
        <button class="btn btn-outline-primary btn-sm" onclick="loadOrders()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>

    <div id="orders-list" class="row g-4">
        <div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

<script>
    const API_URL = '../../../api/admin/package-orders.php';

    async function loadOrders() {
        const grid = document.getElementById('orders-list');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            const res = await fetch(API_URL);
            const data = await res.json();
            
            if (!data.orders || data.orders.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><h5 class="text-muted">No active package orders to coordinate.</h5></div>';
                return;
            }
            
            grid.innerHTML = '';
            data.orders.forEach(o => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-xl-4 mb-3';
                col.innerHTML = `
                    <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-1">${o.package_title}</h5>
                                <span class="badge bg-secondary">${o.status.replace('_', ' ').toUpperCase()}</span>
                            </div>
                            <p class="text-muted small mb-2">Vendor: ${o.vendor_name || 'Test Vendor'}<br>Creator: @${o.influencer_handle}</p>
                            
                            <div class="alert alert-secondary py-2 mt-2 mb-3" style="font-size: 0.85rem;">
                                <strong>Brief:</strong> ${o.brief}
                            </div>
                            
                            <div class="mt-auto pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="text-success fs-5">₹${o.total_amount}</strong>
                                    <small class="text-muted">GST: ₹${o.gst_amount}</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm" id="status-${o.id}">
                                        <option value="contact_pending" ${o.status === 'contact_pending' ? 'selected' : ''}>Contact Pending</option>
                                        <option value="scheduled" ${o.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                        <option value="in_progress" ${o.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                        <option value="delivered" ${o.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                        <option value="completed" ${o.status === 'completed' ? 'selected' : ''}>Completed (Pay Creator)</option>
                                        <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>Cancelled / Refunded</option>
                                    </select>
                                    <button onclick="updateStatus('${o.id}')" class="btn btn-primary btn-sm" style="white-space: nowrap;">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        } catch (e) {
            grid.innerHTML = '<div class="col-12 text-center py-5"><h5 class="text-danger">Failed to load.</h5></div>';
        }
    }

    async function updateStatus(id) {
        const newStatus = document.getElementById('status-' + id).value;
        // Notes popup - Senior's requirement
        const note = prompt("Enter coordination note (e.g., 'Contacted vendor, shoot scheduled'):");
        if (note === null) return; 
        
        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: newStatus, notes: note || 'Status updated by admin' })
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

<?php require __DIR__.'/layouts/foot.php'; ?>