<section class="ac-card mt-3">
    <div class="ac-head">
        <div><h2>Administrator accounts</h2><small>Invitation-only access; no public administrator signup.</small></div>
        <button class="ac-btn primary" type="button" data-open-dialog="admin-invite-dialog">Invite administrator</button>
    </div>
    <div class="ac-table-wrap"><table class="ac-table">
        <thead><tr><th>Administrator</th><th>Role/scope</th><th>MFA</th><th>Last access</th><th>Sessions</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <tr><td><b>Amit Kumar</b><br><small>a•••@consciousindi.in</small></td><td>Super Admin<br><small>Break-glass audited</small></td><td><span class="ac-status success">Enforced</span></td><td>Today, 10:45<br><small>Known device · Bengaluru</small></td><td>2 active</td><td><span class="ac-status success">Active</span></td><td><button class="ac-btn small" type="button" data-open-dialog="admin-account-dialog">Review</button></td></tr>
            <tr><td><b>Priya Singh</b><br><small>p•••@consciousindi.in</small></td><td>Vendor Operations<br><small>KYC + catalog</small></td><td><span class="ac-status success">Enforced</span></td><td>Yesterday, 18:20<br><small>Known device · Jaipur</small></td><td>1 active</td><td><span class="ac-status success">Active</span></td><td><button class="ac-btn small" type="button" data-open-dialog="admin-account-dialog">Review</button></td></tr>
            <tr><td><b>Finance checker invite</b><br><small>f•••@consciousindi.in</small></td><td>Finance Checker</td><td>Required at activation</td><td>Never</td><td>0</td><td><span class="ac-status warning">Invite expires in 2d</span></td><td><button class="ac-btn small" type="button" data-demo-action="Revoke pending administrator invitation">Revoke</button></td></tr>
        </tbody>
    </table></div>
</section>
<section class="ac-grid two mt-3">
    <article class="ac-card"><div class="ac-head"><h2>Access review</h2></div><div class="ac-body"><p>Quarterly review of role necessity, inactive accounts, conflicting finance permissions, temporary elevation and third-party access.</p><div class="ac-actions"><button class="ac-btn" type="button" data-demo-action="Export purpose-limited access review">Export review</button><button class="ac-btn primary" type="button" data-demo-action="Start signed access certification">Start certification</button></div></div></article>
    <article class="ac-card"><div class="ac-head"><h2>Session and device risk</h2></div><div class="ac-body"><p>3 active sessions · 1 new-device challenge · 0 impossible-travel alerts · 2 recovery-code rotations due.</p><button class="ac-btn danger" type="button" data-demo-action="Review and revoke suspicious administrator sessions">Review sessions</button></div></article>
</section>
<dialog class="ac-dialog" id="admin-invite-dialog">
    <div class="ac-dialog-head"><h2>Invite administrator</h2><button class="ac-btn small" type="button" data-close-dialog>Close</button></div>
    <form class="ac-body" data-admin-form="admin-invite" data-success="Invitation fields validated. Backend approval, single-use expiry and audit logging are required.">
        <div class="ac-callout warning">Use a named work account. Finance maker and checker permissions must not be combined for the same action.</div>
        <div class="ac-form-grid mt-3">
            <div class="ac-field"><label for="admin-invite-name">Full name *</label><input class="ac-input" id="admin-invite-name" name="name" autocomplete="name" maxlength="100" required></div>
            <div class="ac-field"><label for="admin-invite-email">Work email *</label><input class="ac-input" id="admin-invite-email" name="email" type="email" autocomplete="off" maxlength="254" required></div>
            <div class="ac-field"><label for="admin-invite-role">Role *</label><select class="ac-select" id="admin-invite-role" name="role" required><option value="">Select least-privilege role</option><option>Vendor Operations</option><option>Catalog Moderator</option><option>Support Agent</option><option>Event Manager</option><option>Content Manager</option><option>Finance Maker</option><option>Finance Checker</option><option>Security Auditor</option><option>Super Admin — additional approval</option></select></div>
            <div class="ac-field"><label for="admin-invite-expiry">Invitation expires *</label><select class="ac-select" id="admin-invite-expiry" name="expiry" required><option>24 hours</option><option>48 hours</option><option>72 hours</option></select></div>
            <div class="ac-field full"><label for="admin-invite-reason">Business reason and scope *</label><textarea class="ac-textarea" id="admin-invite-reason" name="reason" minlength="20" maxlength="500" required></textarea></div>
            <label class="ac-check full"><input type="checkbox" name="mfa_required" checked disabled> MFA enrollment is mandatory before first access.</label>
        </div>
        <div class="ac-form-actions"><button class="ac-btn primary" type="submit">Validate invitation</button></div>
    </form>
</dialog>
<dialog class="ac-dialog" id="admin-account-dialog">
    <div class="ac-dialog-head"><h2>Administrator access review</h2><button class="ac-btn small" type="button" data-close-dialog>Close</button></div>
    <div class="ac-body"><div class="ac-callout">Role changes, session revocation, suspension and recovery reset require step-up authentication, reason and audit logging.</div><div class="ac-actions mt-3"><button class="ac-btn" type="button" data-demo-action="Review administrator role history">Role history</button><button class="ac-btn" type="button" data-demo-action="Revoke all administrator sessions">Revoke sessions</button><button class="ac-btn" type="button" data-demo-action="Require MFA re-enrollment">Reset MFA</button><button class="ac-btn danger" type="button" data-demo-action="Suspend administrator account with reason">Suspend</button></div></div>
</dialog>
