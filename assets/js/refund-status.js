(function () {
    'use strict';

    window.pollRefundStatus = async function (orderNumber, onUpdate, opts) {
        opts = opts || {};
        const maxAttempts = opts.maxAttempts || 20;      // 20 × 15s = 5 min
        const intervalMs  = opts.intervalMs  || 15000;   // 15 seconds

        let attempt = 0;
        let lastState = null;

        async function tick() {
            attempt++;
            try {
                const res = await fetch(
                    '../api/user/refund-status.php?orderNumber=' + encodeURIComponent(orderNumber),
                    { credentials: 'include', headers: { Accept: 'application/json' } }
                );
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Failed');

                // Only call onUpdate if state actually changed
                const newState = JSON.stringify(data.refunds.map(r => r.status));
                if (newState !== lastState) {
                    lastState = newState;
                    onUpdate(data);
                }

                // Stop if all refunds are in terminal state
                if (data.summary.allComplete || (!data.summary.hasActiveRefund && attempt > 1)) {
                    return;
                }

                if (attempt >= maxAttempts) return;
                setTimeout(tick, intervalMs);
            } catch (err) {
                console.warn('Refund poll failed:', err);
                if (attempt < maxAttempts) setTimeout(tick, intervalMs * 2);
            }
        }

        tick();
    };
})();