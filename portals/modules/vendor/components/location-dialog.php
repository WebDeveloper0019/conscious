<dialog class="vc-dialog" id="location-dialog">
  <div class="vc-dialog-head"><h2>Add dispatch or return location</h2><button class="vc-btn small" type="button" data-close-dialog>Close</button></div>
  <form class="vc-dialog-body" data-frontend-form="vendor-location" data-success="Location validated and ready for backend connection.">
    <div class="vc-form-grid">
      <div class="vc-field"><label for="location-name">Location name</label><input class="vc-input" id="location-name" name="location_name" required minlength="2" maxlength="80" placeholder="Jaipur workshop"></div>
      <div class="vc-field"><label for="location-purpose">Purpose</label><select class="vc-select" id="location-purpose" name="purpose" required><option value="">Select purpose</option><option>Dispatch only</option><option>Returns only</option><option>Dispatch and returns</option><option>Event stock</option></select></div>
      <div class="vc-field full"><label for="location-address">Address</label><textarea class="vc-textarea" id="location-address" name="address" required minlength="10" maxlength="300" autocomplete="street-address"></textarea></div>
      <div class="vc-field"><label for="location-city">City/town/village</label><input class="vc-input" id="location-city" name="city" required minlength="2" maxlength="80" autocomplete="address-level2"></div>
      <div class="vc-field"><label for="location-district">District</label><input class="vc-input" id="location-district" name="district" required minlength="2" maxlength="80"></div>
      <div class="vc-field"><label for="location-state">State/UT</label><select class="vc-select" id="location-state" name="state" required><option value="">Select state/UT</option><?php require __DIR__.'/india-states-options.php'; ?></select></div>
      <div class="vc-field"><label for="location-pin">PIN code</label><input class="vc-input" id="location-pin" name="pincode" data-pincode inputmode="numeric" required maxlength="6" autocomplete="postal-code"></div>
      <div class="vc-field"><label for="location-contact">Contact phone</label><input class="vc-input" id="location-contact" name="contact_phone" data-indian-phone inputmode="numeric" required maxlength="10" autocomplete="tel-national"></div>
      <div class="vc-field"><label for="location-capacity">Daily capacity</label><input class="vc-input" id="location-capacity" name="daily_capacity" type="number" min="1" max="10000" required></div>
      <div class="vc-field full"><label class="vc-check"><input name="default_dispatch" type="checkbox"> Set as default dispatch location</label><label class="vc-check mt-2"><input name="default_return" type="checkbox"> Set as default return location</label></div>
    </div>
    <div class="vc-form-actions"><button class="vc-btn" type="button" data-close-dialog>Cancel</button><button class="vc-btn primary" type="submit">Validate location</button></div>
  </form>
</dialog>
