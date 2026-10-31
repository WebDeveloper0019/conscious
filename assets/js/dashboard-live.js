document.addEventListener('DOMContentLoaded', async () => {
    const messageEl = document.getElementById('vd-message');
    
    try {
        // WE ONLY FETCH DASHBOARD.PHP
        const response = await fetch('/conscious/api/vendor/dashboard.php');
        const result = await response.json();

        if (result.success && result.data) {
            const data = result.data;

            // Update UI Numbers
            document.getElementById('vd-new-orders').textContent = data.newOrders;
            document.getElementById('vd-products').textContent = data.activeProducts;
            document.getElementById('vd-payout').textContent = '₹' + data.payout;
            document.getElementById('vd-delivered').textContent = data.deliveredOrders;

            // Update Operational overview
            document.getElementById('vd-preparing').textContent = data.operational.preparing;
            document.getElementById('vd-ready').textContent = data.operational.ready;
            document.getElementById('vd-shipping').textContent = data.operational.shipping;

            // Update Badges
            document.getElementById('vd-store-status').textContent = 'Store is Live';
            document.getElementById('vd-store-status').className = 'vc-status success';
            
            document.getElementById('vd-verification-status').textContent = 'Verified';
            document.getElementById('vd-verification-status').className = 'vc-status success';

            if (messageEl) messageEl.style.display = 'none'; 

            // Tasks
            const tasksContainer = document.getElementById('vd-tasks');
            if (tasksContainer) {
                tasksContainer.innerHTML = `<div class="vc-empty" style="text-align:center; padding:20px; color:#666;">No pending tasks! You are all caught up.</div>`;
            }

        } else {
            if (messageEl) {
                messageEl.textContent = 'Error: ' + (result.message || 'Unknown error');
                messageEl.className = 'vc-callout amber';
                messageEl.style.display = 'block';
            }
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
});