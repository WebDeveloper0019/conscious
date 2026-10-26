<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Shop Priya's curated collection of sustainable products — handpicked and reviewed by India's top sustainable living creator." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Priya Creates | Creator Shop | Conscious India" />
  <meta property="og:description" content="Priya Creates' curated shop — 48 conscious products from India's top sustainable living influencer." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Priya Creates — Creator Shop | Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}
/* Influencer profile: populated with local dummy data until registration binding is added. */
.influencer-profile{--ip-orange:#ff9331;--ip-red:#b90f1d;--ip-navy:#121d84;--ip-cream:#fff8ed;--ip-ink:#25202a;--ip-muted:#655f67;background:var(--ip-cream);color:var(--ip-ink);}
.influencer-profile .ip-cover{min-height:0;position:relative;overflow:hidden;background:linear-gradient(105deg,#d62c2a 0%,#bb0e20 48%,#970817 100%);}
.influencer-profile .ip-cover:before{content:"";position:absolute;inset:0;opacity:.2;background-image:radial-gradient(circle at 78% 55%,transparent 0 28px,rgba(255,201,108,.7) 29px 30px,transparent 31px),linear-gradient(120deg,transparent 45%,rgba(255,205,117,.45) 45.2%,transparent 45.7%);background-size:120px 120px,180px 180px}.influencer-profile .ip-cover:after{content:"";position:absolute;width:360px;height:360px;right:-155px;bottom:-250px;border:1px solid rgba(255,210,127,.42);border-radius:50%;box-shadow:0 0 0 32px rgba(255,210,127,.05),0 0 0 70px rgba(255,210,127,.04)}
.ip-hero-inner{position:relative;z-index:1;padding:25px 0 22px;display:grid;grid-template-columns:90px minmax(0,1fr) 170px;gap:18px;align-items:center}.ip-avatar{width:90px;height:90px;border-radius:50%;object-fit:cover;background:#fff;border:3px solid #ffd683;box-shadow:0 7px 17px rgba(67,3,9,.35);margin:0}.ip-identity{color:#fff}.ip-eyebrow{font-size:.63rem;letter-spacing:.08em;margin:0 0 4px;color:#ffe3b8}.ip-name{font-family:var(--font-display);font-size:clamp(1.55rem,3vw,2.15rem);line-height:1;font-weight:800;letter-spacing:-.035em;margin:0 0 4px}.ip-brand{font-size:.76rem;font-weight:700;color:rgba(255,255,255,.88);margin:0}.ip-hero-meta{gap:6px;margin-top:10px}.ip-location{font-size:.7rem;color:#fff0d7;margin-right:2px}.ip-location i{color:#ffcc63;margin-right:4px}.ip-pill{padding:5px 8px;border-radius:5px;font-size:.62rem;background:rgba(85,0,11,.22);border-color:rgba(255,235,202,.3)}.ip-pill--availability{background:#fff0d1;color:#7e2510;border-color:#fff0d1}.ip-contact{justify-self:end;align-self:center;min-width:150px;padding:11px 16px!important;border-radius:24px!important;background:#ff9b31!important;border-color:#ff9b31!important;color:#fff!important;font-size:.76rem!important;box-shadow:0 7px 16px rgba(74,3,10,.28)}.ip-contact:hover,.ip-contact:focus{transform:translateY(-2px);background:#ffad4e!important;box-shadow:0 10px 20px rgba(74,3,10,.35)}
.ip-main{padding:15px 0 48px}.ip-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(255px,.85fr);gap:17px;align-items:start}.ip-stack{display:grid;gap:13px}.ip-card{background:#fffdfa;border:1px solid #f2e8da;border-radius:13px;padding:16px 18px;box-shadow:0 5px 16px rgba(74,55,34,.06);transition:transform .2s ease,box-shadow .2s ease}.ip-card:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(74,55,34,.1)}.ip-card-header{margin-bottom:10px}.ip-title{position:relative;padding-left:9px;margin:0;font-family:var(--font-display);font-size:1rem;font-weight:800;letter-spacing:-.02em;color:#3a3034}.ip-title:before{content:"";position:absolute;left:0;top:.16em;width:3px;height:.92em;background:var(--ip-orange);border-radius:2px}.ip-kicker{font-size:.59rem;letter-spacing:.075em;color:#bd7d3d}.ip-about{margin:0;color:var(--ip-muted);font-size:.75rem;line-height:1.65}.ip-about:first-letter{float:none;font:inherit;padding:0;color:inherit}
.ip-portfolio-grid{display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:94px;gap:8px}.ip-portfolio-item{position:relative;overflow:hidden;border-radius:7px;background:#f0d2ad;min-height:0}.ip-portfolio-item:first-child{grid-row:auto}.ip-portfolio-item:nth-child(2){background:#d3c8b8}.ip-portfolio-item:nth-child(3){background:#e1b985}.ip-portfolio-item img{width:100%;height:100%;display:block;object-fit:cover;opacity:1;mix-blend-mode:normal;transition:transform .35s ease}.ip-portfolio-item:hover img{transform:scale(1.08);opacity:1}.ip-portfolio-caption{display:none}
.ip-portfolio-url{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px;border:1px dashed #e9bf85;border-radius:8px;background:#fff8eb}.ip-portfolio-url p{margin:3px 0 0;color:var(--ip-muted);font-size:.76rem}.ip-portfolio-url .bc-btn{flex:0 0 auto}.ip-portfolio-actions{display:flex;justify-content:center;gap:10px;margin-top:12px}.ip-portfolio-actions .bc-btn{min-width:122px;border-radius:18px!important;font-size:.65rem;padding:8px 11px!important}
.ip-side-card{padding:15px}.ip-stats{background:linear-gradient(135deg,#172182,#10166c);color:#fff;border-color:#252b91}.ip-stats .ip-title{color:#fff}.ip-stats .ip-title:before{background:#ffb13b}.ip-stats .ip-kicker{color:#ffcf78}.ip-stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.ip-stat{padding:11px 7px 9px;text-align:center;border-radius:6px;background:rgba(255,255,255,.075)}.ip-stat strong{display:block;font-family:var(--font-display);font-size:1.16rem;line-height:1.1;color:#ffbd50}.ip-stat span{display:block;margin-top:4px;color:rgba(255,255,255,.7);font-size:.6rem}.ip-tag-list{display:flex;flex-wrap:wrap;gap:7px;margin-top:4px}.ip-tag{display:inline-flex;align-items:center;gap:5px;padding:6px 9px;border:0;background:#f8f0e7;border-radius:14px;color:#796050;font-size:.65rem;font-weight:700}.ip-availability{background:#fffdfa;color:var(--ip-ink);border-color:#f2e8da}.ip-availability .ip-title{color:#3a3034}.ip-availability .ip-kicker{color:#bd7d3d}.ip-availability-status{display:inline-flex;align-items:center;gap:7px;margin-top:3px;padding:6px 10px;background:#eff8ed;border-radius:14px;color:#42a84c;font-size:.67rem;font-weight:700}.ip-availability-status i{color:#39ae4a;font-size:.73rem}.ip-availability-note{display:none}.ip-social-list{display:grid;gap:0;margin-top:3px}.ip-social{gap:9px;padding:8px 0;border-bottom:1px solid #f2e8dc;color:var(--ip-ink);font-size:.72rem}.ip-social:hover,.ip-social:focus{color:#d96f18;padding-left:3px}.ip-social-icon{width:20px;height:20px;background:transparent;font-size:.76rem}.ip-social-text{display:flex;gap:4px;align-items:center}.ip-social-platform{display:none}.ip-social-handle{font-size:.69rem;font-weight:600}.ip-social-arrow{display:none}
.ip-modal .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 80px rgba(17,42,75,.27)}.ip-modal .modal-header{padding:24px 27px 15px;border:0;background:linear-gradient(120deg,#fff8ec,#fff)}.ip-modal .modal-title{font-family:var(--font-display);font-size:1.55rem;font-weight:800;color:var(--ip-navy)}.ip-modal .modal-body{padding:12px 27px 27px}.ip-modal .form-label{font-size:.85rem;font-weight:700;color:var(--ip-navy)}.ip-modal .form-control,.ip-modal .form-select{border-color:#e8d8c2;padding:.72rem .8rem}.ip-modal .form-control:focus,.ip-modal .form-select:focus{border-color:#ef8d2c;box-shadow:0 0 0 .22rem rgba(245,139,42,.16)}.ip-modal .form-text{color:#7c8693}.ip-modal-success{display:none;margin-bottom:16px}.ip-modal-success.show{display:block}.ip-modal .btn-close:focus{box-shadow:0 0 0 .22rem rgba(245,139,42,.18)}
@media (max-width:991.98px){.ip-hero-inner{grid-template-columns:82px minmax(0,1fr) auto;gap:14px;padding:22px 0}.ip-avatar{width:82px;height:82px}.ip-main{padding-top:14px}.ip-layout{grid-template-columns:1fr}.ip-sidebar{display:grid;grid-template-columns:1fr 1fr 1fr;gap:13px}.ip-sidebar .ip-card{margin:0}.ip-stats{grid-column:span 1}.ip-availability{grid-column:span 1}}
@media (max-width:575.98px){.ip-cover{overflow:hidden}.ip-hero-inner{grid-template-columns:68px 1fr;padding:19px 0 17px;gap:11px}.ip-avatar{position:static;width:68px;height:68px;border-width:2px;box-shadow:0 5px 12px rgba(67,3,9,.3)}.ip-eyebrow{display:none}.ip-name{font-size:1.4rem}.ip-brand{font-size:.67rem}.ip-hero-meta{margin-top:7px}.ip-location{width:100%;font-size:.65rem}.ip-pill{font-size:.56rem;padding:4px 6px}.ip-contact{grid-column:1 / -1;justify-self:stretch;min-width:0;text-align:center;padding:9px 13px!important}.ip-main{padding:12px 0 38px}.ip-layout,.ip-stack{gap:12px}.ip-card{border-radius:11px;padding:14px}.ip-sidebar{display:flex;flex-direction:column;gap:12px}.ip-portfolio-grid{grid-template-rows:70px;gap:6px}.ip-portfolio-actions{gap:7px}.ip-portfolio-actions .bc-btn{min-width:0;flex:1;padding:7px 6px!important;font-size:.58rem}.ip-portfolio-url{align-items:flex-start;flex-direction:column}.ip-modal .modal-dialog{margin:10px}.ip-modal .modal-header{padding:21px 20px 10px}.ip-modal .modal-body{padding:12px 20px 22px}}
@media (prefers-reduced-motion:reduce){.ip-card,.ip-portfolio-item img,.ip-contact,.ip-social{transition:none!important}}
.ip-hero-actions{justify-self:end;display:grid;justify-items:center;gap:9px}.ip-hero-actions .ip-contact{justify-self:auto;align-self:auto}.ip-connect{color:#ffe7be;font-size:.58rem;font-weight:700;text-align:center}.ip-hero-socials{display:flex;gap:7px}.ip-hero-socials a{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;color:#fff;text-decoration:none;font-size:.61rem;background:rgba(255,255,255,.17);transition:transform .2s ease,background .2s ease}.ip-hero-socials a:hover,.ip-hero-socials a:focus{background:#ff9b31;color:#fff;transform:translateY(-2px)}
@media (max-width:575.98px){.ip-hero-actions{grid-column:1 / -1;justify-self:stretch;grid-template-columns:1fr auto;align-items:center}.ip-hero-actions .ip-contact{grid-column:auto;justify-self:stretch}.ip-connect{display:none}}
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
            <li class="breadcrumb-item"><a href="/conscious/creators">Creators</a></li>
            <li class="breadcrumb-item active" aria-current="page">Priya Creates</li>
      </ol>
    </nav>
  </div>
</div>

<template aria-hidden="true">
<section class="section-py-sm" style="background:linear-gradient(135deg,#FF6B6B,#8B0000);" aria-label="Creator profile">
  <div class="container">
    <div style="display:grid;grid-template-columns:auto 1fr auto;gap:28px;align-items:center;" class="bc-reveal">
      <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#FF6B6B,#C0392B);display:flex;align-items:center;justify-content:center;font-size:2.5rem;border:3px solid rgba(255,255,255,0.3);">👩</div>
      <div style="color:white;">
        <h1 style="font-family:var(--font-display);font-size:clamp(1.5rem,3vw,2.25rem);font-weight:800;margin-bottom:6px;">Priya Creates</h1>
        <div style="color:rgba(255,255,255,0.7);font-size:0.9375rem;margin-bottom:12px;">@priya_creates · Sustainable Living & DIY Crafts</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <span class="bc-badge" style="background:rgba(255,255,255,0.2);color:white;"><i class="fa-brands fa-instagram"></i> 2.4M</span>
          <span class="bc-badge" style="background:rgba(255,255,255,0.2);color:white;"><i class="fa-brands fa-youtube"></i> 890K</span>
          <span class="bc-badge" style="background:rgba(255,153,51,0.3);color:var(--saffron-light);">✓ Verified Creator</span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <a href="/conscious/shop" class="bc-btn bc-btn-primary"><i class="fa-solid fa-bag-shopping"></i> Shop Collection</a>
        <button class="bc-btn bc-btn-outline-white bc-btn-sm"><i class="fa-solid fa-bell"></i> Follow</button>
      </div>
    </div>
  </div>
</section>
<section class="section-py" style="background:var(--bg-cream);">
  <div class="container">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:40px;align-items:start;">
      <div class="bc-reveal-left">
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;margin-bottom:24px;">
          <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:14px;">About Priya</h2>
          <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:0;">Priya is India's most-followed sustainable living creator. After a decade in corporate finance, she discovered that conscious choices could be beautiful, affordable and deeply satisfying. Her content — honest product reviews, DIY tutorials and artisan spotlights — has inspired 2.4 million people to live more intentionally.</p>
        </div>
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;">
          <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:20px;">Priya's Curated Collection</h2>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            <article class="bc-product-card" data-category="eco"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🌿</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Bamboo Dining Set</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9rem;">₹899</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="eco"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🧵</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Organic Cotton Kurta</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9rem;">₹1,499</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add"><i class="fa-solid fa-plus"></i></button></div></div></article>
            <article class="bc-product-card" data-category="eco"><div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🏺</div></div><div class="bc-product-body"><h4 class="bc-product-title" style="font-size:0.8125rem;"><a href="/conscious/product-detail">Blue Pottery Vase</a></h4><div class="bc-product-price-row"><span class="bc-product-price" style="font-size:0.9rem;">₹1,299</span><button class="bc-product-add-btn" style="width:28px;height:28px;" aria-label="Add"><i class="fa-solid fa-plus"></i></button></div></div></article>
          </div>
          <div style="text-align:center;margin-top:16px;"><a href="/conscious/shop" class="bc-btn bc-btn-outline bc-btn-sm">View Full Collection (48 items)</a></div>
        </div>
      </div>
      <div class="bc-reveal-right">
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:var(--r-xl);padding:24px;color:white;margin-bottom:20px;">
          <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Creator Stats</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="text-align:center;padding:12px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);"><div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--saffron-light);">2.4M</div><div style="font-size:0.75rem;opacity:0.6;">Followers</div></div>
            <div style="text-align:center;padding:12px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);"><div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--saffron-light);">48</div><div style="font-size:0.75rem;opacity:0.6;">Products</div></div>
            <div style="text-align:center;padding:12px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);"><div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--saffron-light);">3.2K</div><div style="font-size:0.75rem;opacity:0.6;">Sales</div></div>
            <div style="text-align:center;padding:12px;background:rgba(255,255,255,0.08);border-radius:var(--r-md);"><div style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--saffron-light);">4.9★</div><div style="font-size:0.75rem;opacity:0.6;">Rating</div></div>
          </div>
        </div>
        <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:20px;">
          <h4 style="font-weight:700;color:var(--text-primary);margin-bottom:12px;">Social Channels</h4>
          <a href="#" style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light);text-decoration:none;color:var(--text-secondary);font-size:0.875rem;"><i class="fa-brands fa-instagram" style="color:#E4405F;font-size:1.125rem;width:20px;"></i> @priya_creates · 2.4M</a>
          <a href="#" style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light);text-decoration:none;color:var(--text-secondary);font-size:0.875rem;"><i class="fa-brands fa-youtube" style="color:#FF0000;font-size:1.125rem;width:20px;"></i> Priya Creates · 890K</a>
          <a href="#" style="display:flex;align-items:center;gap:10px;padding:10px 0;text-decoration:none;color:var(--text-secondary);font-size:0.875rem;"><i class="fa-brands fa-x-twitter" style="color:#000;font-size:1.125rem;width:20px;"></i> @priya_creates · 340K</a>
        </div>
      </div>
    </div>
  </div>
</section>
</template>

<!-- Local placeholder data: registration data can hydrate these data-* fields in a future frontend integration. -->
<section class="influencer-profile" aria-label="Influencer profile" data-profile-image="assets/images/user.png" data-full-name="Priya Nair" data-brand-name="Priya Creates" data-about="I create grounded, joyful stories around slower living, responsible design and the people who make India beautiful." data-category="Sustainable Living" data-city="Bengaluru, India" data-availability="Freelance" data-portfolio="uploaded-images">
  <div class="ip-cover">
    <div class="container ip-hero-inner">
      <img class="ip-avatar" src="assets/images/user.png" alt="Priya Nair" data-profile-field="profileImage" />
      <div class="ip-identity">
        <div class="ip-eyebrow"><i class="fa-solid fa-sparkles" aria-hidden="true"></i> Conscious creator</div>
        <h1 class="ip-name" data-profile-field="fullName">Priya Nair</h1>
        <p class="ip-brand" data-profile-field="brandName">Priya Creates</p>
        <div class="ip-hero-meta">
          <span class="ip-location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span data-profile-field="city">Bengaluru, India</span></span>
          <span class="ip-pill"><i class="fa-solid fa-leaf" aria-hidden="true"></i><span data-profile-field="category">Sustainable Living</span></span>
          <span class="ip-pill ip-pill--availability"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span data-profile-field="availability">Freelance</span></span>
        </div>
      </div>
      <div class="ip-hero-actions">
        <button type="button" class="bc-btn bc-btn-primary ip-contact" data-bs-toggle="modal" data-bs-target="#contactInfluencerModal"><i class="fa-regular fa-message" aria-hidden="true"></i> Hire Influencer</button>
        <div class="ip-connect">Connect with me<div class="ip-hero-socials"><a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Creates on Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a><a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Creates on YouTube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a><a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Nair on LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a></div></div>
      </div>
    </div>
  </div>

  <div class="container ip-main">
    <div class="ip-layout">
      <div class="ip-stack">
        <article class="ip-card" aria-labelledby="about-title">
          <div class="ip-card-header"><h2 class="ip-title" id="about-title">A little about me</h2><span class="ip-kicker">Creator note</span></div>
          <p class="ip-about" data-profile-field="about">I create grounded, joyful stories around slower living, responsible design and the people who make India beautiful. From mindful home rituals to behind-the-scenes conversations with artisan-led brands, my work makes conscious choices feel practical, warm and full of character.</p>
        </article>

        <article class="ip-card" aria-labelledby="portfolio-title" data-portfolio-type="uploaded-images">
          <div class="ip-card-header"><h2 class="ip-title" id="portfolio-title">Selected portfolio</h2><span class="ip-kicker">Recent work</span></div>
          <div class="ip-portfolio-grid" aria-label="Portfolio images" data-portfolio-view="uploads">
            <a class="ip-portfolio-item" href="https://example.com/priya-creates/slow-home" target="_blank" rel="noopener noreferrer" aria-label="View Priya's slow home story"><img src="assets/images/portfolio-slow-home.png" alt="Artisan-made terracotta vase and handwoven home details" /><span class="ip-portfolio-caption">Slow home, considered details</span></a>
            <a class="ip-portfolio-item" href="https://example.com/priya-creates/artisan-stories" target="_blank" rel="noopener noreferrer" aria-label="View Priya's artisan collaboration"><img src="assets/images/portfolio-artisan-stories.png" alt="Naturally dyed handloom textiles and hand-crafted ceramics" /><span class="ip-portfolio-caption">Artisan stories</span></a>
            <a class="ip-portfolio-item" href="https://example.com/priya-creates/mindful-styling" target="_blank" rel="noopener noreferrer" aria-label="View Priya's mindful styling story"><img src="assets/images/portfolio-mindful-styling.png" alt="Zero-waste picnic styled with handmade and reusable objects" /><span class="ip-portfolio-caption">Mindful styling</span></a>
            <a class="ip-portfolio-item" href="https://example.com/priya-creates/slow-home" target="_blank" rel="noopener noreferrer" aria-label="View Priya's home styling portfolio"><img src="assets/images/portfolio-slow-home.png" alt="Warm artisan-made home styling details" /><span class="ip-portfolio-caption">Warm spaces</span></a>
          </div>
          <div class="ip-portfolio-actions" data-portfolio-view="uploads">
            <a class="bc-btn bc-btn-primary bc-btn-sm" href="https://example.com/priya-creates" target="_blank" rel="noopener noreferrer">View Portfolio <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
            <a class="bc-btn bc-btn-outline bc-btn-sm" href="https://example.com/priya-creates/portfolio" target="_blank" rel="noopener noreferrer">Visit Portfolio Website <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
          </div>
          <!-- Future binding: show this actual URL variant and hide [data-portfolio-view="uploads"] when a portfolio URL is supplied instead of uploads. -->
          <div class="ip-portfolio-url d-none" data-portfolio-view="url">
            <div><strong>Explore Priya's full portfolio</strong><p>Selected collaborations and creator work.</p></div>
            <a class="bc-btn bc-btn-outline bc-btn-sm" href="https://example.com/priya-creates/portfolio" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View portfolio</a>
          </div>
        </article>

        <article class="ip-card" aria-labelledby="categories-title">
          <div class="ip-card-header"><h2 class="ip-title" id="categories-title">Categories / Niche</h2></div>
          <div class="ip-tag-list"><span class="ip-tag"><i class="fa-solid fa-leaf" aria-hidden="true"></i> Sustainable Living</span><span class="ip-tag">DIY Crafts</span><span class="ip-tag">Lifestyle</span><span class="ip-tag">Home Decor</span></div>
        </article>

        <article class="ip-card ip-availability" aria-labelledby="availability-title">
          <div class="ip-card-header"><h2 class="ip-title" id="availability-title">Availability</h2></div>
          <div class="ip-availability-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Freelance</span></div>
        </article>
      </div>

      <aside class="ip-sidebar" aria-label="Creator details">
        <section class="ip-card ip-side-card ip-stats" aria-labelledby="stats-title">
          <div class="ip-card-header"><h2 class="ip-title" id="stats-title">Creator Stats</h2></div>
          <div class="ip-stat-grid">
            <div class="ip-stat"><strong>2.4M</strong><span>Followers</span></div>
            <div class="ip-stat"><strong>48</strong><span>Collaborations</span></div>
            <div class="ip-stat"><strong>3.2K</strong><span>Reels</span></div>
            <div class="ip-stat"><strong>4.9★</strong><span>Rating</span></div>
          </div>
        </section>
        <section class="ip-card ip-side-card" aria-labelledby="social-title" data-social-links="instagram,youtube,linkedin">
          <div class="ip-card-header"><h2 class="ip-title" id="social-title">Social channels</h2><span class="ip-kicker">Find me</span></div>
          <div class="ip-social-list">
            <a class="ip-social" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Creates on Instagram"><span class="ip-social-icon instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></span><span class="ip-social-text"><span class="ip-social-platform">Instagram</span><span class="ip-social-handle">@priya_creates</span></span><i class="fa-solid fa-arrow-up-right-from-square ip-social-arrow" aria-hidden="true"></i></a>
            <a class="ip-social" href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Creates on YouTube"><span class="ip-social-icon youtube"><i class="fa-brands fa-youtube" aria-hidden="true"></i></span><span class="ip-social-text"><span class="ip-social-platform">YouTube</span><span class="ip-social-handle">Priya Creates</span></span><i class="fa-solid fa-arrow-up-right-from-square ip-social-arrow" aria-hidden="true"></i></a>
            <a class="ip-social" href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="Priya Nair on LinkedIn"><span class="ip-social-icon linkedin"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></span><span class="ip-social-text"><span class="ip-social-platform">LinkedIn</span><span class="ip-social-handle">Priya Nair</span></span><i class="fa-solid fa-arrow-up-right-from-square ip-social-arrow" aria-hidden="true"></i></a>
          </div>
        </section>

      </aside>
    </div>
  </div>
</section>

<!-- Frontend-only contact form. It intentionally has no endpoint or persistence. -->
<div class="modal fade ip-modal" id="contactInfluencerModal" tabindex="-1" aria-labelledby="contactInfluencerModalTitle" aria-describedby="contactInfluencerModalDescription" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header"><div><div class="ip-kicker mb-1">Start a conversation</div><h2 class="modal-title" id="contactInfluencerModalTitle">Contact Priya</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <p id="contactInfluencerModalDescription" class="text-secondary small mb-4">Share a few details about your project. This is a preview form and does not send a message.</p>
        <div class="alert alert-success ip-modal-success" id="ip-contact-feedback" role="status">Thanks! This preview form has been completed, but no message was sent.</div>
        <form id="ip-contact-form" novalidate>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="contact-name">Full Name <span aria-hidden="true">*</span></label><input id="contact-name" class="form-control" type="text" required autocomplete="name" /><div class="invalid-feedback">Please enter your full name.</div></div>
            <div class="col-md-6"><label class="form-label" for="contact-number">Contact Number <span aria-hidden="true">*</span></label><input id="contact-number" class="form-control" type="tel" required autocomplete="tel" /><div class="invalid-feedback">Please enter a contact number.</div></div>
            <div class="col-md-6"><label class="form-label" for="contact-email">Email Address <span aria-hidden="true">*</span></label><input id="contact-email" class="form-control" type="email" required autocomplete="email" /><div class="invalid-feedback">Please enter a valid email address.</div></div>
            <div class="col-md-6"><label class="form-label" for="contact-service">Select Package <span aria-hidden="true">*</span></label><select id="contact-service" class="form-select" required><option value="" selected disabled>Select a package</option><option>Basic</option><option>Gold</option><option>Platinum</option></select><div class="invalid-feedback">Please choose a package.</div></div>
            <div class="col-12"><label class="form-label" for="contact-query">Message</label><textarea id="contact-query" class="form-control" rows="4" placeholder="Tell Priya a little about your idea (optional)"></textarea></div>
          </div>
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mt-4"><span class="form-text">Fields marked * are required.</span><button class="bc-btn bc-btn-primary" type="submit"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i> Submit</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

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
  // Preview-only contact behaviour: validates locally and never transmits or persists data.
  (function () {
    var contactForm = document.getElementById('ip-contact-form');
    var feedback = document.getElementById('ip-contact-feedback');
    if (!contactForm) return;
    contactForm.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      contactForm.classList.add('was-validated');
      if (contactForm.checkValidity()) {
        feedback.textContent = 'This is a design preview. No message was sent.';
        feedback.classList.add('show');
      }
    });
    document.getElementById('contactInfluencerModal').addEventListener('hidden.bs.modal', function () {
      contactForm.classList.remove('was-validated');
      feedback.classList.remove('show');
      feedback.textContent = 'Thanks! This preview form has been completed, but no message was sent.';
    });
  }());
</script>
<script src="portals/modules/influencer/assets/js/public-profile.js?v=20261017-3" defer></script>
</body>
</html>
