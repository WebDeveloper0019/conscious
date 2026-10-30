<section class="cp-impact-hero">
  <div><span class="cx-kicker">Your verified contribution</span><h2>Every purchase supports a living craft tradition.</h2><p>See the measurable social and environmental value connected to your completed Conscious India orders. Figures are estimates based on vendor declarations and product-level impact data.</p><div class="cp-impact-actions"><button class="cc-btn primary" type="button" data-demo-action="Download impact report"><i class="fa-solid fa-file-arrow-down"></i> Download report</button><select class="cc-select" aria-label="Impact period"><option>All time</option><option>2026</option><option>Last 12 months</option></select></div></div>
  <div class="cp-impact-score"><span><i class="fa-solid fa-leaf"></i></span><small>Conscious score</small><strong>82</strong><p>Excellent progress</p></div>
</section>

<section class="cp-metric-grid">
  <article><span class="green"><i class="fa-solid fa-people-group"></i></span><div><strong data-impact="artisans">0</strong><p>artisan livelihoods supported</p><small>Across 6 verified maker groups</small></div></article>
  <article><span class="blue"><i class="fa-solid fa-droplet"></i></span><div><strong><span data-impact="waterSaved">0</span> L</strong><p>estimated water saved</p><small>Compared with mass-made alternatives</small></div></article>
  <article><span class="gold"><i class="fa-solid fa-recycle"></i></span><div><strong><span data-impact="wasteDiverted">0</span> kg</strong><p>lower material waste</p><small>Recycled and low-waste processes</small></div></article>
  <article><span class="orange"><i class="fa-solid fa-location-dot"></i></span><div><strong data-impact="regions">0</strong><p>Indian craft regions reached</p><small>From Kutch to Madhubani</small></div></article>
</section>

<div class="cp-two-column">
  <section class="cp-panel">
    <header class="cp-panel-head">
      <div><span class="cx-kicker">Impact by craft</span><h2>Where your support travelled</h2></div>
      <a href="shop.php">Explore crafts</a>
    </header>
    <!-- Empty container where JS will inject the dynamic bars -->
    <div class="cp-impact-bars" id="impact-bars-container">
      <p style="color:var(--text-muted); font-size:14px; padding: 10px 0;">Loading impact data...</p>
    </div>
  </section>
  
  <aside class="cp-panel cp-impact-region">
    <span class="cp-round-icon"><i class="fa-solid fa-map-location-dot"></i></span>
    <h2>7 regions, one story</h2>
    <p>Your orders have supported makers in Rajasthan, Gujarat, Bihar, West Bengal, Odisha, Karnataka and Kashmir.</p>
    <a class="cc-btn" href="vendors.php">Meet your makers</a>
  </aside>
</div>

<section class="cp-panel">
  <header class="cp-panel-head">
    <div><span class="cx-kicker">Order-level detail</span><h2>How each order made a difference</h2></div>
    <button class="cc-btn" type="button" data-demo-action="Impact methodology"><i class="fa-solid fa-circle-info"></i> Methodology</button>
  </header>
  <div class="cp-impact-orders">
    <article><img src="assets/images/products/blue-pottery-vase.jpg" alt="Blue Pottery Floral Vase"><div><small>ORDER CI-83401</small><h3>Blue Pottery Floral Vase</h3><p>Jaipur Pottery House · Rajasthan</p></div><ul><li><i class="fa-solid fa-user-group"></i><span><b>3 makers</b> supported</span></li><li><i class="fa-solid fa-sun"></i><span><b>Low-energy</b> workshop</span></li><li><i class="fa-solid fa-box"></i><span><b>Plastic-light</b> packing</span></li></ul></article>
    <article><img src="assets/images/products/kantha-cushion.jpg" alt="Kantha embroidered cushion"><div><small>ORDER CI-82116</small><h3>Kantha Embroidered Cushion</h3><p>Kantha Collective · West Bengal</p></div><ul><li><i class="fa-solid fa-person-dress"></i><span><b>Women-led</b> collective</span></li><li><i class="fa-solid fa-recycle"></i><span><b>Reclaimed</b> textile layers</span></li><li><i class="fa-solid fa-hand-holding-heart"></i><span><b>Fair-pay</b> declared</span></li></ul></article>
  </div>
  <p class="cp-method-note"><i class="fa-solid fa-shield-halved"></i> Impact values are not carbon credits. They are transparent estimates built from vendor-supplied materials, production, packaging and livelihood information.</p>
</section>

<!-- JAVASCRIPT TO FETCH DYNAMIC DATA -->
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Fetch data from the API you created
        const response = await fetch('/conscious/api/user/impact.php'); 
        const data = await response.json();

        if (data.success && data.impact) {
            // 1. Update Top Stats Numbers
            document.querySelector('[data-impact="artisans"]').textContent = data.impact.artisans;
            document.querySelector('[data-impact="waterSaved"]').textContent = data.impact.waterSaved;
            document.querySelector('[data-impact="wasteDiverted"]').textContent = data.impact.wasteDiverted;
            document.querySelector('[data-impact="regions"]').textContent = data.impact.regions;

            // 2. Render Impact by Craft Progress Bars
            const barsContainer = document.getElementById('impact-bars-container');
            if (barsContainer && data.impact.crafts) {
                barsContainer.innerHTML = ''; // Clear the "Loading..." text
                
                // Loop through each category returned by the database
                data.impact.crafts.forEach(craft => {
                    const barHTML = `
                        <div>
                            <p><span>${craft.name}</span><b>${craft.percentage}%</b></p>
                            <i><em style="width:${craft.percentage}%"></em></i>
                        </div>
                    `;
                    barsContainer.insertAdjacentHTML('beforeend', barHTML);
                });
            }
        }
    } catch (error) {
        console.error("Failed to load impact data:", error);
    }
});
</script>