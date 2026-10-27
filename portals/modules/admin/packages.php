<?php 
$pageTitle='Service Packages';
$pageSubtitle='Review and approve creator packages';
$activePage='packages';
require __DIR__.'/layouts/head.php'; 
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h5 mb-0 text-dark">Pending Approvals</h3>
        <button class="btn btn-outline-primary btn-sm" onclick="loadPackages()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>

    <div id="admin-package-list" class="row g-4">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>
</div>

<script>
    const API_URL = '../../../api/admin/packages.php';

    async function loadPackages() {
        const grid = document.getElementById('admin-package-list');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            const res = await fetch(API_URL, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            
            if (!data.packages || data.packages.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><h5 class="text-muted">No pending packages to review.</h5></div>';
                return;
            }
            
            grid.innerHTML = '';
            data.packages.forEach(pkg => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-xl-4';
                
                const deliverables = (pkg.deliverables_json || []).map(d => `<li>${d}</li>`).join('');
                const platforms = (pkg.platforms_json || []).join(', ');
                
                col.innerHTML = `
                    <div class="card border-start border-4 border-warning h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">${pkg.title}</h5>
                                    <h6 class="text-muted mb-2">By ${pkg.full_name} (@${pkg.handle})</h6>
                                </div>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </div>
                            
                            <p class="card-text text-muted small">${pkg.description}</p>
                            
                            <div class="row mb-3 small bg-light p-2 rounded">
                                <div class="col-6"><strong>Price:</strong> ₹${pkg.price_inr}</div>
                                <div class="col-6"><strong>Turnaround:</strong> ${pkg.turnaround_days} days</div>
                                <div class="col-12 mt-2"><strong>Platforms:</strong> ${platforms}</div>
                                <div class="col-12 mt-1"><strong>Deliverables:</strong> <ul class="mb-0 pl-3">${deliverables}</ul></div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-auto">
                                <button onclick="processPackage('${pkg.id}', 'approve')" class="btn btn-success flex-fill">Approve</button>
                                <button onclick="processPackage('${pkg.id}', 'reject')" class="btn btn-outline-danger flex-fill">Reject</button>
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

    async function processPackage(id, action) {
        if (!confirm(`Are you sure you want to ${action} this package?`)) return;
        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, action: action })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            alert(data.message);
            loadPackages();
        } catch (e) {
            alert('Error: ' + e.message);
        }
    }

    loadPackages();
</script>

<?php require __DIR__.'/layouts/foot.php'; ?>