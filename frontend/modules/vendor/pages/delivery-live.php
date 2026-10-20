<div class="vc-callout" role="note">You arrange every delivery yourself. Conscious Indi does not book a courier or use Shiprocket. The order and tracking updates below are saved to the backend and shown to the customer.</div>
<div id="dl-message" class="vc-callout mt-3" role="status" hidden></div>

<div data-tabs class="mt-3">
  <div class="vc-tabs" role="tablist">
    <button type="button" class="vc-tab active" data-tab-target="queue" aria-selected="true">Order delivery</button>
    <button type="button" class="vc-tab" data-tab-target="coverage" aria-selected="false">India coverage</button>
    <button type="button" class="vc-tab" data-tab-target="rates" aria-selected="false">Location rates</button>
    <button type="button" class="vc-tab" data-tab-target="check" aria-selected="false">Check serviceability</button>
  </div>

  <section data-tab-panel="queue" class="vc-grid" aria-label="Vendor-managed order delivery">
    <article class="vc-card"><div class="vc-card-head"><h2>Orders requiring delivery</h2><button class="vc-btn small" type="button" id="dl-refresh-orders">Refresh</button></div><div class="vc-card-body"><p class="vc-help">Only real orders for your vendor account appear here. Prepare an order in Orders before making a package.</p><div id="dl-order-list">Loading orders…</div></div></article>
    <article class="vc-card" id="dl-order-detail" hidden><div class="vc-card-head"><h2 id="dl-order-title">Selected order</h2><span id="dl-order-status" class="vc-status"></span></div><div class="vc-card-body"><div id="dl-order-address"></div><div id="dl-order-items" class="mt-3"></div><div id="dl-order-actions" class="vc-form-actions"></div></div></article>
    <article class="vc-card" id="dl-package-card" hidden><div class="vc-card-head"><h2>Create a manual delivery package</h2></div><div class="vc-card-body"><p class="vc-help">Use this after the order reaches “Ready to dispatch”. Split quantities if you are sending more than one package. AWB is optional; an internal reference is generated when you leave it blank.</p><form id="dl-package-form" class="vc-form-grid">
      <div class="vc-field"><label for="dl-courier">Delivery service or person</label><input class="vc-input" id="dl-courier" name="courier_name" required minlength="2" maxlength="120" placeholder="Self delivery, employee or courier name"></div>
      <div class="vc-field"><label for="dl-reference">Courier reference, if available</label><input class="vc-input" id="dl-reference" name="tracking_reference" maxlength="120" placeholder="Optional — not necessarily an AWB"></div>
      <div class="vc-field"><label for="dl-tracking-url">Courier tracking URL, if available</label><input class="vc-input" id="dl-tracking-url" name="tracking_url" type="url" maxlength="500"></div>
      <div class="vc-field"><label for="dl-expected">Expected delivery date</label><input class="vc-input" id="dl-expected" name="expected_delivery_date" type="date" required></div>
      <div class="vc-field"><label for="dl-cost">Your delivery cost (₹)</label><input class="vc-input" id="dl-cost" name="vendor_shipping_cost" type="number" min="0" max="100000" step="0.01"></div>
      <div class="vc-field full"><label>Items and quantities for this package</label><div id="dl-package-items"></div></div>
      <div class="vc-field full"><button type="submit" class="vc-btn primary">Create package</button></div>
    </form></div></article>
    <article class="vc-card" id="dl-packages-card" hidden><div class="vc-card-head"><h2>Packages and manual milestones</h2></div><div class="vc-card-body"><div id="dl-packages"></div></div></article>
  </section>

  <section data-tab-panel="coverage" hidden><article class="vc-card"><div class="vc-card-head"><h2>India delivery settings</h2></div><div class="vc-card-body"><p class="vc-help">Select All India to serve every Indian state and PIN except locations you exclude below. Checkout uses these saved rules to calculate delivery price.</p><form id="dl-settings-form" class="vc-form-grid">
    <div class="vc-field"><label for="dl-coverage-model">Coverage</label><select class="vc-select" id="dl-coverage-model" name="coverage_model" required><option value="all_india">All India with exclusions</option><option value="selected_locations">Selected locations only</option></select></div>
    <div class="vc-field"><label for="dl-base-charge">Default delivery charge (₹)</label><input class="vc-input" id="dl-base-charge" name="base_charge" type="number" min="0" max="100000" step="0.01" required></div>
    <div class="vc-field"><label for="dl-free-above">Free delivery above order value (₹)</label><input class="vc-input" id="dl-free-above" name="free_above" type="number" min="0" max="10000000" step="0.01" placeholder="Leave blank if never free"></div>
    <div class="vc-field"><label for="dl-processing-min">Processing time: minimum days</label><input class="vc-input" id="dl-processing-min" name="processing_min_days" type="number" min="0" max="90" required></div>
    <div class="vc-field"><label for="dl-processing-max">Processing time: maximum days</label><input class="vc-input" id="dl-processing-max" name="processing_max_days" type="number" min="0" max="90" required></div>
    <div class="vc-field full"><label class="vc-check"><input type="checkbox" name="cod_enabled"> Offer cash on delivery where you can collect it manually</label></div>
    <div class="vc-field full"><label class="vc-check"><input type="checkbox" name="fragile_handling"> Fragile-item handling available</label></div>
    <div class="vc-field full"><button type="submit" class="vc-btn primary">Save delivery settings</button></div>
  </form></div></article></section>

  <section data-tab-panel="rates" hidden><div class="vc-grid"><article class="vc-card"><div class="vc-card-head"><h2>State, city and PIN rules</h2></div><div class="vc-card-body"><p class="vc-help">An exclusion overrides All India serviceability. For a priced zone, its charge overrides the default delivery charge. You can also create an All India rate.</p><div id="dl-zone-list">Loading rules…</div></div></article><article class="vc-card"><div class="vc-card-head"><h2>Add a location rule</h2></div><div class="vc-card-body"><form id="dl-zone-form" class="vc-form-grid">
    <div class="vc-field"><label for="dl-zone-name">Rule name</label><input class="vc-input" id="dl-zone-name" name="zone_name" required minlength="3" maxlength="100"></div>
    <div class="vc-field"><label for="dl-zone-type">Match location by</label><select class="vc-select" id="dl-zone-type" name="rule_type"><option value="state">State or UT</option><option value="city">City and state</option><option value="pincode">PIN range</option><option value="all_india">All India</option></select></div>
    <div class="vc-field"><label for="dl-zone-state">State or UT</label><select class="vc-select" id="dl-zone-state" name="state"><option value="">Choose state or UT</option><?php require __DIR__.'/../components/india-states-options.php'; ?></select></div>
    <div class="vc-field"><label for="dl-zone-city">City</label><input class="vc-input" id="dl-zone-city" name="city" maxlength="100"></div>
    <div class="vc-field"><label for="dl-zone-from">PIN from</label><input class="vc-input" id="dl-zone-from" name="pincode_from" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6"></div>
    <div class="vc-field"><label for="dl-zone-to">PIN to</label><input class="vc-input" id="dl-zone-to" name="pincode_to" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6"></div>
    <div class="vc-field full"><label class="vc-check"><input type="checkbox" name="is_exclusion" id="dl-zone-exclusion"> Exclude this location from delivery</label></div>
    <div class="vc-field"><label for="dl-zone-charge">Delivery charge (₹) for included locations</label><input class="vc-input" id="dl-zone-charge" name="charge" type="number" min="0" max="100000" step="0.01"></div>
    <div class="vc-field full"><button class="vc-btn primary" type="submit">Add rule</button></div>
  </form></div></article></div></section>

  <section data-tab-panel="check" hidden><article class="vc-card"><div class="vc-card-head"><h2>Test a customer destination</h2></div><div class="vc-card-body"><p class="vc-help">Uses the same serviceability and charge calculation as checkout.</p><form id="dl-quote-form" class="vc-form-grid">
    <div class="vc-field"><label for="dl-check-state">State or UT</label><select class="vc-select" id="dl-check-state" name="state" required><option value="">Choose state or UT</option><?php require __DIR__.'/../components/india-states-options.php'; ?></select></div>
    <div class="vc-field"><label for="dl-check-city">City</label><input class="vc-input" id="dl-check-city" name="city" required minlength="2" maxlength="100"></div>
    <div class="vc-field"><label for="dl-check-pin">PIN code</label><input class="vc-input" id="dl-check-pin" name="pincode" inputmode="numeric" pattern="[1-9][0-9]{5}" minlength="6" maxlength="6" required></div>
    <div class="vc-field"><label for="dl-check-value">Order value (₹)</label><input class="vc-input" id="dl-check-value" name="order_value" type="number" min="0" max="10000000" step="0.01" value="500" required></div>
    <div class="vc-field"><label for="dl-check-weight">Package weight (grams)</label><input class="vc-input" id="dl-check-weight" name="weight_grams" type="number" min="1" max="500000" value="500" required></div>
    <div class="vc-field full"><button class="vc-btn primary" type="submit">Check destination and price</button></div>
  </form><div id="dl-quote-result" class="vc-callout mt-3" role="status" hidden></div></div></article></section>
</div>
