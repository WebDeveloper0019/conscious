/* Conscious Indi — Back-in-stock / price-drop subscription widget. */
'use strict';

(function () {
    // Detect current depth: root page → 'api/', subfolder → '../api/'
    var _path = window.location.pathname;
    var _prefix = /\/customer\/|\/vendor\/|\/admin\/|\/influencer\//.test(_path) ? '../api/' : 'api/';
    var API = new URL(_prefix + 'user/stock-subscriptions.php', window.location.href).href;
    function csrf() {
        var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    /**
     * Attach to any button with data-stock-sub="back_in_stock|price_drop"
     * Attributes:
     *   data-product-id
     *   data-variant-id (optional)
     *   data-target-price (optional, for price_drop)
     *   data-label-success (optional, custom success text)
     */
    function attach(btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            var type = btn.getAttribute('data-stock-sub');
            var productId = btn.getAttribute('data-product-id');
            if (!type || !productId) return;

            var payload = {
                product_id: productId,
                subscription_type: type
            };

            var vid = btn.getAttribute('data-variant-id');
            if (vid) payload.variant_id = vid;

            if (type === 'price_drop') {
                var tp = parseFloat(btn.getAttribute('data-target-price'));
                if (!tp || tp <= 0) {
                    var input = parseFloat(prompt('Enter your target price (₹):'));
                    if (!input || input <= 0) return;
                    tp = input;
                }
                payload.target_price = tp;
            }

            btn.disabled = true;
            var original = btn.innerHTML;
            btn.innerHTML = 'Saving…';

            fetch(API, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success === false) throw new Error(d.message || 'Failed');
                var msg = btn.getAttribute('data-label-success') || d.message || 'Subscribed!';
                btn.innerHTML = msg;
                btn.style.background = '#10B981';
                btn.style.color = '#fff';
                btn.style.borderColor = '#10B981';
                btn.disabled = true;
            })
            .catch(function (err) {
                alert('Error: ' + err.message);
                btn.innerHTML = original;
                btn.disabled = false;
            });
        });
    }

    function init() {
        document.querySelectorAll('[data-stock-sub]').forEach(attach);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();