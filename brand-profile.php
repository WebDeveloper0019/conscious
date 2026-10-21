<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover EarthWeave Co. — a verified sustainable fashion brand from Jaipur. GOTS & Fair Trade certified." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="EarthWeave Co. | Sustainable Fashion Brand | Conscious Indi" />
  <meta property="og:description" content="Sustainable fashion brand using natural dyes and supporting 340+ weavers in Rajasthan." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>EarthWeave Co. — Brand Profile | Conscious Indi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Brand cover ===== */
.bp-cover-grid{display:grid;grid-template-columns:auto 1fr auto;gap:32px;align-items:center;}
.bp-cover-logo{width:100px;height:100px;border-radius:var(--r-xl);background:linear-gradient(135deg,#FFF3E6,#FFE0B2);display:flex;align-items:center;justify-content:center;font-size:3rem;border:3px solid rgba(255,153,51,0.4);flex-shrink:0;}
.bp-cover-badges{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap;}
.bp-cover-title{font-family:var(--font-display);font-size:clamp(1.5rem,4vw,2.5rem);font-weight:800;color:white;margin-bottom:8px;}
.bp-cover-meta{display:flex;gap:20px;flex-wrap:wrap;color:rgba(255,255,255,0.7);font-size:0.875rem;}
.bp-cover-actions{display:flex;flex-direction:column;gap:10px;}

/* ===== Two-column content layout (story + sidebar) ===== */
.bp-main-grid{display:grid;grid-template-columns:2fr 1fr;gap:48px;align-items:start;}

/* ===== Card grids ===== */
.bp-cert-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.bp-products-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
.bp-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

/* ===== Responsive breakpoints ===== */
@media (max-width:991px){
  .bp-main-grid{grid-template-columns:1fr;gap:32px;}
  .bp-cover-grid{grid-template-columns:1fr;text-align:center;gap:20px;}
  .bp-cover-logo{margin:0 auto;}
  .bp-cover-badges,.bp-cover-meta{justify-content:center;}
  .bp-cover-actions{flex-direction:row;justify-content:center;}
}
@media (max-width:767px){
  .bp-products-grid{grid-template-columns:repeat(2,1fr);}
}
@media (max-width:575px){
  .bp-cert-grid{grid-template-columns:1fr;}
  .bp-products-grid{grid-template-columns:1fr;}
  .bp-cover-actions{flex-direction:column;width:100%;}
  .bp-cover-actions .bc-btn{width:100%;justify-content:center;}
  .bp-cover-meta{flex-direction:column;gap:8px;align-items:center;}
}
@media (max-width:420px){
  .bp-stats-grid{gap:10px;}
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
            <li class="breadcrumb-item"><a href="/conscious/brands">Brands</a></li>
            <li class="breadcrumb-item active" aria-current="page">EarthWeave Co.</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm" style="background:linear-gradient(135deg,var(--navy),#1A0050);" aria-label="Brand cover">
  <div class="container">
    <div class="bp-cover-grid bc-reveal">
      <div class="bp-cover-logo">🌿</div>
      <div>
        <div class="bp-cover-badges">
          <span class="bc-badge" style="background:rgba(19,136,8,0.2);color:#5EE85E;border:1px solid rgba(19,136,8,0.3);"><i class="fa-solid fa-circle-check"></i> Verified Brand</span>
          <span class="bc-badge" style="background:rgba(255,153,51,0.2);color:var(--saffron-light);border:1px solid rgba(255,153,51,0.3);">🏆 ESG Certified</span>
          <span class="bc-badge" style="background:rgba(201,164,74,0.2);color:var(--gold-light);border:1px solid rgba(201,164,74,0.3);">Conscious Indi Partner</span>
        </div>
        <h1 class="bp-cover-title">EarthWeave Co.</h1>
        <div class="bp-cover-meta">
          <span><i class="fa-solid fa-location-dot" style="color:var(--saffron-light);"></i> Jaipur, Rajasthan</span>
          <span><i class="fa-regular fa-calendar" style="color:var(--saffron-light);"></i> Since 2018</span>
          <span><i class="fa-solid fa-leaf" style="color:var(--saffron-light);"></i> Sustainable Fashion &amp; Textiles</span>
          <span><i class="fa-solid fa-star" style="color:#FFC107;"></i> 4.9 (1,240 reviews)</span>
        </div>
      </div>
      <div class="bp-cover-actions">
        <a href="/conscious/shop" class="bc-btn bc-btn-primary"><i class="fa-solid fa-bag-shopping"></i> Shop Brand</a>
        <a href="/conscious/contact" class="bc-btn bc-btn-outline-white bc-btn-sm"><i class="fa-regular fa-envelope"></i> Contact Brand</a>
      </div>
    </div>
  </div>
</section>

<section class="section-py" style="background:var(--bg-cream);">
  <div class="container">
    <div class="bp-main-grid">
      <!-- Story & Mission -->
      <div class="bc-reveal-left">
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:32px;margin-bottom:24px;">
          <div class="bc-section-label">Brand Story</div>
          <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">Weaving a Conscious Future</h2>
          <p style="font-size:1rem;color:var(--text-secondary);line-height:1.8;margin-bottom:16px;">EarthWeave Co. was born in 2018 from a simple belief: fashion can be beautiful and ethical at the same time. Founder Meera Joshi quit her corporate career to work with the handloom weavers of Barmer, Rajasthan — women whose centuries-old weaving skills were at risk of disappearing.</p>
          <p style="font-size:1rem;color:var(--text-secondary);line-height:1.8;margin-bottom:16px;">Today, EarthWeave partners with 340+ weavers across Rajasthan and Gujarat, using only natural plant-based dyes and 100% organic cotton. Every piece tells the story of the hands that made it.</p>
          <blockquote style="border-left:4px solid var(--saffron);padding:12px 20px;background:var(--saffron-pale);border-radius:0 var(--r-md) var(--r-md) 0;font-style:italic;color:var(--text-secondary);font-size:1.0625rem;line-height:1.7;">
            "We don't just sell clothes. We sell stories — of women who weave their dreams into every thread."
            <cite style="display:block;margin-top:8px;font-style:normal;font-weight:700;color:var(--saffron-dark);">— Meera Joshi, Founder</cite>
          </blockquote>
        </div>

        <!-- Certifications -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;margin-bottom:24px;">
          <h3 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:20px;">Certifications &amp; Standards</h3>
          <div class="bp-cert-grid">
            <div style="display:flex;gap:12px;align-items:center;padding:12px;background:var(--green-pale);border-radius:var(--r-md);border:1px solid rgba(19,136,8,0.15);">
              <div style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;"><i class="fa-solid fa-leaf"></i></div>
              <div><div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);">GOTS Certified</div><div style="font-size:0.75rem;color:var(--text-muted);">Organic Textile Standard</div></div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;padding:12px;background:var(--navy-pale);border-radius:var(--r-md);border:1px solid rgba(0,0,128,0.1);">
              <div style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--navy);display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;"><i class="fa-solid fa-shield-halved"></i></div>
              <div><div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);">Fair Trade</div><div style="font-size:0.75rem;color:var(--text-muted);">Ethical Sourcing Certified</div></div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;padding:12px;background:var(--saffron-pale);border-radius:var(--r-md);border:1px solid rgba(255,153,51,0.15);">
              <div style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--saffron);display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;"><i class="fa-solid fa-recycle"></i></div>
              <div><div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);">B Corp Certified</div><div style="font-size:0.75rem;color:var(--text-muted);">ESG &amp; Social Impact</div></div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;padding:12px;background:var(--gold-pale);border-radius:var(--r-md);border:1px solid rgba(201,164,74,0.15);">
              <div style="width:36px;height:36px;border-radius:var(--r-sm);background:var(--gold-dark);display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;"><i class="fa-solid fa-award"></i></div>
              <div><div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);">GI Tagged Products</div><div style="font-size:0.75rem;color:var(--text-muted);">Geographical Indication</div></div>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;">
          <h3 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:20px;">Featured Products</h3>
          <div class="bp-products-grid">
            <article class="bc-product-card" data-category="handloom"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🧵</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Natural Dye Block Print Kurta</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹1,899</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="handloom"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🧶</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Kantha Stitch Tote Bag</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹699</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="handloom"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">👗</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Indigo Ajrakh Print Dupatta</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9375rem;">₹1,299</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div></div></article>
          </div>
          <div style="text-align:center;margin-top:16px;"><a href="/conscious/shop" class="bc-btn bc-btn-outline bc-btn-sm">View All 120 Products</a></div>
        </div>
      </div>

      <!-- Sidebar Stats & Social -->
      <div class="bc-reveal-right">
        <!-- Stats -->
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:var(--r-xl);padding:28px;color:white;margin-bottom:20px;">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;color:white;">Brand Impact</h3>
          <div class="bp-stats-grid">
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">340+</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;">Weavers Supported</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">120+</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;">Products</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">4.9★</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;">Customer Rating</div>
            </div>
            <div style="text-align:center;padding:14px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);">
              <div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--saffron-light);">2,400+</div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:4px;">Happy Orders</div>
            </div>
          </div>
        </div>

        <!-- Contact & Social -->
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:24px;margin-bottom:20px;">
          <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">Connect with Brand</h3>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="#" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:var(--r-md);border:1.5px solid var(--border-warm);color:var(--text-secondary);text-decoration:none;font-size:0.875rem;transition:all var(--t);">
              <i class="fa-brands fa-instagram" style="color:#E4405F;font-size:1.125rem;"></i> @earthweave.co
            </a>
            <a href="#" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:var(--r-md);border:1.5px solid var(--border-warm);color:var(--text-secondary);text-decoration:none;font-size:0.875rem;">
              <i class="fa-brands fa-facebook-f" style="color:#1877F2;font-size:1.125rem;"></i> EarthWeave Co.
            </a>
            <a href="/conscious/contact" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:var(--r-md);border:1.5px solid var(--border-warm);color:var(--text-secondary);text-decoration:none;font-size:0.875rem;">
              <i class="fa-regular fa-envelope" style="color:var(--saffron);font-size:1.125rem;"></i> Contact Brand
            </a>
          </div>
        </div>

        <!-- Partner Info -->
        <div style="background:var(--saffron-pale);border-radius:var(--r-lg);border:1.5px solid rgba(255,153,51,0.2);padding:20px;">
          <div style="font-size:0.875rem;font-weight:700;color:var(--saffron-dark);margin-bottom:8px;"><i class="fa-solid fa-certificate"></i> Conscious Indi Verified</div>
          <p style="font-size:0.8125rem;color:var(--text-secondary);line-height:1.6;margin:0;">This brand has passed our 12-point sustainability audit covering ethical sourcing, fair wages, environmental impact, and product authenticity.</p>
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