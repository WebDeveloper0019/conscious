<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover certified products with Geographical Indication and sustainability credentials from verified brands on Conscious India." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="GI Tag Marketplace | Conscious India" />
  <meta property="og:description" content="Authentic, origin-protected products that preserve heritage, support communities and protect the planet." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>GI Tag Marketplace — Conscious India | Authentic, Origin-Protected &amp; Heritage Certified</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== GI Hero (matches Vegan hero exactly) ===== */
.gi-hero{background:linear-gradient(120deg,#F7F3EA,#EFE9DB);position:relative;overflow:hidden;padding:48px 0;}
.gi-hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:center;position:relative;z-index:1;}
.gi-hero-title{font-family:var(--font-display);font-weight:800;font-size:clamp(2rem,4vw,3.25rem);color:var(--navy-dark);margin-bottom:14px;line-height:1.08;}
.gi-hero-title span{color:var(--saffron);}
.gi-hero-desc{font-size:1.0625rem;color:var(--text-secondary);opacity:0.9;max-width:460px;margin-bottom:24px;line-height:1.6;}
.gi-hero-feats{display:flex;flex-wrap:wrap;gap:22px;}
.gi-hero-feat{display:flex;align-items:center;gap:10px;}
.gi-hero-feat-icon{width:42px;height:42px;border-radius:50%;background:#fff;border:1px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.05rem;flex-shrink:0;}
.gi-hero-feat-title{font-size:0.8125rem;font-weight:700;color:var(--text-primary);}
.gi-hero-img-wrap{position:relative;display:flex;align-items:center;justify-content:center;}
.gi-hero-img{position:relative;width:100%;max-width:460px;height:300px;object-fit:cover;border-radius:16px;z-index:1;box-shadow:0 20px 40px rgba(0,0,0,0.12);}
.gi-hero-badge{position:absolute;bottom:-18px;right:6%;width:96px;height:96px;border-radius:50%;background:var(--navy-dark);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-size:0.6rem;font-weight:700;letter-spacing:0.03em;box-shadow:0 10px 24px rgba(0,0,0,0.2);z-index:2;padding:8px;}
.gi-hero-badge i{font-size:1.3rem;margin-bottom:4px;color:var(--saffron-light);}

/* ===== Explore section (matches Vegan explore section exactly) ===== */
.gi-explore-section{background:#fff;padding:56px 0 24px;}
.gi-section-head{text-align:center;margin-bottom:36px;}
.gi-section-kicker{display:flex;align-items:center;justify-content:center;gap:10px;font-family:var(--font-display);font-weight:700;font-size:1.625rem;color:var(--text-primary);margin-bottom:8px;}
.gi-section-kicker i{color:var(--saffron);font-size:1rem;}
.gi-section-sub{font-size:0.9375rem;color:var(--text-secondary);opacity:0.85;}

.gi-group{margin-bottom:36px;}
.gi-group-title{display:flex;align-items:center;gap:10px;font-family:var(--font-display);font-weight:700;font-size:1.125rem;color:var(--text-primary);margin-bottom:16px;}
.gi-group-title i{color:var(--saffron);font-size:0.9375rem;}
.gi-group-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
.gi-group-grid.cols-3{grid-template-columns:repeat(3,1fr);}

.gi-cert-card{background:#fff;border:1px solid var(--border-warm);border-radius:14px;padding:20px;transition:transform .2s ease,box-shadow .2s ease;}
.gi-cert-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,0.08);}
.gi-cert-tag{display:inline-block;font-size:0.6875rem;font-weight:600;color:var(--text-secondary);background:var(--saffron-pale);border-radius:var(--r-full);padding:3px 12px;margin-bottom:14px;}
.gi-cert-top{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.gi-cert-icon{width:50px;height:50px;border-radius:50%;background:var(--saffron-pale);border:1px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.3rem;flex-shrink:0;}
.gi-cert-title{font-weight:700;font-size:0.9375rem;color:var(--text-primary);}
.gi-cert-desc{font-size:0.8125rem;color:var(--text-secondary);opacity:0.85;line-height:1.5;margin-bottom:14px;min-height:38px;}
.gi-cert-count{display:flex;align-items:center;gap:6px;font-size:0.75rem;color:var(--text-secondary);opacity:0.8;margin-bottom:14px;}
.gi-cert-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:var(--navy-dark);color:#fff;border:none;border-radius:var(--r-full);padding:10px;font-size:0.8125rem;font-weight:600;text-decoration:none;transition:background .2s ease;}
.gi-cert-btn:hover{background:#152c1c;color:#fff;}

/* ===== GI brands (matches Vegan brands section exactly) ===== */
.gi-brands-section{background:var(--bg-cream);padding:24px 0 56px;}
.gi-brands-track{display:grid;grid-template-columns:repeat(7,1fr);gap:16px;position:relative;}
.gi-brand-card{background:#fff;border:1px solid var(--border-warm);border-radius:14px;padding:18px 12px;text-align:center;}
.gi-brand-logo{width:56px;height:56px;border-radius:50%;background:var(--saffron-pale);display:flex;align-items:center;justify-content:center;color:var(--navy-dark);font-size:1.3rem;margin:0 auto 10px;font-family:var(--font-display);font-weight:700;overflow:hidden;}
.gi-brand-name{font-weight:700;font-size:0.8125rem;color:var(--text-primary);margin-bottom:8px;}
.gi-brand-badge{display:inline-block;font-size:0.625rem;font-weight:700;letter-spacing:0.02em;color:var(--navy-dark);background:var(--gold-pale);border-radius:var(--r-full);padding:3px 10px;margin-bottom:10px;}
.gi-brand-link{display:inline-flex;align-items:center;gap:6px;font-size:0.75rem;font-weight:600;color:var(--text-primary);border:1px solid var(--border-warm);border-radius:var(--r-full);padding:5px 14px;text-decoration:none;}
.gi-brand-link:hover{background:var(--navy-dark);color:#fff;border-color:var(--navy-dark);}
.gi-brands-nav{display:flex;align-items:center;justify-content:center;gap:16px;margin-top:24px;}
.gi-brands-nav-btn{width:36px;height:36px;border-radius:50%;border:1px solid var(--border-warm);background:#fff;color:var(--text-primary);display:flex;align-items:center;justify-content:center;cursor:pointer;}
.gi-brands-nav-btn:hover{background:var(--navy-dark);color:#fff;border-color:var(--navy-dark);}
.gi-brands-explore{text-align:center;margin-top:8px;}

/* ===== Responsive (matches Vegan breakpoints exactly) ===== */
@media (max-width:1199px){
  .gi-group-grid{grid-template-columns:repeat(3,1fr);}
  .gi-group-grid.cols-3{grid-template-columns:repeat(3,1fr);}
  .gi-brands-track{grid-template-columns:repeat(4,1fr);}
}
@media (max-width:991px){
  .gi-hero-grid{grid-template-columns:1fr;text-align:center;}
  .gi-hero-desc{margin-left:auto;margin-right:auto;}
  .gi-hero-feats{justify-content:center;}
  .gi-hero-img-wrap{order:-1;margin-bottom:24px;}
  .gi-group-grid,.gi-group-grid.cols-3{grid-template-columns:repeat(2,1fr);}
  .gi-brands-track{grid-template-columns:repeat(3,1fr);}
}
@media (max-width:767px){
  .gi-group-grid,.gi-group-grid.cols-3{grid-template-columns:repeat(2,1fr);}
  .gi-brands-track{grid-template-columns:repeat(2,1fr);}
  .gi-hero-feats{gap:14px;}
}
@media (max-width:480px){
  .gi-group-grid,.gi-group-grid.cols-3{grid-template-columns:1fr;}
  .gi-brands-track{grid-template-columns:repeat(2,1fr);}
  .gi-hero-img{height:220px;}
}
@media (max-width:360px){
  .gi-brands-track{grid-template-columns:1fr;}
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
        <li class="breadcrumb-item active" aria-current="page">GI Tag</li>
      </ol>
    </nav>
  </div>
</div>

<!-- HERO -->
<section class="gi-hero" aria-labelledby="gi-hero-heading">
  <div class="container">
    <div class="gi-hero-grid">
      <div class="bc-reveal">
        <h1 class="gi-hero-title" id="gi-hero-heading">GI Tag <span>Marketplace</span></h1>
        <p class="gi-hero-desc">Discover certified products with Geographical Indication and sustainability credentials that preserve heritage, support communities and protect the planet.</p>
        <div class="gi-hero-feats">
          <div class="gi-hero-feat">
            <div class="gi-hero-feat-icon"><i class="fa-solid fa-leaf"></i></div>
            <div class="gi-hero-feat-title">Authentic Products</div>
          </div>
          <div class="gi-hero-feat">
            <div class="gi-hero-feat-icon"><i class="fa-solid fa-people-group"></i></div>
            <div class="gi-hero-feat-title">Supports Local Communities</div>
          </div>
          <div class="gi-hero-feat">
            <div class="gi-hero-feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="gi-hero-feat-title">Verified Certifications</div>
          </div>
          <div class="gi-hero-feat">
            <div class="gi-hero-feat-icon"><i class="fa-solid fa-stamp"></i></div>
            <div class="gi-hero-feat-title">Protected Origin</div>
          </div>
        </div>
      </div>
      <div class="gi-hero-img-wrap bc-reveal delay-2">
        <img class="gi-hero-img" src="assets/images/gi-tag/hero-gi.jpg" alt="Certified GI-tagged heritage crafts — pottery, textiles and brassware" width="480" height="300" loading="eager" />
        <div class="gi-hero-badge"><i class="fa-solid fa-stamp"></i>PROTECTED ORIGIN</div>
      </div>
    </div>
  </div>
</section>

<!-- EXPLORE CERTIFICATIONS -->
<section class="gi-explore-section" aria-labelledby="gi-cat-heading">
  <div class="container">
    <div class="gi-section-head bc-reveal">
      <div class="gi-section-kicker"><i class="fa-solid fa-leaf"></i> Explore Certifications <i class="fa-solid fa-leaf"></i></div>
      <p class="gi-section-sub" id="gi-cat-heading">Discover trusted certifications that stand for quality, authenticity and sustainability.</p>
    </div>

    <div class="gi-group bc-reveal">
      <div class="gi-group-title"><i class="fa-solid fa-leaf"></i> Organic</div>
      <div class="gi-group-grid cols-3">
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Organic</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-leaf"></i></div>
            <div class="gi-cert-title">India Organic</div>
          </div>
          <p class="gi-cert-desc">Organic agriculture and processing.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 18 approved brands</div>
          <a href="/conscious/brands?cert=india-organic" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Organic</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-certificate"></i></div>
            <div class="gi-cert-title">PGS-India</div>
          </div>
          <p class="gi-cert-desc">Participatory organic assurance.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 11 approved brands</div>
          <a href="/conscious/brands?cert=pgs-india" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Organic</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-seedling"></i></div>
            <div class="gi-cert-title">Vegan</div>
          </div>
          <p class="gi-cert-desc">Animal-free formulation and materials.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 9 approved brands</div>
          <a href="/conscious/brands?cert=vegan" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

    <div class="gi-group bc-reveal delay-1">
      <div class="gi-group-title"><i class="fa-solid fa-shirt"></i> Textile / Product Sustainability</div>
      <div class="gi-group-grid cols-3">
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Textile</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-shirt"></i></div>
            <div class="gi-cert-title">GOTS</div>
          </div>
          <p class="gi-cert-desc">Organic textile standard.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 14 approved brands</div>
          <a href="/conscious/brands?cert=gots" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Textile</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-handshake"></i></div>
            <div class="gi-cert-title">Fair Trade</div>
          </div>
          <p class="gi-cert-desc">Fair livelihoods and traceability.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 22 approved brands</div>
          <a href="/conscious/brands?cert=fair-trade" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">Textile</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-flask"></i></div>
            <div class="gi-cert-title">OEKO-TEX</div>
          </div>
          <p class="gi-cert-desc">Safer textile chemistry.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 16 approved brands</div>
          <a href="/conscious/brands?cert=oeko-tex" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

    <div class="gi-group bc-reveal delay-2">
      <div class="gi-group-title"><i class="fa-solid fa-globe"></i> General Sustainability</div>
      <div class="gi-group-grid cols-3">
        <div class="gi-cert-card">
          <span class="gi-cert-tag">General</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-stamp"></i></div>
            <div class="gi-cert-title">GI Tag</div>
          </div>
          <p class="gi-cert-desc">Protected origin and traditional knowledge.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 26 approved brands</div>
          <a href="/conscious/brands?cert=gi-tag" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">General</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-earth-asia"></i></div>
            <div class="gi-cert-title">B Corp</div>
          </div>
          <p class="gi-cert-desc">Verified social and environmental practice.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 7 approved brands</div>
          <a href="/conscious/brands?cert=b-corp" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="gi-cert-card">
          <span class="gi-cert-tag">General</span>
          <div class="gi-cert-top">
            <div class="gi-cert-icon"><i class="fa-solid fa-recycle"></i></div>
            <div class="gi-cert-title">Zero Waste</div>
          </div>
          <p class="gi-cert-desc">Circular material and packaging practice.</p>
          <div class="gi-cert-count"><i class="fa-solid fa-people-group"></i> 12 approved brands</div>
          <a href="/conscious/brands?cert=zero-waste" class="gi-cert-btn">View Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CERTIFIED BRANDS -->
<section class="gi-brands-section" aria-labelledby="gi-brands-heading">
  <div class="container">
    <div class="gi-section-head bc-reveal">
      <div class="gi-section-kicker"><i class="fa-solid fa-leaf"></i> Certified Brands <i class="fa-solid fa-leaf"></i></div>
      <p class="gi-section-sub" id="gi-brands-heading">Discover verified brands approved by the Conscious India Admin under trusted certifications.</p>
    </div>
    <div class="gi-brands-track bc-reveal">
      <div class="gi-brand-card">
        <div class="gi-brand-logo">K</div>
        <div class="gi-brand-name">Kantha Collective</div>
        <span class="gi-brand-badge">GI Tag</span><br>
        <a href="/conscious/brand-products?brand=kantha" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">B</div>
        <div class="gi-brand-name">Blue Pottery House</div>
        <span class="gi-brand-badge">GI Tag</span><br>
        <a href="/conscious/brand-products?brand=blue-pottery" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">M</div>
        <div class="gi-brand-name">Muga Loom Studio</div>
        <span class="gi-brand-badge">GOTS</span><br>
        <a href="/conscious/brand-products?brand=muga" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">F</div>
        <div class="gi-brand-name">Forest Form</div>
        <span class="gi-brand-badge">Vegan</span><br>
        <a href="/conscious/brand-products?brand=forest-form" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">V</div>
        <div class="gi-brand-name">Valley Saffron Co.</div>
        <span class="gi-brand-badge">India Organic</span><br>
        <a href="/conscious/brand-products?brand=valley-saffron" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">E</div>
        <div class="gi-brand-name">Earthy Home</div>
        <span class="gi-brand-badge">Zero Waste</span><br>
        <a href="/conscious/brand-products?brand=earthy-home" class="gi-brand-link">View Products</a>
      </div>
      <div class="gi-brand-card">
        <div class="gi-brand-logo">P</div>
        <div class="gi-brand-name">Pashmina Weave Co.</div>
        <span class="gi-brand-badge">GI Tag</span><br>
        <a href="/conscious/brand-products?brand=pashmina-weave" class="gi-brand-link">View Products</a>
      </div>
    </div>
    <div class="gi-brands-nav">
      <button class="gi-brands-nav-btn" id="brandPrev" aria-label="Previous brands"><i class="fa-solid fa-chevron-left"></i></button>
      <button class="gi-brands-nav-btn" id="brandNext" aria-label="Next brands"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
    <div class="gi-brands-explore">
      <a href="/conscious/brands?cert=gi-tag" class="bc-btn bc-btn-navy">Explore All Certified Brands <i class="fa-solid fa-arrow-right"></i></a>
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
<script>
document.querySelector('#brandPrev')?.addEventListener('click',()=>document.querySelector('.gi-brands-track').scrollBy({left:-320,behavior:'smooth'}));
document.querySelector('#brandNext')?.addEventListener('click',()=>document.querySelector('.gi-brands-track').scrollBy({left:320,behavior:'smooth'}));
</script>
</body>
</html>