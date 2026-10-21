<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Meet Rajan Kumhar, master Blue Pottery artisan from Jaipur. 28 years of craft, National Award winner. Shop his handcrafted pottery." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Rajan Kumhar | Master Potter, Jaipur | Conscious Indi" />
  <meta property="og:description" content="National Award winning Blue Pottery master from Jaipur. Shop authentic handcrafted pottery." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Rajan Kumhar — Master Potter | Conscious Indi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Artisan profile header ===== */
.ap-header-grid{display:grid;grid-template-columns:auto 1fr;gap:32px;align-items:center;}
.ap-avatar{width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,var(--saffron),var(--gold));display:flex;align-items:center;justify-content:center;font-size:3rem;border:4px solid rgba(255,255,255,0.3);flex-shrink:0;}
.ap-header-badges{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap;}
.ap-header-title{font-family:var(--font-display);font-size:clamp(1.5rem,4vw,2.5rem);font-weight:800;color:white;margin-bottom:8px;}
.ap-header-meta{display:flex;gap:20px;flex-wrap:wrap;color:rgba(255,255,255,0.7);font-size:0.875rem;}

/* ===== Two-column content layout ===== */
.ap-main-grid{display:grid;grid-template-columns:2fr 1fr;gap:40px;align-items:start;}

/* ===== Card grids ===== */
.ap-craft-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.ap-products-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
.ap-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

/* ===== Responsive breakpoints ===== */
@media (max-width:991px){
  .ap-main-grid{grid-template-columns:1fr;gap:32px;}
  .ap-header-grid{grid-template-columns:1fr;text-align:center;gap:20px;}
  .ap-avatar{margin:0 auto;}
  .ap-header-badges,.ap-header-meta{justify-content:center;}
}
@media (max-width:767px){
  .ap-products-grid{grid-template-columns:repeat(2,1fr);}
}
@media (max-width:575px){
  .ap-craft-grid{grid-template-columns:1fr;}
  .ap-products-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- PRELOADER -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious Indi">
  <img src="assets/images/symbol.png" alt="Conscious Indi" class="bc-preloader-symbol" width="90" height="90" aria-hidden="true" />
  <div class="bc-preloader-brand">Conscious Indi</div>
  <div class="bc-preloader-chakra"></div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>
<main id="main-content">

<div class="bc-breadcrumb-wrap">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item"><a href="/conscious/artisans">Artisans</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rajan Kumhar</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm" style="background:linear-gradient(135deg,#8B4513,#3D1A06);" aria-label="Artisan profile header">
  <div class="container">
    <div class="ap-header-grid bc-reveal">
      <div class="ap-avatar">🧑</div>
      <div>
        <div class="ap-header-badges">
          <span class="bc-badge" style="background:rgba(19,136,8,0.2);color:#5EE85E;border:1px solid rgba(19,136,8,0.3);"><i class="fa-solid fa-circle-check"></i> Verified Artisan</span>
          <span class="bc-badge" style="background:rgba(201,164,74,0.2);color:var(--gold-light);border:1px solid rgba(201,164,74,0.3);">🏆 National Award Winner</span>
        </div>
        <h1 class="ap-header-title">Rajan Kumhar</h1>
        <div class="ap-header-meta">
          <span><i class="fa-solid fa-hands" style="color:var(--saffron-light);"></i> Master Potter</span>
          <span><i class="fa-solid fa-location-dot" style="color:var(--saffron-light);"></i> Jaipur, Rajasthan</span>
          <span><i class="fa-regular fa-calendar" style="color:var(--saffron-light);"></i> 28 years experience</span>
          <span><i class="fa-solid fa-star" style="color:#FFC107;"></i> 4.9 (324 reviews)</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-py" style="background:var(--bg-cream);">
  <div class="container">
    <div class="ap-main-grid">
      <div class="bc-reveal-left">
        <!-- Story -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:32px;margin-bottom:24px;">
          <div class="bc-section-label">Artisan Story</div>
          <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">Four Generations of Blue Pottery</h2>
          <p style="font-size:1rem;color:var(--text-secondary);line-height:1.8;margin-bottom:16px;">Rajan Kumhar was born into pottery. His great-grandfather came to Jaipur from Sindh, carrying with him the secrets of Persian blue pottery — a tradition that had already survived a thousand years. Rajan learned to center clay before he learned to read.</p>
          <p style="font-size:1rem;color:var(--text-secondary);line-height:1.8;margin-bottom:24px;">Blue Pottery of Jaipur is uniquely Indian — it uses no clay from the ground, but a composite of powdered glass, quartz stone and kathha gum. The distinctive blue-and-white motifs are applied by hand with camel-hair brushes, then fired at precise temperatures. Rajan has spent decades perfecting every step of this ancient process.</p>
          <blockquote style="border-left:4px solid var(--saffron);padding:16px 20px;background:var(--saffron-pale);border-radius:0 var(--r-md) var(--r-md) 0;font-style:italic;font-size:1.125rem;color:var(--text-secondary);line-height:1.7;">
            "Every vase I throw carries the spirit of my ancestors — 400 years of Blue Pottery runs through my hands. I am just the latest link in a very long chain."
            <cite style="display:block;margin-top:8px;font-style:normal;font-weight:700;color:var(--saffron-dark);">— Rajan Kumhar</cite>
          </blockquote>
        </div>
        <!-- Craft Details -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;margin-bottom:24px;">
          <h3 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:20px;">About the Craft</h3>
          <div class="ap-craft-grid">
            <div style="padding:14px;background:var(--saffron-pale);border-radius:var(--r-md);border:1px solid rgba(255,153,51,0.15);">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Craft</div>
              <div style="font-weight:700;color:var(--text-primary);">Jaipur Blue Pottery</div>
            </div>
            <div style="padding:14px;background:var(--green-pale);border-radius:var(--r-md);border:1px solid rgba(19,136,8,0.15);">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">GI Status</div>
              <div style="font-weight:700;color:var(--text-primary);">GI Tagged ✓</div>
            </div>
            <div style="padding:14px;background:var(--navy-pale);border-radius:var(--r-md);border:1px solid rgba(0,0,128,0.1);">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Origin State</div>
              <div style="font-weight:700;color:var(--text-primary);">Rajasthan</div>
            </div>
            <div style="padding:14px;background:var(--gold-pale);border-radius:var(--r-md);border:1px solid rgba(201,164,74,0.15);">
              <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Recognition</div>
              <div style="font-weight:700;color:var(--text-primary);">National Craft Award</div>
            </div>
          </div>
        </div>
        <!-- Products -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;">
          <h3 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:20px;">Rajan's Products</h3>
          <div class="ap-products-grid">
            <article class="bc-product-card" data-category="pottery"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🏺</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Blue Pottery Vase</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹1,299</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="pottery"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🫙</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Ceramic Storage Jar</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹799</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="pottery"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🫖</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Hand-painted Tea Set</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹1,599</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
          </div>
          <div style="text-align:center;margin-top:16px;"><a href="/conscious/shop" class="bc-btn bc-btn-outline bc-btn-sm">See All 87 Products</a></div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="bc-reveal-right">
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:var(--r-xl);padding:28px;color:white;margin-bottom:20px;">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;color:white;">Artisan Stats</h3>
          <div class="ap-stats-grid">
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">28</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);">Years Crafting</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">87</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);">Products Listed</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">4.9★</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);">Avg. Rating</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">942</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);">Pieces Sold</div>
            </div>
          </div>
        </div>
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:24px;">
          <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:14px;">Related Stories</h3>
          <a href="/conscious/story-detail" style="display:flex;gap:12px;align-items:center;text-decoration:none;color:inherit;padding:10px 0;border-bottom:1px solid var(--border-light);">
            <div style="width:52px;height:52px;border-radius:var(--r-md);background:linear-gradient(135deg,#C88422,#4A210D);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">🏺</div>
            <div><div style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);">Blue Pottery: A Tradition That Almost Disappeared</div><div style="font-size:0.75rem;color:var(--text-muted);margin-top:3px;">Artisan Story · 4 min read</div></div>
          </a>
          <a href="/conscious/story-detail" style="display:flex;gap:12px;align-items:center;text-decoration:none;color:inherit;padding:10px 0;">
            <div style="width:52px;height:52px;border-radius:var(--r-md);background:linear-gradient(135deg,var(--saffron),var(--gold));display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">🌟</div>
            <div><div style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);">How Rajan Won the National Craft Award</div><div style="font-size:0.75rem;color:var(--text-muted);margin-top:3px;">Feature Story · 6 min read</div></div>
          </a>
        </div>
      </div>
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