/* Conscious Indi — Vendor delivery OTP: generate, verify, status. */
'use strict';

(function () {
    var API = '../api/vendor/';

    function csrf() {
        var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function showResult(obj) {
        var el = document.getElementById('otpTestResult');
        if (!el) { alert(JSON.stringify(obj, null, 2)); return; }
        el.style.display = 'block';
        el.textContent = typeof obj === 'string' ? obj : JSON.stringify(obj, null, 2);
    }

    /* ─── Generate ─── */
    window.generateDeliveryOtp = function (groupId) {
        if (!groupId) { alert('Paste a group ID first.'); return; }
        if (!confirm('Generate and send OTP to the customer for group ' + groupId.slice(0, 8) + '…?')) return;

        fetch(API + 'delivery-otp.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ group_id: groupId })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            var msg = d.message;
            if (d.debug_otp) {
                msg += '\n\n[LOCAL DEBUG]\nOTP: ' + d.debug_otp
                     + '\nExpires in: ' + (d.expiresInHours || '?') + ' hours';
            }
            showResult({ ok: true, response: d });
            alert(msg);
        })
        .catch(function (err) {
            showResult({ ok: false, error: err.message });
            alert('Error: ' + err.message);
        });
    };

    /* ─── Verify ─── */
    window.verifyDeliveryOtp = function (groupId) {
        if (!groupId) { alert('Paste a group ID first.'); return; }

        var otp = prompt('Enter the 6-digit OTP (check server response or customer notification):');
        if (!otp) return;
        otp = otp.trim();
        if (!/^[0-9]{6}$/.test(otp)) { alert('OTP must be exactly 6 digits.'); return; }

        fetch(API + 'verify-delivery-otp.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ group_id: groupId, otp: otp })
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) {
                var msg = d.message || 'Verification failed.';
                if (typeof d.attemptsLeft === 'number') msg += ' (' + d.attemptsLeft + ' attempts left)';
                showResult({ ok: false, response: d });
                throw new Error(msg);
            }
            showResult({ ok: true, response: d });
            alert('✓ ' + d.message);
        })
        .catch(function (err) { alert('Error: ' + err.message); });
    };

    /* ─── Status ─── */
    window.checkOtpStatus = function (groupId) {
        if (!groupId) { alert('Paste a group ID first.'); return; }

        fetch(API + 'delivery-otp.php?group_id=' + encodeURIComponent(groupId), {
            credentials: 'include'
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d.success) throw new Error(d.message || 'Failed');
            showResult(d.status);
        })
        .catch(function (err) {
            showResult({ ok: false, error: err.message });
            alert('Error: ' + err.message);
        });
    };
})();