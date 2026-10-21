<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover curated collections from India's conscious creators. Shop products reviewed and recommended by top sustainability influencers." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Creator Shops | Conscious Indi" />
  <meta property="og:description" content="Shop curated collections from India's top sustainable living creators and influencers." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Creator Shops — Conscious Indi | Shop with India's Conscious Creators</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}
</style>
<style>.creator-directory-subtitle + .bc-section-desc{display:none}</style>
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
            <li class="breadcrumb-item active" aria-current="page">Creators</li>
      </ol>
    </nav>
  </div>
</div>

<style>
/* Local creator-directory treatment; shared header, CTA, newsletter, and footer remain unchanged. */
.creators-page-hero{position:relative;overflow:hidden;padding:62px 0 64px!important;background:radial-gradient(circle at 17% 28%,rgba(244,151,44,.23),transparent 19%),radial-gradient(circle at 86% 74%,rgba(111,74,192,.22),transparent 25%),linear-gradient(125deg,#080145,#16066a 54%,#0a0347)!important}.creators-page-hero:before,.creators-page-hero:after{content:"";position:absolute;width:170px;height:170px;border:1px solid rgba(255,183,65,.22);border-radius:50%}.creators-page-hero:before{left:-54px;bottom:-88px}.creators-page-hero:after{right:-44px;top:-86px}.creators-page-hero .bc-section-desc{max-width:690px;line-height:1.75}.bc-influencer-cards-grid{display:none!important}
.creator-directory{background:linear-gradient(180deg,#fffdf9 0%,var(--bg-cream) 100%);padding:52px 0 64px}.creator-directory-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:24px}.creator-card{--accent:#fa6f45;position:relative;display:flex;min-height:405px;flex-direction:column;overflow:hidden;border:1px solid rgba(57,35,13,.08);border-radius:22px;background:#fff;box-shadow:0 10px 26px rgba(39,23,6,.08);transition:transform .28s ease,box-shadow .28s ease}.creator-card:hover{transform:translateY(-7px);box-shadow:0 20px 34px rgba(39,23,6,.14)}.creator-card:focus-within{box-shadow:0 0 0 3px rgba(255,130,31,.22),0 20px 34px rgba(39,23,6,.14)}.creator-card__accent{height:96px;background:linear-gradient(125deg,var(--accent),color-mix(in srgb,var(--accent) 55%,#211065));border-radius:0 0 47% 47% / 0 0 22% 22%}.creator-card__body{position:relative;display:flex;flex:1;flex-direction:column;align-items:center;padding:0 20px 20px;text-align:center}.creator-photo{width:112px;height:112px;position:relative;display:grid;place-items:center;overflow:hidden;margin-top:-57px;border:4px solid #fff;border-radius:50%;background:#f4eee6;box-shadow:0 8px 16px rgba(30,18,5,.16)}.creator-photo img{display:block;width:100%;height:100%;object-fit:cover}.creator-photo__empty{width:34px;height:34px;border:1px solid #d9ccbd;border-radius:50%;background:linear-gradient(180deg,#fffaf4,#e5d9ce)}.creator-verified{display:inline-grid;place-items:center;width:18px;height:18px;margin-left:3px;border:2px solid #fff;border-radius:50%;background:#2875d7;color:#fff;font-size:9px;vertical-align:2px}.creator-card h2{margin:13px 0 3px;color:var(--text-dark);font-size:1.03rem;font-weight:800;line-height:1.3}.creator-brand,.creator-niche{min-height:20px;color:var(--text-muted);font-size:.77rem}.creator-brand{font-weight:700;color:#5d4b3a}.creator-tags{display:flex;min-height:32px;flex-wrap:wrap;justify-content:center;gap:6px;margin:13px 0}.creator-tag{padding:5px 9px;border-radius:9px;background:#fff0df;color:#b85309;font-size:.66rem;font-weight:800;line-height:1.1}.creator-card__metrics{display:grid;width:100%;grid-template-columns:1fr 1fr;margin:2px 0 17px;border-top:1px solid #f0e9e2;border-bottom:1px solid #f0e9e2}.creator-metric{padding:10px 4px;color:#786957;font-size:.66rem;line-height:1.25}.creator-metric+.creator-metric{border-left:1px solid #f0e9e2}.creator-metric strong{display:block;margin-bottom:2px;color:var(--text-dark);font-size:.83rem}.creator-metric i{margin-right:3px;color:#ff8a20}.creator-card .bc-btn{width:100%;margin-top:auto;justify-content:center;border-color:#ff8a20;background:white;color:#e76d09;font-weight:800}.creator-card .bc-btn:hover{background:#ff8a20;color:white}.creator-card:nth-child(2){--accent:#3daf94}.creator-card:nth-child(3){--accent:#ecaa31}.creator-card:nth-child(4){--accent:#7950c5}.creator-card:nth-child(5){--accent:#3b68cc}.creator-card:nth-child(6){--accent:#d94885}.creator-card:nth-child(7){--accent:#07899d}.creator-card:nth-child(8){--accent:#ed642c}.creator-card:nth-child(2) .creator-tag{background:#e2f4ed;color:#18745e}.creator-card:nth-child(3) .creator-tag{background:#fff2d7;color:#b76b00}.creator-card:nth-child(4) .creator-tag{background:#efe7ff;color:#7040aa}.creator-card:nth-child(5) .creator-tag{background:#e5edff;color:#355bb8}.creator-card:nth-child(6) .creator-tag{background:#fee8f1;color:#b43068}.creator-card:nth-child(7) .creator-tag{background:#ddf3f5;color:#097183}.creator-card:nth-child(8) .creator-tag{background:#fff0df;color:#c65116}@media(max-width:991.98px){.creator-directory-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.creator-directory{padding-top:38px}}@media(max-width:575.98px){.creators-page-hero{padding:44px 0!important}.creator-directory-grid{grid-template-columns:1fr;gap:18px}.creator-card{min-height:390px}.creator-directory{padding:30px 0 42px}}
</style>
<section class="section-py-sm creators-page-hero" style="background:linear-gradient(135deg,#4A0D8E,#0A0040);" aria-labelledby="creators-heading">
  <div class="container">
    <div class="bc-reveal" style="text-align:center;color:white;">
      <div class="bc-section-label" style="color:var(--saffron-light);justify-content:center;">Creator Economy</div>
      <h1 class="bc-section-title" style="color:white;" id="creators-heading">Creator Shops</h1>
      <p class="bc-section-desc creator-directory-subtitle" style="color:rgba(255,255,255,0.75);margin:0 auto;">Discover and connect with India's most inspiring creators, influencers, artisans, and conscious brands.</p>
      <p class="bc-section-desc" style="color:rgba(255,255,255,0.75);margin:0 auto;">Shop curated collections from India's conscious content creators — reviewed, recommended and loved.</p>
    </div>
  </div>
</section>
<!-- <section class="section-py" style="background:var(--bg-cream);">
  <div class="container">
    <div class="bc-influencer-cards-grid bc-reveal" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">

      <div class="bc-inf-card bc-reveal" style="text-align:center;">
        <div class="bc-inf-avatar" style="background:linear-gradient(135deg,#FF6B6B,#C0392B);display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:white;margin:0 auto 12px;">👩</div>
        <div class="bc-inf-name">Priya Creates</div>
        <div class="bc-inf-niche">Sustainable Living · DIY Crafts</div>
        <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 2.4M Followers</div>
        <div style="margin-top:6px;font-size:0.75rem;color:var(--text-muted);">48 products · 4.9★</div>
        <a href="/conscious/creator-profile" class="bc-btn bc-btn-primary bc-btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Visit Shop</a>
      </div>

      <div class="bc-inf-card bc-reveal delay-1" style="text-align:center;">
        <div class="bc-inf-avatar" style="background:var(--green-pale);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 12px;">🧔</div>
        <div class="bc-inf-name">EcoArjun</div>
        <div class="bc-inf-niche">Zero Waste · Minimalism</div>
        <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 890K Followers</div>
        <div style="margin-top:6px;font-size:0.75rem;color:var(--text-muted);">32 products · 4.8★</div>
        <a href="/conscious/creator-profile" class="bc-btn bc-btn-primary bc-btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Visit Shop</a>
      </div>

      <div class="bc-inf-card bc-reveal delay-2" style="text-align:center;">
        <div class="bc-inf-avatar" style="background:var(--gold-pale);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 12px;">👩</div>
        <div class="bc-inf-name">Aditi Crafts</div>
        <div class="bc-inf-niche">Home Decor · Artisan Fashion</div>
        <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 1.2M Followers</div>
        <div style="margin-top:6px;font-size:0.75rem;color:var(--text-muted);">65 products · 4.9★</div>
        <a href="/conscious/creator-profile" class="bc-btn bc-btn-primary bc-btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Visit Shop</a>
      </div>

      <div class="bc-inf-card bc-reveal delay-3" style="text-align:center;">
        <div class="bc-inf-avatar" style="background:var(--navy-pale);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 12px;">🧑</div>
        <div class="bc-inf-name">Rohan Conscious</div>
        <div class="bc-inf-niche">Sustainable Fashion · Travel</div>
        <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 620K Followers</div>
        <div style="margin-top:6px;font-size:0.75rem;color:var(--text-muted);">28 products · 4.7★</div>
        <a href="/conscious/creator-profile" class="bc-btn bc-btn-primary bc-btn-sm" style="margin-top:14px;width:100%;justify-content:center;">Visit Shop</a>
      </div>

    </div>
  </div>
</section> -->
<section class="creator-directory" aria-label="Influencer directory"><div class="container"><div class="creator-directory-grid bc-reveal" aria-live="polite"></div></div></section>
<section class="section-py" style="background:linear-gradient(135deg,var(--navy),#0A0040);" aria-label="Become a creator">
  <div class="container">
    <div class="bc-reveal" style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">
      <div style="color:white;">
        <div class="bc-section-label" style="color:var(--saffron-light);">Join the Creator Economy</div>
        <h2 class="bc-section-title" style="color:white;">Become a Conscious Creator</h2>
        <p style="color:rgba(255,255,255,0.75);line-height:1.7;margin-bottom:20px;">Monetise your audience by curating a shop of products you truly believe in — handcrafted, sustainable, and meaningful.</p>
        <ul style="color:rgba(255,255,255,0.75);line-height:1.9;list-style:none;padding:0;margin-bottom:24px;">
          <li><i class="fa-solid fa-check-circle" style="color:var(--saffron-light);"></i> Earn 10–15% commission on every sale</li>
          <li><i class="fa-solid fa-check-circle" style="color:var(--saffron-light);"></i> Curate your own branded storefront</li>
          <li><i class="fa-solid fa-check-circle" style="color:var(--saffron-light);"></i> Access exclusive brand partnerships</li>
          <li><i class="fa-solid fa-check-circle" style="color:var(--saffron-light);"></i> Real-time analytics dashboard</li>
        </ul>
        <a href="/conscious/become-brand-partner" class="bc-btn bc-btn-primary"><i class="fa-solid fa-star"></i> Apply to Become a Creator</a>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div style="background:rgba(255,255,255,0.08);border-radius:var(--r-lg);padding:20px;text-align:center;color:white;">
          <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:var(--saffron-light);" data-count="500" data-suffix="+">500+</div>
          <div style="font-size:0.875rem;opacity:0.75;">Active Creators</div>
        </div>
        <div style="background:rgba(255,255,255,0.08);border-radius:var(--r-lg);padding:20px;text-align:center;color:white;">
          <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:var(--saffron-light);">₹2Cr+</div>
          <div style="font-size:0.875rem;opacity:0.75;">Creator Earnings</div>
        </div>
        <div style="background:rgba(255,255,255,0.08);border-radius:var(--r-lg);padding:20px;text-align:center;color:white;">
          <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:var(--saffron-light);">15%</div>
          <div style="font-size:0.875rem;opacity:0.75;">Avg. Commission</div>
        </div>
        <div style="background:rgba(255,255,255,0.08);border-radius:var(--r-lg);padding:20px;text-align:center;color:white;">
          <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:var(--saffron-light);" data-count="50" data-suffix="M+">50M+</div>
          <div style="font-size:0.875rem;opacity:0.75;">Reach Combined</div>
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
<style>.creator-demo{display:inline-block;vertical-align:2px;margin-left:5px;padding:3px 5px;border-radius:5px;background:#fff0d5;color:#935c00;font-size:9px;font-weight:800;letter-spacing:.05em}</style>
<script src="frontend/modules/influencer/assets/js/public-directory.js?v=20261017-3" defer></script>
</body>
</html>
