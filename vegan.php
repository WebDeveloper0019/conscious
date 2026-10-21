<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover ethical, plant-based and cruelty-free vegan products from verified brands on Conscious Indi." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Vegan Marketplace | Conscious Indi" />
  <meta property="og:description" content="Ethical, plant-based and cruelty-free products for a healthier you and a kinder planet." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Vegan Marketplace — Conscious Indi | Ethical, Plant-Based &amp; Cruelty-Free</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Vegan Hero ===== */
.vg-hero{background:linear-gradient(120deg,#F7F3EA,#EFE9DB);position:relative;overflow:hidden;padding:48px 0;}
.vg-hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:center;position:relative;z-index:1;}
.vg-hero-title{font-family:var(--font-display);font-weight:800;font-size:clamp(2rem,4vw,3.25rem);color:var(--navy-dark);margin-bottom:14px;line-height:1.08;}
.vg-hero-title span{color:var(--saffron);}
.vg-hero-desc{font-size:1.0625rem;color:var(--text-secondary);opacity:0.9;max-width:460px;margin-bottom:24px;line-height:1.6;}
.vg-hero-feats{display:flex;flex-wrap:wrap;gap:22px;}
.vg-hero-feat{display:flex;align-items:center;gap:10px;}
.vg-hero-feat-icon{width:42px;height:42px;border-radius:50%;background:#fff;border:1px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.05rem;flex-shrink:0;}
.vg-hero-feat-title{font-size:0.8125rem;font-weight:700;color:var(--text-primary);}
.vg-hero-img-wrap{position:relative;display:flex;align-items:center;justify-content:center;}
.vg-hero-img{position:relative;width:100%;max-width:460px;height:300px;object-fit:cover;border-radius:16px;z-index:1;box-shadow:0 20px 40px rgba(0,0,0,0.12);}
.vg-hero-badge{position:absolute;bottom:-18px;right:6%;width:96px;height:96px;border-radius:50%;background:var(--navy-dark);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-size:0.6rem;font-weight:700;letter-spacing:0.03em;box-shadow:0 10px 24px rgba(0,0,0,0.2);z-index:2;padding:8px;}
.vg-hero-badge i{font-size:1.3rem;margin-bottom:4px;color:var(--saffron-light);}

/* ===== Explore section ===== */
.vg-explore-section{background:#fff;padding:56px 0 24px;}
.vg-section-head{text-align:center;margin-bottom:36px;}
.vg-section-kicker{display:flex;align-items:center;justify-content:center;gap:10px;font-family:var(--font-display);font-weight:700;font-size:1.625rem;color:var(--text-primary);margin-bottom:8px;}
.vg-section-kicker i{color:var(--saffron);font-size:1rem;}
.vg-section-sub{font-size:0.9375rem;color:var(--text-secondary);opacity:0.85;}

.vg-group{margin-bottom:36px;}
.vg-group-title{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-weight:700;font-size:1.125rem;color:var(--text-primary);margin-bottom:16px;}
.vg-group-title i{color:var(--saffron);font-size:0.9375rem;}
.vg-group-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
.vg-group-grid.cols-3{grid-template-columns:repeat(3,1fr);}

.vg-cert-card{background:#fff;border:1px solid var(--border-warm);border-radius:14px;padding:20px;transition:transform .2s ease,box-shadow .2s ease;}
.vg-cert-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,0.08);}
.vg-cert-tag{display:inline-block;font-size:0.6875rem;font-weight:600;color:var(--text-secondary);background:var(--saffron-pale);border-radius:var(--r-full);padding:3px 12px;margin-bottom:14px;}
.vg-cert-top{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.vg-cert-icon{width:50px;height:50px;border-radius:50%;background:var(--saffron-pale);border:1px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.3rem;flex-shrink:0;}
.vg-cert-title{font-weight:700;font-size:0.9375rem;color:var(--text-primary);}
.vg-cert-desc{font-size:0.8125rem;color:var(--text-secondary);opacity:0.85;line-height:1.5;margin-bottom:14px;min-height:38px;}
.vg-cert-count{display:flex;align-items:center;gap:6px;font-size:0.75rem;color:var(--text-secondary);opacity:0.8;margin-bottom:14px;}
.vg-cert-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:var(--navy-dark);color:#fff;border:none;border-radius:var(--r-full);padding:10px;font-size:0.8125rem;font-weight:600;text-decoration:none;transition:background .2s ease;}
.vg-cert-btn:hover{background:#152c1c;color:#fff;}

/* ===== Vegan brands carousel ===== */
.vg-brands-section{background:var(--bg-cream);padding:24px 0 56px;}
.vg-brands-track{display:grid;grid-template-columns:repeat(7,1fr);gap:16px;position:relative;}
.vg-brand-card{background:#fff;border:1px solid var(--border-warm);border-radius:14px;padding:18px 12px;text-align:center;}
.vg-brand-logo{width:56px;height:56px;border-radius:50%;background:var(--saffron-pale);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.3rem;margin:0 auto 10px;}
.vg-brand-name{font-weight:700;font-size:0.8125rem;color:var(--text-primary);margin-bottom:10px;}
.vg-brand-link{display:inline-flex;align-items:center;gap:6px;font-size:0.75rem;font-weight:600;color:var(--text-primary);border:1px solid var(--border-warm);border-radius:var(--r-full);padding:5px 14px;text-decoration:none;}
.vg-brand-link:hover{background:var(--navy-dark);color:#fff;border-color:var(--navy-dark);}
.vg-brands-nav{display:flex;align-items:center;justify-content:center;gap:16px;margin-top:24px;}
.vg-brands-nav-btn{width:36px;height:36px;border-radius:50%;border:1px solid var(--border-warm);background:#fff;color:var(--text-primary);display:flex;align-items:center;justify-content:center;cursor:pointer;}
.vg-brands-explore{text-align:center;margin-top:8px;}

/* ===== Responsive ===== */
@media (max-width:1199px){
  .vg-group-grid{grid-template-columns:repeat(3,1fr);}
  .vg-group-grid.cols-3{grid-template-columns:repeat(3,1fr);}
  .vg-brands-track{grid-template-columns:repeat(4,1fr);}
}
@media (max-width:991px){
  .vg-hero-grid{grid-template-columns:1fr;text-align:center;}
  .vg-hero-desc{margin-left:auto;margin-right:auto;}
  .vg-hero-feats{justify-content:center;}
  .vg-hero-img-wrap{order:-1;margin-bottom:24px;}
  .vg-group-grid,.vg-group-grid.cols-3{grid-template-columns:repeat(2,1fr);}
  .vg-brands-track{grid-template-columns:repeat(3,1fr);}
}
@media (max-width:767px){
  .vg-group-grid,.vg-group-grid.cols-3{grid-template-columns:repeat(2,1fr);}
  .vg-brands-track{grid-template-columns:repeat(2,1fr);}
  .vg-hero-feats{gap:14px;}
}
@media (max-width:480px){
  .vg-group-grid,.vg-group-grid.cols-3{grid-template-columns:1fr;}
  .vg-brands-track{grid-template-columns:repeat(2,1fr);}
  .vg-hero-img{height:220px;}
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
        <li class="breadcrumb-item active" aria-current="page">Vegan</li>
      </ol>
    </nav>
  </div>
</div>

<!-- HERO -->
<section class="vg-hero" aria-labelledby="vg-hero-heading">
  <div class="container">
    <div class="vg-hero-grid">
      <div class="bc-reveal">
        <h1 class="vg-hero-title" id="vg-hero-heading">Vegan <span>Marketplace</span></h1>
        <p class="vg-hero-desc">Discover ethical, plant-based and cruelty-free products for a healthier you and a kinder planet.</p>
        <div class="vg-hero-feats">
          <div class="vg-hero-feat">
            <div class="vg-hero-feat-icon"><i class="fa-solid fa-leaf"></i></div>
            <div class="vg-hero-feat-title">Authentic Products</div>
          </div>
          <div class="vg-hero-feat">
            <div class="vg-hero-feat-icon"><i class="fa-solid fa-people-group"></i></div>
            <div class="vg-hero-feat-title">Supports Local Communities</div>
          </div>
          <div class="vg-hero-feat">
            <div class="vg-hero-feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="vg-hero-feat-title">Verified Certifications</div>
          </div>
          <div class="vg-hero-feat">
            <div class="vg-hero-feat-icon"><i class="fa-solid fa-globe"></i></div>
            <div class="vg-hero-feat-title">Sustainable Future</div>
          </div>
        </div>
      </div>
      <div class="vg-hero-img-wrap bc-reveal delay-2">
        <img class="vg-hero-img" src="assets/images/vegan/hero-vegan.jpg" alt="Curated vegan products — tote bag, skincare and grooming essentials" width="480" height="300" loading="eager" />
        <div class="vg-hero-badge"><i class="fa-solid fa-seedling"></i>100% VEGAN</div>
      </div>
    </div>
  </div>
</section>

<!-- EXPLORE VEGAN CATEGORIES -->
<section class="vg-explore-section" aria-labelledby="vg-cat-heading">
  <div class="container">
    <div class="vg-section-head bc-reveal">
      <div class="vg-section-kicker"><i class="fa-solid fa-leaf"></i> Explore Vegan Categories <i class="fa-solid fa-leaf"></i></div>
      <p class="vg-section-sub" id="vg-cat-heading">Discover trusted vegan categories that stand for ethics, quality and sustainability.</p>
    </div>

    <div class="vg-group bc-reveal">
      <div class="vg-group-title"><i class="fa-solid fa-bowl-food"></i> Food &amp; Nutrition</div>
      <div class="vg-group-grid">
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Food &amp; Beverages</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-mug-hot"></i></div>
            <div class="vg-cert-title">Plant-Based Pantry</div>
          </div>
          <p class="vg-cert-desc">Organic ingredients &amp; healthy beverages.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 42 vegan products</div>
          <a href="/conscious/shop?cat=food-beverages" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Supplements</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-capsules"></i></div>
            <div class="vg-cert-title">Wellness Supplements</div>
          </div>
          <p class="vg-cert-desc">Plant-based supplements for a balanced life.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 26 vegan products</div>
          <a href="/conscious/shop?cat=supplements" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Snacks</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-seedling"></i></div>
            <div class="vg-cert-title">Guilt-Free Snacks</div>
          </div>
          <p class="vg-cert-desc">Cruelty-free treats made from whole foods.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 31 vegan products</div>
          <a href="/conscious/shop?cat=snacks" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Certified</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="vg-cert-title">Vegan Certified</div>
          </div>
          <p class="vg-cert-desc">Animal-free formulation and materials.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-people-group"></i> 62 approved brands</div>
          <a href="/conscious/brands?cert=vegan" class="vg-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

    <div class="vg-group bc-reveal delay-1">
      <div class="vg-group-title"><i class="fa-solid fa-spa"></i> Personal &amp; Home Care</div>
      <div class="vg-group-grid cols-3">
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Personal Care</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-pump-soap"></i></div>
            <div class="vg-cert-title">Vegan Skin &amp; Hair Care</div>
          </div>
          <p class="vg-cert-desc">Skincare, haircare &amp; hygiene essentials.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 58 vegan products</div>
          <a href="/conscious/shop?cat=personal-care" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Home &amp; Living</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-house"></i></div>
            <div class="vg-cert-title">Eco Home Essentials</div>
          </div>
          <p class="vg-cert-desc">Eco-friendly, vegan products for your home.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 37 vegan products</div>
          <a href="/conscious/shop?cat=home-living" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Cruelty-Free</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-paw"></i></div>
            <div class="vg-cert-title">Cruelty-Free Certified</div>
          </div>
          <p class="vg-cert-desc">No animal testing, ever.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-people-group"></i> 45 approved brands</div>
          <a href="/conscious/brands?cert=cruelty-free" class="vg-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

    <div class="vg-group bc-reveal delay-2">
      <div class="vg-group-title"><i class="fa-solid fa-recycle"></i> Fashion &amp; Sustainability</div>
      <div class="vg-group-grid cols-3">
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Fashion &amp; Accessories</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <div class="vg-cert-title">Vegan Leather &amp; Style</div>
          </div>
          <p class="vg-cert-desc">Cruelty-free materials &amp; sustainable style.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-store"></i> 24 vegan products</div>
          <a href="/conscious/shop?cat=fashion-accessories" class="vg-cert-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">Eco Packaging</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-box-open"></i></div>
            <div class="vg-cert-title">Zero Waste Packaging</div>
          </div>
          <p class="vg-cert-desc">Circular material &amp; packaging practice.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-people-group"></i> 19 approved brands</div>
          <a href="/conscious/brands?cert=zero-waste" class="vg-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="vg-cert-card">
          <div class="vg-cert-tag">General Sustainability</div>
          <div class="vg-cert-top">
            <div class="vg-cert-icon"><i class="fa-solid fa-earth-asia"></i></div>
            <div class="vg-cert-title">B Corp Verified</div>
          </div>
          <p class="vg-cert-desc">Verified social &amp; environmental practice.</p>
          <div class="vg-cert-count"><i class="fa-solid fa-people-group"></i> 12 approved brands</div>
          <a href="/conscious/brands?cert=b-corp" class="vg-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- VEGAN BRANDS -->
<section class="vg-brands-section" aria-labelledby="vg-brands-heading">
  <div class="container">
    <div class="vg-section-head bc-reveal">
      <div class="vg-section-kicker"><i class="fa-solid fa-leaf"></i> Vegan Brands <i class="fa-solid fa-leaf"></i></div>
      <p class="vg-section-sub" id="vg-brands-heading">Discover conscious vegan brands that are certified and admin approved.</p>
    </div>
    <div class="vg-brands-track bc-reveal">
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-leaf"></i></div>
        <div class="vg-brand-name">Nature's Trunk</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-spa"></i></div>
        <div class="vg-brand-name">Botanical Bliss</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-mug-hot"></i></div>
        <div class="vg-brand-name">Healthy Junction</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-seedling"></i></div>
        <div class="vg-brand-name">EarthWeave Co.</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-pump-soap"></i></div>
        <div class="vg-brand-name">Conscious Chemist</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-bag-shopping"></i></div>
        <div class="vg-brand-name">Earthiest</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
      <div class="vg-brand-card">
        <div class="vg-brand-logo"><i class="fa-solid fa-house"></i></div>
        <div class="vg-brand-name">The Better Home</div>
        <a href="/conscious/brand-profile" class="vg-brand-link">View Products</a>
      </div>
    </div>
    <div class="vg-brands-nav">
      <button class="vg-brands-nav-btn" aria-label="Previous brands"><i class="fa-solid fa-chevron-left"></i></button>
      <button class="vg-brands-nav-btn" aria-label="Next brands"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
    <div class="vg-brands-explore">
      <a href="/conscious/brands?cat=vegan" class="bc-btn bc-btn-navy">Explore All Vegan Brands <i class="fa-solid fa-arrow-right"></i></a>
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
