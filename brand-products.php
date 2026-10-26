<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Brand Products | Conscious India</title><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" /><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"><link rel="stylesheet" href="assets/css/bharatcraft.css"><style>
  .bp{background:var(--bg-cream)}
  .bp-hero{position:relative;overflow:hidden;padding:var(--section-py-lg) 0;background:linear-gradient(135deg,var(--green) 0%,var(--green-dark) 65%,#0A2E08 100%);color:#fff}
  .bp-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 15% 30%,rgba(255,153,51,.16) 0%,transparent 55%),radial-gradient(circle at 85% 75%,rgba(201,164,74,.14) 0%,transparent 55%);pointer-events:none}
  .bp-hero .container{position:relative;z-index:1}
  .bp-hero a[href="/conscious/gi-tag"]{color:rgba(255,255,255,.85);font-size:.8125rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
  .bp-hero a[href="/conscious/gi-tag"]:hover{color:#fff;text-decoration:underline}
  .bp-hero-grid{display:grid;grid-template-columns:1fr 220px;gap:2rem;align-items:end}
  .bp-mark{height:170px;width:170px;border-radius:var(--r-lg);border:1px solid rgba(255,255,255,.3);display:grid;place-items:center;font:700 4rem var(--font-display);color:#fff;background:rgba(255,255,255,.08)}
  .bp h1,.bp h2{font-family:var(--font-display)}
  .bp h1{font-size:clamp(2.25rem,4.5vw,3.75rem);font-weight:800;margin:.6rem 0}
  .bp-hero p{color:rgba(255,255,255,.78);max-width:520px;line-height:1.7;margin-top:.5rem}
  .bp-cert{display:inline-flex;align-items:center;gap:8px;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--saffron-light)}
  .bp-section{padding:var(--section-py) 0}
  .bp-section > .container > .bp-cert{color:var(--saffron-dark)}
  .bp-products{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
  .bp-product{background:#fff;border:1px solid var(--border-warm);border-radius:var(--r-lg);padding:1.25rem;box-shadow:var(--shadow-sm);transition:transform var(--t),box-shadow var(--t)}
  .bp-product:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
  .bp-product img{width:100%;height:190px;object-fit:contain;background:var(--bg-cream);padding:1rem;border-radius:var(--r-md)}
  .bp-product h2{font-size:1.2rem;margin:.9rem 0 .3rem;color:var(--text-primary)}
  .bp-product strong{color:var(--saffron-dark);font-size:1.05rem}
  .bp-product p{margin-top:.6rem}
  .bp-product a{display:inline-flex;align-items:center;gap:4px;color:var(--green);font-weight:700;font-size:.8125rem}
  .bp-product a:hover{color:var(--saffron-dark)}
  @media(max-width:700px){.bp-hero{padding:3rem 0}.bp-hero-grid,.bp-products{grid-template-columns:1fr}.bp-mark{height:100px;width:100px;font-size:2.5rem}.bp-section{padding:3rem 0}}
</style></head><body class="bp">

<div id="bc-preloader" role="status" aria-label="Loading Conscious India">
  <img
    src="assets/images/symbol.png"
    alt="Conscious India"
    class="bc-preloader-symbol"
    width="90"
    height="90"
    aria-hidden="true"
  />
  <div class="bc-preloader-brand">Conscious India</div>
  <div class="bc-preloader-chakra"></div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>


<main id="main-content"><section class="bp-hero"><div class="container bp-hero-grid"><div><a href="/conscious/gi-tag" style="color:#fff">← GI Tag &amp; Certifications</a><div class="bp-cert" id="cert"></div><h1 id="name">Brand products</h1><p id="about"></p></div><div class="bp-mark" id="mark" aria-hidden="true">C</div></div></section><section class="bp-section"><div class="container"><div class="bp-cert" style="color:var(--terracotta)">Curated collection</div><h2>Products from this brand</h2><div class="bp-products" id="products"></div></div></section></main>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<!-- Back to Top Button -->
<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>

<script>const data={kantha:['Kantha Collective','GI Tag certified','Hand-stitched Bengal textiles',[['Kantha Cushion','assets/images/products/kantha-cushion.svg','₹1,850'],['Kantha Throw','assets/images/products/pashmina-shawl.svg','₹3,200']]],'blue-pottery':['Blue Pottery House','GI Tag certified','Jaipur blue pottery in its place of origin',[['Blue Pottery Vase','assets/images/products/blue-pottery-vase.svg','₹2,200']]],muga:['Muga Loom Studio','GOTS certified','Golden Assam Muga silk',[['Muga Silk Shawl','assets/images/products/pashmina-shawl.svg','₹5,500']]],'forest-form':['Forest Form','Vegan certified','Botanical body care',[['Botanical Cleansing Bar','assets/images/categories/eco.svg','₹295']]],'valley-saffron':['Valley Saffron Co.','India Organic certified','Pampore saffron grown with care',[['Saffron Gift Box','assets/images/products/sandalwood-box.svg','₹1,490']]],'earthy-home':['Earthy Home','Zero Waste certified','Low-waste everyday essentials',[['Terracotta Tea Set','assets/images/products/blue-pottery-vase.svg','₹1,490']]]};let d=data[new URLSearchParams(location.search).get('brand')]||data.kantha;name.textContent=d[0];cert.textContent=d[1];about.textContent=d[2];mark.textContent=d[0][0];products.innerHTML=d[3].map(p=>`<article class="bp-product"><img src="${p[1]}" alt="${p[0]}"><h2>${p[0]}</h2><strong>${p[2]}</strong><p><a href="/conscious/product-detail">View Product</a> · <a href="portals/modules/customer/cart.php">Shop Now</a></p></article>`).join('')</script></body></html>
