document.addEventListener('DOMContentLoaded', async () => {
    const messageEl = document.getElementById('vd-message');
    
    try {
        // Fetching ONLY the new dashboard API we created
        const response = await fetch('/conscious/api/vendor/dashboard.php');
        const result = await response.json();

        if (result.success && result.data) {
            const data = result.data;

            // Update Top Numbers
            document.getElementById('vd-new-orders').textContent = data.newOrders;
            document.getElementById('vd-products').textContent = data.activeProducts;
            document.getElementById('vd-payout').textContent = '₹' + data.payout;
            document.getElementById('vd-delivered').textContent = data.deliveredOrders;

            // Update Operational Overview
            document.getElementById('vd-preparing').textContent = data.operational.preparing;
            document.getElementById('vd-ready').textContent = data.operational.ready;
            document.getElementById('vd-shipping').textContent = data.operational.shipping;
            
            // These are 0 for now until we build the features
            document.getElementById('vd-returns').textContent = data.operational.returns;
            document.getElementById('vd-low-stock').textContent = data.operational.lowStock;
            document.getElementById('vd-cod').textContent = data.operational.cod;

            // Update Status Badges
            const statusBadge = document.getElementById('vd-store-status');
            if (statusBadge) {
                statusBadge.textContent = 'Store is Live';
                statusBadge.className = 'vc-status success';
            }
            
            const verifyBadge = document.getElementById('vd-verification-status');
            if (verifyBadge) {
                verifyBadge.textContent = 'Verified Vendor';
                verifyBadge.className = 'vc-status success';
            }

            // Hide the yellow warning box
            if (messageEl) {
                messageEl.style.display = 'none'; 
            }

            // Update Tasks List (No tasks for now)
            const tasksContainer = document.getElementById('vd-tasks');
            if (tasksContainer) {
                tasksContainer.innerHTML = `<div class="vc-empty" style="text-align:center; padding:20px; color:#666;">No pending tasks! You are all caught up.</div>`;
            }

        } else {
            console.error("API Error:", result.message);
            if (messageEl) {
                messageEl.textContent = 'Failed to load data: ' + (result.message || 'Unknown error');
                messageEl.className = 'vc-callout amber';
                messageEl.style.display = 'block';
            }
        }
    } catch (error) {
        console.error("Network Error:", error);
        if (messageEl) {
            messageEl.textContent = 'Network error. Please refresh the page.';
            messageEl.className = 'vc-callout amber';
            messageEl.style.display = 'block';
        }
    }
});