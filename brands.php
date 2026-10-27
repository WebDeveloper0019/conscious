<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover India's leading sustainable brands. All brands verified for ethical practices, sustainability & authentic craftsmanship." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Sustainable Brands Directory | Conscious India" />
  <meta property="og:description" content="Discover India's finest verified sustainable brands — fashion, home, beauty, food & more." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Sustainable Brands — Conscious India | Verified Brand Directory</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Brands hero ===== */
.brands-hero-title{font-size:clamp(1.75rem,4vw,2.75rem);line-height:1.15;}
.brands-hero-desc{max-width:640px;margin-left:auto;margin-right:auto;}

/* ===== Filter tabs — wrap on tablet, horizontal-scroll on mobile ===== */
.bc-brand-tabs{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:32px;}

/* ===== Vendor grid — responsive column counts ===== */
.bc-vendors-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;}

/* ===== Responsive breakpoints ===== */
@media (max-width:1199px){
  .bc-vendors-grid{grid-template-columns:repeat(2,1fr);gap:20px;}
}
@media (max-width:991px){
  .bc-brand-tabs{gap:8px;}
  .bc-vendor-cats{flex-wrap:wrap;}
}
@media (max-width:767px){
  .bc-vendors-grid{grid-template-columns:1fr;gap:18px;}
  .bc-brand-tabs{flex-wrap:nowrap;overflow-x:auto;justify-content:flex-start;-webkit-overflow-scrolling:touch;padding-bottom:8px;margin-left:-4px;margin-right:-4px;padding-left:4px;padding-right:4px;}
  .bc-brand-tabs::-webkit-scrollbar{height:4px;}
  .bc-brand-tabs::-webkit-scrollbar-thumb{background:var(--border-warm);border-radius:4px;}
  .bc-brand-tab{flex:0 0 auto;white-space:nowrap;}
  .bc-vendor-stats{flex-wrap:wrap;row-gap:10px;}
}
@media (max-width:480px){
  .bc-vendor-card-top{flex-direction:column;align-items:flex-start;text-align:left;}
  .bc-vendor-stats{justify-content:space-between;}
}
</style>
</head>
<body>

<!-- PRELOADER -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious India">
  <img src="assets/images/symbol.png" alt="Conscious India" class="bc-preloader-symbol" width="90" height="90" aria-hidden="true" />
  <div class="bc-preloader-brand">Conscious India</div>
  <div class="bc-preloader-chakra"></div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>
<main id="main-content">

<div class="bc-breadcrumb-wrap">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sustainable Brands</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm" style="background:linear-gradient(135deg,var(--navy),var(--navy-light));" aria-labelledby="brands-heading">
  <div class="container">
    <div class="bc-reveal" style="text-align:center;color:white;">
      <div class="bc-section-label" style="color:var(--saffron-light);justify-content:center;">Verified Directory</div>
      <h1 class="bc-section-title brands-hero-title" style="color:white;" id="brands-heading">Sustainable Brands of India</h1>
      <p class="bc-section-desc brands-hero-desc" style="color:rgba(255,255,255,0.75);">Every brand on Conscious India is verified for sustainability, ethical practices and genuine craftsmanship.</p>
    </div>
  </div>
</section>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Browse brands">
  <div class="container">
    <!-- Filter tabs -->
    <div class="bc-brand-tabs" id="bc-brand-tabs">
      <button class="bc-brand-tab active" data-bfilter="all">All Brands</button>
      <button class="bc-brand-tab" data-bfilter="fashion">Fashion &amp; Textile</button>
      <button class="bc-brand-tab" data-bfilter="home">Home &amp; Decor</button>
      <button class="bc-brand-tab" data-bfilter="beauty">Beauty &amp; Wellness</button>
      <button class="bc-brand-tab" data-bfilter="food">Food &amp; Nutrition</button>
      <button class="bc-brand-tab" data-bfilter="eco">Eco &amp; Zero Waste</button>
      <button class="bc-brand-tab" data-bfilter="jewelry">Jewelry</button>
      <button class="bc-brand-tab" data-bfilter="kids">Kids &amp; Baby</button>
      <button class="bc-brand-tab" data-bfilter="wellness">Wellness</button>
    </div>

    <!-- Brand Grid -->
    <div class="bc-vendors-grid">
      <!-- Brand 1 -->
      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal" data-bcat="fashion" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-1">🌿</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">EarthWeave Co.</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Jaipur, Rajasthan</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Verified &amp; ESG Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Sustainable Fashion</span><span class="bc-vendor-cat-tag">Natural Dyes</span><span class="bc-vendor-cat-tag">Organic Cotton</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">Pioneering India's slow fashion revolution with handloom fabrics and natural plant-based dyes since 2018.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">120+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.9★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">2.4K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>

      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal delay-1" data-bcat="eco" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-3">🌱</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">GreenRoot India</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Pune, Maharashtra</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Zero Waste Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Zero Waste</span><span class="bc-vendor-cat-tag">Bamboo Products</span><span class="bc-vendor-cat-tag">Eco Packaging</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">India's most awarded zero-waste lifestyle brand — bamboo, jute and naturally decomposable alternatives.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">85+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.8★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">1.8K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>

      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal delay-2" data-bcat="jewelry" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-4">💫</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">SilverLeaf Studio</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Kolkata, West Bengal</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> GI Certified Craft</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Silver Filigree</span><span class="bc-vendor-cat-tag">Tribal Jewelry</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">Reviving Bengal's 500-year old silver filigree (tarkashi) craft through contemporary jewelry designs.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">200+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.9★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">3.1K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>

      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal" data-bcat="home" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-2">🏮</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Mitti Mahal</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Khurja, Uttar Pradesh</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Verified Artisan Brand</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Pottery</span><span class="bc-vendor-cat-tag">Ceramics</span><span class="bc-vendor-cat-tag">Home Decor</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">Bringing Khurja's renowned ceramic artistry to contemporary homes — each piece individually handcrafted.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">150+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.7★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">2.0K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>

      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal delay-1" data-bcat="beauty" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-5">🌸</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Ayur Ananta</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Mysore, Karnataka</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Ayurvedic Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Ayurvedic Beauty</span><span class="bc-vendor-cat-tag">Natural Skincare</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">Formulating authentic Ayurvedic beauty products using traditional recipes and organically grown herbs.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">65+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.9★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.2K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>

      <a href="/conscious/brand-profile" class="bc-vendor-card bc-reveal delay-2" data-bcat="food" style="text-decoration:none;color:inherit;">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-6">🫙</div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Pure Harvest</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Coimbatore, Tamil Nadu</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> FSSAI &amp; Organic Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats"><span class="bc-vendor-cat-tag">Organic Foods</span><span class="bc-vendor-cat-tag">Cold-Pressed Oils</span></div>
        <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;margin-bottom:16px;">Farm-to-table organic foods, cold-pressed oils and traditional spice blends from certified organic farms.</p>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">95+</div><div class="bc-vendor-stat-key">Products</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.8★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">5.6K+</div><div class="bc-vendor-stat-key">Orders</div></div>
        </div>
      </a>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <button class="bc-btn bc-btn-outline"><i class="fa-solid fa-plus"></i> Load More Brands</button>
    </div>
  </div>
</section>

<section class="bc-newsletter-section section-py" aria-labelledby="newsletter-heading">
  <div class="container">
    <div class="bc-newsletter-inner bc-reveal">
      <div class="bc-section-label">Stay Connected</div>
      <h2 class="bc-section-title" id="newsletter-heading">Join the Conscious Community</h2>
      <p class="bc-section-desc">Get curated stories, upcoming events, exclusive offers &amp; sustainability insights delivered to your inbox.</p>
      <form class="bc-newsletter-form" id="bc-newsletter-form" novalidate>
        <input class="bc-newsletter-input" type="email" placeholder="Enter your email address" aria-label="Email address" required />
        <button class="bc-newsletter-submit" type="submit"><i class="fa-solid fa-paper-plane"></i> Subscribe</button>
      </form>
      <div class="bc-newsletter-perks">
        <span class="bc-newsletter-perk"><i class="fa-solid fa-check-circle"></i> No spam, ever</span>
        <span class="bc-newsletter-perk"><i class="fa-solid fa-check-circle"></i> Unsubscribe anytime</span>
        <span class="bc-newsletter-perk"><i class="fa-solid fa-check-circle"></i> 50,000+ conscious subscribers</span>
      </div>
    </div>
  </div>
</section>
</main>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
</body>
</html>