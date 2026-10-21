<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Shop handcrafted products from verified Indian artisans and sustainable brands. Filter by category, price, and rating." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Shop — Conscious Indi" />
  <meta property="og:description" content="Handcrafted treasures from verified artisans & sustainable brands across India." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Shop — Conscious Indi | Handcrafted &amp; Sustainable Products</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ============ SHOP PAGE RESPONSIVE ============ */
.bc-shop-layout{ display:grid; grid-template-columns:260px 1fr; gap:32px; align-items:start; }
.bc-shop-sidebar{ position:sticky; top:80px; }

.bc-shop-sortbar{
  display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
  margin-bottom:24px; padding:14px 20px; background:white; border-radius:var(--r-md); border:1.5px solid var(--border-warm);
}
.bc-shop-sort-controls{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }

.bc-shop-product-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
.bc-live-filter-group{margin-bottom:22px}.bc-live-filter-group h3{font-size:.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-primary);margin:0 0 12px}.bc-live-options{display:flex;flex-direction:column;gap:9px}.bc-live-options label{display:flex;align-items:center;gap:9px;font-size:.875rem;color:var(--text-secondary);cursor:pointer}.bc-live-options input{accent-color:var(--saffron)}.bc-product-card.bc-live-card{display:flex;flex-direction:column;overflow:hidden;border:1px solid var(--border-warm);border-radius:18px;background:white;box-shadow:0 8px 22px rgba(30,43,34,.05)}.bc-live-card .bc-product-img-wrap{aspect-ratio:4/4.2;overflow:hidden;background:#ece8e1}.bc-live-card .bc-product-img-wrap>a,.bc-live-card .bc-product-img-wrap img{display:block;width:100%;height:100%}.bc-live-card .bc-product-img-wrap img{object-fit:cover;transition:transform .35s}.bc-live-card:hover .bc-product-img-wrap img{transform:scale(1.035)}.bc-live-card .bc-product-body{display:flex;flex:1;flex-direction:column;gap:7px;padding:15px}.bc-live-card .bc-product-artisan{margin:0;color:var(--text-muted);font-size:.78rem}.bc-live-card .bc-product-title{margin:0;font-size:1rem;line-height:1.35;min-height:2.65em}.bc-live-card .bc-product-title a{color:var(--text-primary);text-decoration:none}.bc-live-card .bc-product-price-row{display:flex;align-items:baseline;gap:8px;margin-top:auto}.bc-live-card .bc-product-price{font-size:1.13rem;font-weight:800}.bc-live-card .bc-product-price-original{font-size:.82rem}.bc-live-card .bc-live-add{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:7px;padding:11px 12px;border:0;border-radius:10px;background:var(--saffron);color:white;font-size:.86rem;font-weight:800;cursor:pointer}.bc-live-card .bc-live-add:hover{background:var(--saffron-dark)}.bc-live-card .bc-live-add:disabled{opacity:.65;cursor:wait}.bc-live-card .bc-live-save{position:absolute;right:11px;top:11px;z-index:2;display:grid;place-items:center;width:36px;height:36px;border:1px solid var(--border-warm);border-radius:50%;background:white;color:var(--text-primary);cursor:pointer}.bc-live-card .bc-live-category{font-size:.72rem;font-weight:700;color:var(--saffron-dark);text-transform:uppercase;letter-spacing:.045em}.bc-shop-toast{position:fixed;right:20px;bottom:22px;z-index:9999;max-width:min(380px,calc(100vw - 40px));padding:13px 16px;border:1px solid var(--border-warm);border-radius:12px;background:white;box-shadow:0 12px 30px rgba(20,37,24,.18);color:var(--text-primary)}.bc-shop-toast a{color:var(--saffron-dark);font-weight:700}
.bc-shop-product-grid:not([data-live-ready])>.bc-product-card{display:none!important}
.bc-shop-product-grid:not([data-live-ready])::before{content:"Loading published products…";display:block;color:var(--text-muted);padding:24px}
.bc-shop-pagination{display:none!important}

.bc-shop-pagination{ display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; margin-top:40px; }

@media (max-width:1024px){
  .bc-shop-layout{ grid-template-columns:1fr; gap:24px; }
  .bc-shop-sidebar{ position:static; }
  .bc-shop-product-grid{ grid-template-columns:repeat(2,1fr); }
}

@media (max-width:640px){
  .bc-shop-sortbar{ padding:12px 16px; }
  .bc-shop-sort-controls{ width:100%; justify-content:space-between; }
}

@media (max-width:480px){
  .bc-shop-product-grid{ grid-template-columns:1fr; gap:14px; }
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

<div class="bc-breadcrumb-wrap" style="background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shop</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Shop Hero -->
<section class="section-py-sm" style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%);" aria-labelledby="shop-heading">
  <div class="container">
    <div class="bc-reveal" style="text-align:center;color:white;">
      <div class="bc-section-label" style="color:var(--saffron-light);justify-content:center;">Curated Marketplace</div>
      <h1 class="bc-section-title" id="shop-heading" style="color:white;font-size:clamp(2rem,4vw,3rem);">Shop Conscious. Shop India.</h1>
      <p class="bc-section-desc" style="color:rgba(255,255,255,0.75);margin:0 auto 28px;">Discover handcrafted products from artisans and sustainable brands across India.</p>
    </div>
  </div>
</section>

<!-- Filter + Product Grid -->
<section class="section-py" style="background:var(--bg-cream);" aria-label="Shop products">
  <div class="container">
    <div class="bc-shop-layout">

      <!-- Sidebar Filters -->
      <aside class="bc-reveal-left bc-shop-sidebar" aria-label="Product filters">
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:24px;">
          <h2 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;margin-bottom:20px;color:var(--text-primary);padding-bottom:12px;border-bottom:1px solid var(--border-light);">
            <i class="fa-solid fa-sliders" style="color:var(--saffron);margin-right:8px;"></i> Filters
          </h2>

          <!-- Category Filter --><div class="bc-live-filter-group" id="bc-live-categories"><h3>Category</h3><div class="bc-live-options" data-category-options></div></div><!-- Price Range -->
          <div style="margin-bottom:24px;">
            <h3 style="font-size:0.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-primary);margin-bottom:12px;">Price Range</h3>
            <div style="display:flex;gap:8px;margin-bottom:10px;">
              <input type="number" placeholder="Min ₹" style="flex:1;padding:8px 10px;border:1.5px solid var(--border-warm);border-radius:var(--r-sm);font-size:0.875rem;color:var(--text-primary);background:var(--bg-cream);width:100%;" />
              <input type="number" placeholder="Max ₹" style="flex:1;padding:8px 10px;border:1.5px solid var(--border-warm);border-radius:var(--r-sm);font-size:0.875rem;color:var(--text-primary);background:var(--bg-cream);width:100%;" />
            </div>
            <small style="color:var(--text-muted);">Enter a minimum, maximum, or both.</small>
          </div>

          <div class="bc-live-filter-group" id="bc-live-materials" hidden><h3>Material</h3><div class="bc-live-options" data-material-options></div></div>
          <div class="bc-live-filter-group" id="bc-live-origins" hidden><h3>Made in</h3><div class="bc-live-options" data-origin-options></div></div>
          <!-- Seller Type --><div class="bc-live-filter-group"><h3>Seller type</h3><div class="bc-live-options"><label><input type="checkbox" value="artisan" data-seller-type> Artisan or maker</label><label><input type="checkbox" value="brand" data-seller-type> Brand or business</label></div></div><button class="bc-btn bc-btn-primary" style="width:100%;">Apply Filters</button>
          <button class="bc-btn bc-btn-outline" style="width:100%;margin-top:8px;">Clear All</button>
        </div>
      </aside>

      <!-- Product Grid -->
      <div>
        <!-- Sort Bar -->
        <div class="bc-shop-sortbar">
          <span style="font-size:0.875rem;color:var(--text-muted);">Showing <strong style="color:var(--text-primary);">Loading products…</strong></span>
          <div class="bc-shop-sort-controls">
            <label style="font-size:0.875rem;color:var(--text-muted);">Sort by:</label>
            <select style="border:1.5px solid var(--border-warm);border-radius:var(--r-sm);padding:6px 12px;font-size:0.875rem;color:var(--text-secondary);background:var(--bg-cream);">
              <option>Featured</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Newest First</option>
            </select>

          </div>
        </div>

        <p id="bc-shop-demo-note" role="status" hidden></p>
        <!-- Product categories are rendered from the catalogue database -->
        <div class="bc-product-tabs" id="bc-live-tabs" aria-label="Product categories"></div>
        <!-- Products -->
        <div class="bc-shop-product-grid" aria-live="polite"></div>

      </div>
    </div>
  </div>
</section>

<!-- Trust Strip -->
<section class="bc-trust-strip" aria-label="Shopping trust signals">
  <div class="container">
    <div class="bc-trust-inner">
      <div class="bc-trust-item"><div class="bc-trust-icon saffron"><i class="fa-solid fa-shield-halved"></i></div><div><div class="bc-trust-info-title">Seller transparency</div><div class="bc-trust-info-desc">Local demo items are clearly marked</div></div></div>
      <div class="bc-trust-divider"></div>
      <div class="bc-trust-item"><div class="bc-trust-icon navy"><i class="fa-solid fa-truck-fast"></i></div><div><div class="bc-trust-info-title">Vendor-managed delivery</div><div class="bc-trust-info-desc">Rates shown at checkout</div></div></div>
      <div class="bc-trust-divider"></div>
      <div class="bc-trust-item"><div class="bc-trust-icon green"><i class="fa-solid fa-rotate-left"></i></div><div><div class="bc-trust-info-title">Policy-based returns</div><div class="bc-trust-info-desc">Eligibility shown per order</div></div></div>
      <div class="bc-trust-divider"></div>
      <div class="bc-trust-item"><div class="bc-trust-icon gold"><i class="fa-solid fa-lock"></i></div><div><div class="bc-trust-info-title">Secure Payments</div><div class="bc-trust-info-desc">UPI, Cards, Net Banking</div></div></div>
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
<script src="assets/js/shop-live.js?v=20261017-3"></script>
</body>
</html>
