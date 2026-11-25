(() => {
  'use strict';
  const byId = id => document.getElementById(id);
  const csrf = () => sessionStorage.getItem('ci_admin_csrf_token') || decodeURIComponent((document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/) || [])[1] || '');
  async function request(method = 'GET', body) {
    const response = await fetch('/conscious/api/admin/security-settings.php', {
      method,
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { Accept: 'application/json', ...(method === 'GET' ? {} : { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() }) },
      body: body === undefined ? undefined : JSON.stringify(body)
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) throw new Error(data.message || 'Security settings could not be updated.');
    return data;
  }
  function render(data) {
    const enabled = Boolean(data.bypass_enabled);
    const available = Boolean(data.bypass_available);
    byId('ass-environment').textContent = data.environment || 'unknown';
    byId('ass-pending').textContent = Number(data.counts?.pending || 0).toLocaleString('en-IN');
    byId('ass-bypassed').textContent = Number(data.counts?.bypassed || 0).toLocaleString('en-IN');
    byId('ass-bypass').checked = enabled;
    byId('ass-bypass').disabled = !available;
    byId('ass-save').disabled = !available;
    byId('ass-switch-label').textContent = enabled ? 'Bypassed' : 'Scanning required';
    byId('ass-status').textContent = enabled ? 'Bypass active' : 'ClamAV required';
    byId('ass-status').className = 'av-badge ' + (enabled ? 'rejected' : 'verified');
    byId('ass-warning').hidden = !available;
    if (!available) byId('ass-message').textContent = 'This safety control is locked because the application is not running in the local environment.';
  }
  async function load() {
    try { const data = await request(); render(data); byId('ass-message').textContent = data.bypass_enabled ? 'Local bypass is active. Documents are unscanned.' : 'ClamAV scanning is required.'; }
    catch (error) { byId('ass-message').textContent = error.message; byId('ass-message').classList.add('is-error'); }
  }
  byId('ass-save').addEventListener('click', async () => {
    const button = byId('ass-save'); button.disabled = true;
    try { const data = await request('POST', { malware_scan_bypass: byId('ass-bypass').checked }); render(data); byId('ass-message').textContent = data.message; byId('ass-message').classList.remove('is-error'); }
    catch (error) { byId('ass-message').textContent = error.message; byId('ass-message').classList.add('is-error'); }
    finally { if (!byId('ass-bypass').disabled) button.disabled = false; }
  });
  load();
})();
