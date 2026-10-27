<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Shop the Conscious India Community Marketplace — impact-driven products from NGOs, Self Help Groups, Women Entrepreneurs & Institutions across India." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Community Marketplace | Conscious India" />
  <meta property="og:description" content="Empowering communities, supporting livelihoods and celebrating impact-driven products from across India." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Community Marketplace — Conscious India | NGOs, SHGs, Women Entrepreneurs & Institutions</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ============ COMMUNITY MARKETPLACE PAGE ============ */

#shop-by-community,
#browse-community-products,
#ngo-products,
#shg-products,
#women-products,
#institutions-products{ scroll-margin-top:118px; }

/* --- Hero --- */
.bc-comm-hero{
  position:relative;
  overflow:hidden;
  padding:60px 0 52px;
  background:linear-gradient(120deg,var(--forest) 0%,var(--green-dark) 45%,var(--terracotta) 130%);
}
.bc-comm-hero::before{
  content:'';
  position:absolute; inset:0;
  background:
    radial-gradient(circle at 12% 25%, rgba(245,239,230,0.14) 0, transparent 42%),
    radial-gradient(circle at 88% 80%, rgba(138,154,91,0.45) 0, transparent 50%);
  pointer-events:none;
}
.bc-comm-hero-inner{
  position:relative; z-index:1;
  display:grid; grid-template-columns:1.15fr 0.85fr; align-items:center; gap:36px;
}
.bc-comm-hero-badge{
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 16px; border-radius:var(--r-full);
  background:rgba(245,239,230,0.16); border:1px solid rgba(245,239,230,0.35);
  color:var(--sand-beige); font-size:0.75rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;
  margin-bottom:18px;
}
.bc-comm-hero-title{
  font-family:var(--font-display); font-weight:800;
  font-size:clamp(1.9rem,4vw,2.85rem); line-height:1.16; color:#fff; margin-bottom:16px;
}
.bc-comm-hero-desc{
  font-size:1.0625rem; line-height:1.7; color:rgba(255,252,247,0.88);
  max-width:520px; margin-bottom:26px;
}
.bc-comm-hero-ctas{ display:flex; gap:14px; flex-wrap:wrap; }
.bc-comm-hero-visual{ position:relative; height:270px; }
.bc-comm-hero-orb{ position:absolute; border-radius:50%; background:rgba(245,239,230,0.10); border:1px solid rgba(245,239,230,0.22); }
.bc-comm-hero-orb.o1{ width:210px; height:210px; top:6%; left:18%; }
.bc-comm-hero-orb.o2{ width:120px; height:120px; bottom:2%; right:10%; background:rgba(138,154,91,0.28); }
.bc-comm-hero-chip{
  position:absolute; display:flex; align-items:center; gap:10px;
  background:rgba(255,252,247,0.97); color:var(--forest);
  padding:11px 18px; border-radius:var(--r-lg); box-shadow:var(--shadow-lg);
  font-weight:700; font-size:0.8438rem; white-space:nowrap;
  animation:bcCommFloat 5s ease-in-out infinite;
}
.bc-comm-hero-chip i{ font-size:1rem; }
.bc-comm-hero-chip.c1{ top:4%; left:4%; animation-delay:0s; }
.bc-comm-hero-chip.c2{ top:44%; right:0; color:var(--saffron-dark); animation-delay:-1.7s; }
.bc-comm-hero-chip.c3{ bottom:6%; left:16%; color:var(--gold-dark); animation-delay:-3.3s; }
@keyframes bcCommFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-10px); } }

@media (max-width:900px){
  .bc-comm-hero{ padding:48px 0 40px; }
  .bc-comm-hero-inner{ grid-template-columns:1fr; text-align:center; }
  .bc-comm-hero-desc{ margin-left:auto; margin-right:auto; }
  .bc-comm-hero-ctas{ justify-content:center; }
  .bc-comm-hero-visual{ display:none; }
}
@media (max-width:480px){
  .bc-comm-hero-ctas{ flex-direction:column; align-items:stretch; }
  .bc-comm-hero-ctas .bc-btn{ width:100%; }
}

/* --- Shop by Community cards --- */
.bc-comm-cat-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:22px; }
.bc-comm-cat-card{
  position:relative; overflow:hidden;
  background:var(--warm-white);
  border:1.5px solid var(--border-warm);
  border-radius:var(--r-xl);
  padding:26px 24px 24px;
  box-shadow:var(--shadow-sm);
  transition:all var(--t);
  display:flex; flex-direction:column;
  text-align:left;
}
.bc-comm-cat-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); border-color:transparent; }
.bc-comm-cat-card.tint{ background:linear-gradient(160deg,var(--warm-white) 55%,var(--saffron-pale) 150%); }
.bc-comm-cat-icon{
  width:54px; height:54px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:1.3125rem; color:#fff; margin-bottom:16px; flex-shrink:0;
}
.bc-comm-cat-icon.forest{ background:linear-gradient(135deg,var(--forest),var(--green-dark)); }
.bc-comm-cat-icon.terracotta{ background:linear-gradient(135deg,var(--terracotta),var(--saffron-dark)); }
.bc-comm-cat-name{ font-family:var(--font-display); font-weight:700; font-size:1.0625rem; color:var(--text-primary); margin-bottom:8px; }
.bc-comm-cat-desc{ font-size:0.8438rem; color:var(--text-muted); line-height:1.55; margin-bottom:18px; flex:1; }
.bc-comm-cat-card .bc-btn{ align-self:flex-start; position:relative; z-index:1; }
.bc-comm-cat-leaf{
  position:absolute; bottom:8px; right:14px; font-size:2.1rem;
  opacity:0.14; color:var(--olive); pointer-events:none; transform:rotate(10deg);
}

@media (max-width:900px){ .bc-comm-cat-grid{ grid-template-columns:repeat(2,1fr); } }
@media (max-width:560px){ .bc-comm-cat-grid{ grid-template-columns:1fr; } }

/* --- Browse header --- */
.bc-comm-browse-header{
  display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap;
  margin-bottom:40px;
}
.bc-comm-browse-filters{ display:flex; gap:12px; flex-wrap:wrap; }
.bc-comm-select{
  border:1.5px solid var(--border-warm); border-radius:var(--r-full);
  background:var(--bg-primary); padding:10px 18px; font-size:0.8125rem; font-weight:600;
  color:var(--text-primary); cursor:pointer;
}

/* --- Product section header --- */
.bc-comm-product-section{ margin-bottom:52px; }
.bc-comm-product-section:last-child{ margin-bottom:0; }
.bc-comm-prod-header{
  display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap;
}
.bc-comm-prod-heading{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.bc-comm-prod-icon{
  width:38px; height:38px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; color:#fff; font-size:0.9375rem;
}
.bc-comm-prod-icon.forest{ background:linear-gradient(135deg,var(--forest),var(--green-dark)); }
.bc-comm-prod-icon.terracotta{ background:linear-gradient(135deg,var(--terracotta),var(--saffron-dark)); }
.bc-comm-prod-title{ font-family:var(--font-display); font-weight:700; font-size:1.25rem; margin:0; }
.bc-comm-prod-title.forest{ color:var(--forest); }
.bc-comm-prod-title.terracotta{ color:var(--terracotta); }
.bc-comm-prod-sub{ font-size:0.8125rem; color:var(--text-muted); margin:2px 0 0; }
.bc-comm-viewall{ font-size:0.875rem; font-weight:700; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; color:var(--text-primary); }
.bc-comm-viewall i{ font-size:0.75rem; transition:transform var(--t-fast); }
.bc-comm-viewall:hover i{ transform:translateX(3px); }

/* --- Product grid --- */
.bc-comm-product-grid{ display:grid; grid-template-columns:repeat(6,1fr); gap:18px; }
@media (max-width:1100px){ .bc-comm-product-grid{ grid-template-columns:repeat(3,1fr); } }
@media (max-width:640px){ .bc-comm-product-grid{ grid-template-columns:repeat(2,1fr); gap:12px; } }
@media (max-width:380px){ .bc-comm-product-grid{ grid-template-columns:1fr; } }

/* --- Product card (shared across all 4 community sections) --- */
.bc-comm-product-card{
  background:var(--bg-primary); border:1.5px solid var(--border-light); border-radius:var(--r-lg);
  overflow:hidden; transition:all var(--t); display:flex; flex-direction:column;
}
.bc-comm-product-card:hover{ transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:transparent; }
.bc-comm-product-img-wrap{ position:relative; aspect-ratio:1; overflow:hidden; }
.bc-comm-product-img{ width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:2.5rem; transition:transform var(--t-slow); }
.bc-comm-product-card:hover .bc-comm-product-img{ transform:scale(1.06); }
.bc-comm-product-badge{
  position:absolute; top:10px; left:10px; padding:4px 10px; border-radius:var(--r-full);
  font-size:0.625rem; font-weight:700; letter-spacing:0.03em; color:#fff;
}
.bc-comm-product-badge.forest{ background:var(--forest); }
.bc-comm-product-badge.terracotta{ background:var(--terracotta); }
.bc-comm-product-wish{
  position:absolute; top:8px; right:8px; width:30px; height:30px; border-radius:50%;
  background:rgba(255,255,255,0.94); border:none; display:flex; align-items:center; justify-content:center;
  color:var(--text-secondary); box-shadow:var(--shadow-sm); cursor:pointer; transition:all var(--t-fast); font-size:0.8125rem;
}
.bc-comm-product-wish:hover{ background:var(--terracotta); color:#fff; }
.bc-comm-product-wish.wishlist-active{ background:var(--terracotta); color:#fff; }
.bc-comm-product-body{ padding:13px 14px 14px; display:flex; flex-direction:column; gap:2px; flex:1; }
.bc-comm-product-name{ font-family:var(--font-display); font-weight:600; font-size:0.875rem; color:var(--text-primary); line-height:1.3; margin:0; }
.bc-comm-product-by{ font-size:0.75rem; color:var(--text-muted); margin:2px 0 0; }
.bc-comm-product-price{ font-family:var(--font-display); font-weight:700; font-size:1rem; color:var(--text-primary); margin:6px 0 10px; }
.bc-comm-product-cta{
  margin-top:auto; display:flex; align-items:center; justify-content:center; gap:6px;
  padding:9px 12px; border-radius:var(--r-sm); border:1.5px solid var(--forest); color:var(--forest);
  font-size:0.75rem; font-weight:700; text-decoration:none; transition:all var(--t-fast);
}
.bc-comm-product-cta:hover{ background:var(--forest); color:#fff; }
.bc-comm-product-cta.terracotta{ border-color:var(--terracotta); color:var(--terracotta); }
.bc-comm-product-cta.terracotta:hover{ background:var(--terracotta); color:#fff; }

@media (max-width:640px){
  .bc-comm-product-name{ font-size:0.8125rem; }
  .bc-comm-product-price{ font-size:0.9375rem; }
}

/* --- Trust bar --- */
.bc-comm-trust-bar{
  display:grid; grid-template-columns:repeat(4,1fr);
  background:var(--warm-white); border:1.5px solid var(--border-warm); border-radius:var(--r-xl);
  box-shadow:var(--shadow-sm); overflow:hidden;
}
.bc-comm-trust-item{ display:flex; align-items:center; gap:14px; padding:26px 22px; border-right:1px solid var(--border-warm); }
.bc-comm-trust-item:last-child{ border-right:none; }
.bc-comm-trust-icon{
  width:46px; height:46px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:1.125rem; color:#fff;
  background:linear-gradient(135deg,var(--forest),var(--olive));
}
.bc-comm-trust-title{ font-family:var(--font-display); font-weight:700; font-size:0.9375rem; margin:0 0 3px; }
.bc-comm-trust-desc{ font-size:0.78125rem; color:var(--text-muted); margin:0; line-height:1.4; }

@media (max-width:900px){
  .bc-comm-trust-bar{ grid-template-columns:repeat(2,1fr); }
  .bc-comm-trust-item{ border-bottom:1px solid var(--border-warm); }
  .bc-comm-trust-item:nth-child(2n){ border-right:none; }
  .bc-comm-trust-item:nth-last-child(-n+2){ border-bottom:none; }
}
@media (max-width:560px){
  .bc-comm-trust-bar{ grid-template-columns:1fr; }
  .bc-comm-trust-item{ border-right:none; border-bottom:1px solid var(--border-warm); }
  .bc-comm-trust-item:last-child{ border-bottom:none; }
}

@media (max-width:768px){
  .bc-comm-browse-header{ align-items:flex-start; }
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

<div class="bc-breadcrumb-wrap" style="background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Community Marketplace</li>
      </ol>
    </nav>
  </div>
</div>

<!-- ============ COMMUNITY MARKETPLACE HERO ============ -->
<section class="bc-comm-hero">
  <div class="container">
    <div class="bc-comm-hero-inner">
      <div class="bc-comm-hero-text bc-reveal">
        <div class="bc-comm-hero-badge"><i class="fa-solid fa-people-group"></i> Community Marketplace</div>
        <h1 class="bc-comm-hero-title">Community Marketplace</h1>
        <p class="bc-comm-hero-desc">Empowering communities, supporting livelihoods and celebrating impact-driven products from across India.</p>
        <div class="bc-comm-hero-ctas">
          <a href="#browse-community-products" class="bc-btn bc-btn-primary">Browse Products</a>
          <a href="#shop-by-community" class="bc-btn bc-btn-outline-white">Shop by Community</a>
        </div>
      </div>
      <div class="bc-comm-hero-visual bc-reveal-right" aria-hidden="true">
        <div class="bc-comm-hero-orb o1"></div>
        <div class="bc-comm-hero-orb o2"></div>
        <div class="bc-comm-hero-chip c1"><i class="fa-solid fa-hand-holding-heart"></i> 200+ NGOs Verified</div>
        <div class="bc-comm-hero-chip c2"><i class="fa-solid fa-people-group"></i> 1,500+ SHG Artisans</div>
        <div class="bc-comm-hero-chip c3"><i class="fa-solid fa-seedling"></i> Real Social Impact</div>
      </div>
    </div>
  </div>
</section>

<!-- ============ SHOP BY COMMUNITY ============ -->
<section class="section-py" id="shop-by-community" style="background:var(--bg-cream);">
  <div class="container">
    <div class="bc-section-header centered bc-reveal">
      <div class="bc-section-label">Community Marketplace</div>
      <h2 class="bc-section-title">Shop by Community</h2>
      <p class="bc-section-desc">Discover and support products made by impactful communities.</p>
    </div>

    <div class="bc-comm-cat-grid">
      <a href="#ngo-products" class="bc-comm-cat-card bc-reveal" data-community="ngo">
        <div class="bc-comm-cat-icon forest"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <h3 class="bc-comm-cat-name">NGO</h3>
        <p class="bc-comm-cat-desc">Products crafted and curated by NGOs working for social good and impact.</p>
        <span class="bc-btn bc-btn-outline bc-btn-sm">Shop Now <i class="fa-solid fa-arrow-right"></i></span>
        <i class="fa-solid fa-leaf bc-comm-cat-leaf"></i>
      </a>
      <a href="#shg-products" class="bc-comm-cat-card tint bc-reveal delay-1" data-community="shg">
        <div class="bc-comm-cat-icon terracotta"><i class="fa-solid fa-people-group"></i></div>
        <h3 class="bc-comm-cat-name">SHGs (Self Help Groups)</h3>
        <p class="bc-comm-cat-desc">Support Self Help Groups empowering communities through sustainable work.</p>
        <span class="bc-btn bc-btn-outline bc-btn-sm">Shop Now <i class="fa-solid fa-arrow-right"></i></span>
        <i class="fa-solid fa-leaf bc-comm-cat-leaf"></i>
      </a>
      <a href="#women-products" class="bc-comm-cat-card bc-reveal delay-2" data-community="women">
        <div class="bc-comm-cat-icon forest"><i class="fa-solid fa-venus"></i></div>
        <h3 class="bc-comm-cat-name">Women Entrepreneur / Army Women Entrepreneur</h3>
        <p class="bc-comm-cat-desc">Empowering women and army women entrepreneurs building a self-reliant India.</p>
        <span class="bc-btn bc-btn-outline bc-btn-sm">Shop Now <i class="fa-solid fa-arrow-right"></i></span>
        <i class="fa-solid fa-leaf bc-comm-cat-leaf"></i>
      </a>
      <a href="#institutions-products" class="bc-comm-cat-card tint bc-reveal delay-3" data-community="institutions">
        <div class="bc-comm-cat-icon terracotta"><i class="fa-solid fa-building-columns"></i></div>
        <h3 class="bc-comm-cat-name">Institutions Group</h3>
        <p class="bc-comm-cat-desc">Quality products from institutions, organizations and collective groups.</p>
        <span class="bc-btn bc-btn-outline bc-btn-sm">Shop Now <i class="fa-solid fa-arrow-right"></i></span>
        <i class="fa-solid fa-leaf bc-comm-cat-leaf"></i>
      </a>
    </div>
  </div>
</section>

<!-- ============ BROWSE PRODUCTS BY COMMUNITY ============ -->
<section class="section-py" id="browse-community-products">
  <div class="container">
    <div class="bc-comm-browse-header bc-reveal">
      <div>
        <h2 class="bc-section-title" style="margin-bottom:6px;">Browse Products by Community</h2>
        <p class="bc-section-desc" style="max-width:620px;">Handpicked products from communities creating real impact.</p>
      </div>
      <div class="bc-comm-browse-filters">
        <select class="bc-comm-select" aria-label="Filter by category">
          <option>All Categories</option>
          <option>NGO</option>
          <option>SHGs</option>
          <option>Women Entrepreneur</option>
          <option>Institutions Group</option>
        </select>
        <select class="bc-comm-select" aria-label="Sort products">
          <option>Sort by: Newest First</option>
          <option>Price: Low to High</option>
          <option>Price: High to Low</option>
          <option>Popularity</option>
        </select>
      </div>
    </div>

    <!-- ===== NGO PRODUCTS ===== -->
    <div class="bc-comm-product-section" id="ngo-products">
      <div class="bc-comm-prod-header bc-reveal">
        <div class="bc-comm-prod-heading">
          <div class="bc-comm-prod-icon forest"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <div>
            <h3 class="bc-comm-prod-title forest">NGO</h3>
            <p class="bc-comm-prod-sub">Products from NGOs making a difference.</p>
          </div>
        </div>
        <a href="/conscious/shop?community=ngo" class="bc-comm-viewall">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="bc-comm-product-grid">
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-1">👜</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Eco Jute Handbag</h4>
            <p class="bc-comm-product-by">Handcrafted by Rural NGO</p>
            <div class="bc-comm-product-price">₹899</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-2">🧺</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Handwoven Basket</h4>
            <p class="bc-comm-product-by">Crafted by NGO Artisans</p>
            <div class="bc-comm-product-price">₹749</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-3">🧼</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Organic Herbal Soap</h4>
            <p class="bc-comm-product-by">Made by Green Earth NGO</p>
            <div class="bc-comm-product-price">₹299</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-4">🪴</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Upcycled Fabric Pouch</h4>
            <p class="bc-comm-product-by">Made by Udayan Care</p>
            <div class="bc-comm-product-price">₹399</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-5">🐝</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Bee Wrap Set</h4>
            <p class="bc-comm-product-by">By Nature Protect NGO</p>
            <div class="bc-comm-product-price">₹599</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-6">📓</div>
            <span class="bc-comm-product-badge forest">NGO</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Eco Notebooks Set</h4>
            <p class="bc-comm-product-by">By Shiksha Foundation</p>
            <div class="bc-comm-product-price">₹199</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>

    <!-- ===== SHGs PRODUCTS ===== -->
    <div class="bc-comm-product-section" id="shg-products">
      <div class="bc-comm-prod-header bc-reveal">
        <div class="bc-comm-prod-heading">
          <div class="bc-comm-prod-icon terracotta"><i class="fa-solid fa-people-group"></i></div>
          <div>
            <h3 class="bc-comm-prod-title terracotta">SHGs (Self Help Groups)</h3>
            <p class="bc-comm-prod-sub">Products made by Self Help Groups.</p>
          </div>
        </div>
        <a href="/conscious/shop?community=shg" class="bc-comm-viewall">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="bc-comm-product-grid">
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-7">🫖</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Terracotta Tea Set</h4>
            <p class="bc-comm-product-by">Made by SHG Women</p>
            <div class="bc-comm-product-price">₹599</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-8">💍</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Handmade Jewelry Set</h4>
            <p class="bc-comm-product-by">By SHG Women</p>
            <div class="bc-comm-product-price">₹1,199</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-1">🥻</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Handloom Cotton Saree</h4>
            <p class="bc-comm-product-by">By SHG Weavers</p>
            <div class="bc-comm-product-price">₹1,499</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-2">🪔</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Natural Incense Sticks</h4>
            <p class="bc-comm-product-by">By SHG Group</p>
            <div class="bc-comm-product-price">₹249</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-3">🌾</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Woven Table Mat</h4>
            <p class="bc-comm-product-by">Made by SHG Artisans</p>
            <div class="bc-comm-product-price">₹399</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-4">🥫</div>
            <span class="bc-comm-product-badge terracotta">SHG</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Pickle Combo Pack</h4>
            <p class="bc-comm-product-by">By SHG Food Group</p>
            <div class="bc-comm-product-price">₹549</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>

    <!-- ===== WOMEN ENTREPRENEUR PRODUCTS ===== -->
    <div class="bc-comm-product-section" id="women-products">
      <div class="bc-comm-prod-header bc-reveal">
        <div class="bc-comm-prod-heading">
          <div class="bc-comm-prod-icon forest"><i class="fa-solid fa-venus"></i></div>
          <div>
            <h3 class="bc-comm-prod-title forest">Women Entrepreneur / Army Women Entrepreneur</h3>
            <p class="bc-comm-prod-sub">Products by women and army women entrepreneurs.</p>
          </div>
        </div>
        <a href="/conscious/shop?community=women" class="bc-comm-viewall">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="bc-comm-product-grid">
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-5">🎒</div>
            <span class="bc-comm-product-badge forest">Army Women</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Camouflage Utility Bag</h4>
            <p class="bc-comm-product-by">Made by Army Women</p>
            <div class="bc-comm-product-price">₹1,199</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-6">👛</div>
            <span class="bc-comm-product-badge forest">Women Entrepreneur</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Handcrafted Leather Wallet</h4>
            <p class="bc-comm-product-by">By Women Entrepreneur</p>
            <div class="bc-comm-product-price">₹1,299</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-7">🍪</div>
            <span class="bc-comm-product-badge forest">Women Entrepreneur</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Millet Cookies Pack</h4>
            <p class="bc-comm-product-by">By Women Entrepreneur</p>
            <div class="bc-comm-product-price">₹349</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-8">🧴</div>
            <span class="bc-comm-product-badge forest">Women Entrepreneur</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Herbal Hair Oil</h4>
            <p class="bc-comm-product-by">By Women Entrepreneur</p>
            <div class="bc-comm-product-price">₹399</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-1">🪴</div>
            <span class="bc-comm-product-badge forest">Women Entrepreneur</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Hand Painted Planter</h4>
            <p class="bc-comm-product-by">By Women Entrepreneur</p>
            <div class="bc-comm-product-price">₹599</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-2">💎</div>
            <span class="bc-comm-product-badge forest">Army Women</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Recycled Silver Earrings</h4>
            <p class="bc-comm-product-by">By Army Women</p>
            <div class="bc-comm-product-price">₹749</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>

    <!-- ===== INSTITUTIONS GROUP PRODUCTS ===== -->
    <div class="bc-comm-product-section" id="institutions-products">
      <div class="bc-comm-prod-header bc-reveal">
        <div class="bc-comm-prod-heading">
          <div class="bc-comm-prod-icon terracotta"><i class="fa-solid fa-building-columns"></i></div>
          <div>
            <h3 class="bc-comm-prod-title terracotta">Institutions Group</h3>
            <p class="bc-comm-prod-sub">Products from institutions and organizations.</p>
          </div>
        </div>
        <a href="/conscious/shop?community=institutions" class="bc-comm-viewall">View All <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="bc-comm-product-grid">
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-3">📔</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Eco Friendly Notebook</h4>
            <p class="bc-comm-product-by">By Youth Welfare Group</p>
            <div class="bc-comm-product-price">₹199</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-4">👕</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Organic Cotton T-shirt</h4>
            <p class="bc-comm-product-by">By Institute Group</p>
            <div class="bc-comm-product-price">₹499</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-5">🥤</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Bamboo Water Bottle</h4>
            <p class="bc-comm-product-by">By Green Planet Org</p>
            <div class="bc-comm-product-price">₹699</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-6">🕯️</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Soy Wax Candle</h4>
            <p class="bc-comm-product-by">By Wellness Institute</p>
            <div class="bc-comm-product-price">₹349</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-1">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-7">💡</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Solar Study Lamp</h4>
            <p class="bc-comm-product-by">By Education Trust</p>
            <div class="bc-comm-product-price">₹899</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
        <article class="bc-comm-product-card bc-reveal delay-2">
          <div class="bc-comm-product-img-wrap"><div class="bc-comm-product-img bc-prod-bg-8">✏️</div>
            <span class="bc-comm-product-badge terracotta">Institutions Group</span>
            <button class="bc-comm-product-wish bc-product-action-btn wishlist" aria-label="Add to wishlist"><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="bc-comm-product-body">
            <h4 class="bc-comm-product-name">Plantable Seed Pencils</h4>
            <p class="bc-comm-product-by">By Eco Club Group</p>
            <div class="bc-comm-product-price">₹149</div>
            <a href="/conscious/product-detail" class="bc-comm-product-cta terracotta">View Product <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ============ COMMUNITY IMPACT / TRUST SECTION ============ -->
<section class="section-py-sm" style="background:var(--bg-cream);">
  <div class="container">
    <div class="bc-comm-trust-bar bc-reveal">
      <div class="bc-comm-trust-item">
        <div class="bc-comm-trust-icon"><i class="fa-solid fa-shield-heart"></i></div>
        <div>
          <h4 class="bc-comm-trust-title">Verified Communities</h4>
          <p class="bc-comm-trust-desc">All communities are verified and trusted.</p>
        </div>
      </div>
      <div class="bc-comm-trust-item">
        <div class="bc-comm-trust-icon"><i class="fa-solid fa-leaf"></i></div>
        <div>
          <h4 class="bc-comm-trust-title">Sustainable Impact</h4>
          <p class="bc-comm-trust-desc">Every purchase creates real social impact.</p>
        </div>
      </div>
      <div class="bc-comm-trust-item">
        <div class="bc-comm-trust-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <div>
          <h4 class="bc-comm-trust-title">Empowering Livelihoods</h4>
          <p class="bc-comm-trust-desc">Supporting communities and local economies.</p>
        </div>
      </div>
      <div class="bc-comm-trust-item">
        <div class="bc-comm-trust-icon"><i class="fa-solid fa-award"></i></div>
        <div>
          <h4 class="bc-comm-trust-title">Quality Assured</h4>
          <p class="bc-comm-trust-desc">Handcrafted with love and quality you can trust.</p>
        </div>
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