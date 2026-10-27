<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="View and manage your saved handcrafted products on Conscious India." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="My Wishlist | Conscious India" />
  <meta property="og:description" content="Your saved handcrafted products from Indian artisans and sustainable brands." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>My Wishlist — Conscious India | Saved Items</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}
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

<div class="bc-breadcrumb-wrap" style="background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item"><a href="/conscious/account">Account</a></li>
            <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Your wishlist">
  <div class="container">
    <div class="bc-section-header">
      <div class="bc-section-label">Saved Items</div>
      <h1 class="bc-section-title">My Wishlist <span style="font-size:1rem;color:var(--text-muted);font-weight:400;">(5 items)</span></h1>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
      <span style="font-size:0.875rem;color:var(--text-muted);">5 items saved</span>
      <button class="bc-btn bc-btn-primary bc-btn-sm"><i class="fa-solid fa-bag-shopping"></i> Move All to Cart</button>
    </div>

<div id="bc-wishlist-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">

      <article class="bc-product-card bc-reveal" data-category="pottery">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">🏺</div>
          <div class="bc-product-actions" style="opacity:1;transform:none;">
            <button class="bc-product-action-btn wishlist wishlist-active" aria-label="Remove from wishlist"><i class="fa-solid fa-heart"></i></button>
            <button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button>
          </div>
        </div>
        <div class="bc-product-body">
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Blue Pottery Vase</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.9</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,299</span><span class="bc-product-price-original">₹1,799</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-1" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">💍</div>
          <div class="bc-product-actions" style="opacity:1;transform:none;">
            <button class="bc-product-action-btn wishlist wishlist-active" aria-label="Remove from wishlist"><i class="fa-solid fa-heart"></i></button>
          </div>
        </div>
        <div class="bc-product-body">
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Kundan Meenakari Necklace</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹3,499</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-2" data-category="handloom">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">🧵</div>
          <div class="bc-product-actions" style="opacity:1;transform:none;">
            <button class="bc-product-action-btn wishlist wishlist-active" aria-label="Remove from wishlist"><i class="fa-solid fa-heart"></i></button>
          </div>
        </div>
        <div class="bc-product-body">
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Banarasi Silk Dupatta</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹2,199</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-3" data-category="paintings">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-4" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">🎨</div>
          <div class="bc-product-actions" style="opacity:1;transform:none;">
            <button class="bc-product-action-btn wishlist wishlist-active" aria-label="Remove from wishlist"><i class="fa-solid fa-heart"></i></button>
          </div>
        </div>
        <div class="bc-product-body">
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Madhubani Painting — Folk Art</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">5.0</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹4,500</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
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