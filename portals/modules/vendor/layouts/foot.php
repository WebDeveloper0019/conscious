  </main>
</div>
</div>
<dialog class="vc-dialog" id="vc-demo-dialog" aria-labelledby="vc-demo-title">
  <div class="vc-dialog-head"><h2 id="vc-demo-title">Vendor preview action</h2><button type="button" class="vc-btn small" data-close-demo-dialog>Close</button></div>
  <div class="vc-dialog-body">
    <div class="vc-callout" id="vc-demo-summary"></div>
    <div class="vc-form-grid mt-3" id="vc-demo-fields"></div>
    <div class="vc-form-actions"><button type="button" class="vc-btn" data-close-demo-dialog>Cancel</button><button type="button" class="vc-btn primary" id="vc-demo-confirm">Complete demo action</button></div>
  </div>
</dialog>
<div class="vc-toast-wrap" id="vc-toast-wrap" aria-live="polite" aria-atomic="true"></div>
<script src="assets/js/app.js?v=20261024" defer></script>
<?php if (!empty($vendorPageScript)): ?><script src="<?= htmlspecialchars($vendorPageScript, ENT_QUOTES, 'UTF-8') ?>" defer></script><?php endif; ?>
<?php if (!empty($vendorOnboardingMode)): ?><script src="assets/js/vendor-onboarding-live.js?v=20261026-2" defer></script><?php endif; ?>
</body>
</html>
