<section class="ac-card ac-security-settings" aria-labelledby="security-settings-title">
  <div class="ac-head">
    <div>
      <span class="ac-kicker">LOCAL DEVELOPMENT</span>
      <h2 id="security-settings-title">Upload malware scanning</h2>
      <p>Control how all uploaded files are handled while ClamAV is unavailable on this computer.</p>
    </div>
    <span class="av-badge pending" id="ass-status">Loading</span>
  </div>
  <div class="ac-body">
    <div class="ac-callout warning" id="ass-warning">
      <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
      <p><b>Local testing only.</b> When bypassed, uploaded files are not malware scanned. They are marked <em>Security scan bypassed</em>, never falsely marked clean. This control cannot be enabled in production.</p>
    </div>

    <div class="ass-control-row">
      <div>
        <h3>Disable ClamAV for all project uploads</h3>
        <p>Allows local document and image workflows to continue without ClamAV. Production remains protected.</p>
      </div>
      <label class="ass-switch">
        <input type="checkbox" id="ass-bypass" disabled>
        <span aria-hidden="true"></span>
        <b id="ass-switch-label">Off</b>
      </label>
    </div>

    <dl class="ass-facts">
      <div><dt>Environment</dt><dd id="ass-environment">—</dd></div>
      <div><dt>Waiting for scan</dt><dd id="ass-pending">—</dd></div>
      <div><dt>Bypassed locally</dt><dd id="ass-bypassed">—</dd></div>
    </dl>

    <div class="ac-form-actions">
      <p id="ass-message" role="status" aria-live="polite">Loading security settings.</p>
      <button class="ac-btn primary" id="ass-save" type="button" disabled>
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Save security setting
      </button>
    </div>
  </div>
</section>
