<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Package Approvals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f4f6f8; font-family: sans-serif; }
        .card { border: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Package Approvals</h2>
            <p class="text-muted mb-0">Review creator packages before they go live.</p>
        </div>
        <button class="btn btn-outline-primary" onclick="loadPendingPackages()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>

    <div id="admin-package-list" class="row g-4">
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading pending packages...</p>
        </div>
    </div>
</div>

<script>
    const API_URL = '../../../api/admin/packages.php';

    async function loadPendingPackages() {
        const grid = document.getElementById('admin-package-list');
        grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';
        
        try {
            const res = await fetch(API_URL, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            
            if (!data.packages || data.packages.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-muted">No pending packages to review.</h4></div>';
                return;
            }
            
            grid.innerHTML = '';
            data.packages.forEach(pkg => {
                const col = document.createElement('div');
                col.className = 'col-md-6';
                
                const deliverables = (pkg.deliverables_json || []).map(d => `<li>${d}</li>`).join('');
                const platforms = (pkg.platforms_json || []).join(', ');
                
                col.innerHTML = `
                    <div class="card border-start border-4 border-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">${pkg.title}</h5>
                                    <h6 class="text-muted mb-2">By ${pkg.full_name} (@${pkg.handle})</h6>
                                </div>
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                            </div>
                            
                            <p class="card-text text-muted small">${pkg.description}</p>
                            
                            <div class="row mb-3 small">
                                <div class="col-6"><strong>Price:</strong> ₹${pkg.price_inr}</div>
                                <div class="col-6"><strong>Turnaround:</strong> ${pkg.turnaround_days} days</div>
                                <div class="col-12 mt-2"><strong>Platforms:</strong> ${platforms}</div>
                                <div class="col-12 mt-1"><strong>Deliverables:</strong> <ul class="mb-0 pl-3">${deliverables}</ul></div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button onclick="processPackage('${pkg.id}', 'approve')" class="btn btn-success flex-fill">Approve & Activate</button>
                                <button onclick="processPackage('${pkg.id}', 'reject')" class="btn btn-outline-danger flex-fill">Reject</button>
                            </div>
                        </div>
                    </div>
                `;
                grid.appendChild(col);
            });
        } catch (e) {
            grid.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-danger">Failed to load. Check console.</h4></div>';
            console.error(e);
        }
    }

    async function processPackage(id, action) {
        if (!confirm(`Are you sure you want to ${action} this package?`)) return;
        
        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id: id, action: action })
            });
            const data = await res.json();
            
            if (!res.ok) throw new Error(data.message);
            
            alert(data.message);
            loadPendingPackages(); // Reload list
            
        } catch (e) {
            alert('Error: ' + e.message);
        }
    }

    // Load on page start
    loadPendingPackages();
</script>

</body>
</html>