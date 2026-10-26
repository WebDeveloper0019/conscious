<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Shop sustainable, natural and artisan-made products from independent Indian makers. Thoughtful materials, mindful packaging and beautiful everyday goods." />
  <meta name="keywords" content="conscious living, sustainable brands, handcrafted, indian crafts, artisan marketplace, creator economy, conscious commerce, corporate gifting, Conscious India" />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Conscious India — Better Things, Gently Made" />
  <meta property="og:description" content="Discover sustainable, natural and artisan-made goods for a more thoughtful everyday." />
  <meta property="og:type" content="website" />
  <title>Conscious India — Sustainable, Natural &amp; Artisan-Made Goods</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <!-- Conscious India Design System -->
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
  <link rel="stylesheet" href="assets/css/home-sustainable.css?v=5" />
  <link rel="stylesheet" href="assets/css/home-hero.css?v=20261025-1" />
  <link rel="stylesheet" href="assets/css/home-maker.css?v=20261026-1" />
  <link rel="stylesheet" href="assets/css/home-editorial-sections.css?v=20261026-1" />
  <style>
    /* Homepage preview intentionally mirrors the first three live-experience cards on events.html. */
    #bc-events-grid > .bc-event-card:nth-child(n+4){display:none}
    @media (max-width: 575px){#bc-events-grid{grid-template-columns:1fr}.bc-event-feat-actions{align-items:stretch}.bc-event-feat-actions .bc-btn{justify-content:center}}
  </style>

  <!-- ── Stories That Matter — Section CSS (inline, server-safe) ── -->
  <style>
    
    .bc-stm-section { position: relative; overflow: hidden; }
    .bc-stm-glow { position: absolute; border-radius: 50%; pointer-events: none; }
    .bc-stm-glow-l { width: 380px; height: 380px; top: -80px; left: -100px; background: radial-gradient(circle, rgba(255,153,51,.14) 0%, transparent 65%); }
    .bc-stm-glow-r { width: 280px; height: 280px; bottom: -60px; right: 4%; background: radial-gradient(circle, rgba(74,205,171,.1) 0%, transparent 65%); }
    .bc-stm-inner { display: grid; grid-template-columns: 42% 58%; gap: 60px; align-items: center; }
    .bc-stm-left { position: relative; }
    .bc-stm-deco-quote { position: absolute; top: -28px; left: -8px; font-size: 11rem; font-family: 'Georgia','Times New Roman',serif; color: rgba(255,153,51,.07); line-height: 1; pointer-events: none; user-select: none; z-index: 0; }
    .bc-stm-left > * { position: relative; z-index: 1; }
    .bc-stm-label { color: #C88422 !important; }
    .bc-stm-title { color: #ffffff !important; font-size: 2.2rem !important; line-height: 1.2 !important; margin-bottom: 14px; }
    .bc-stm-desc { color: rgba(255,255,255,.68); font-size: .9375rem; line-height: 1.72; margin-bottom: 26px; }
    .bc-stm-type-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 30px; }
    .bc-stm-pill { font-size: .6875rem; font-weight: 600; padding: 5px 12px; border-radius: 100px; letter-spacing: .02em; }
    .bc-stm-pill--artisan  { background: rgba(255,153,51,.14);  color: #C88422; border: 1px solid rgba(255,153,51,.28); }
    .bc-stm-pill--brand    { background: rgba(74,205,171,.12);  color: #5FDBBF; border: 1px solid rgba(74,205,171,.28); }
    .bc-stm-pill--woman    { background: rgba(255,107,157,.12); color: #FF8FC0; border: 1px solid rgba(255,107,157,.28); }
    .bc-stm-pill--circular { background: rgba(142,195,62,.12);  color: #A6D96A; border: 1px solid rgba(142,195,62,.28); }
    .bc-stm-cta { margin-top: 0; }
    .bc-stm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .bc-stm-card { position: relative; background: rgba(255,255,255,.05); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,.09); border-radius: 16px; padding: 18px 18px 16px; display: flex; flex-direction: column; gap: 11px; overflow: hidden; transition: transform .25s ease, background .25s ease, border-color .25s ease, box-shadow .25s ease; cursor: pointer; text-decoration: none; }
    .bc-stm-card:hover { transform: translateY(-5px); background: rgba(255,255,255,.09); }
    .bc-stm-card--artisan:hover  { box-shadow: 0 16px 40px rgba(255,153,51,.18);  border-color: rgba(255,153,51,.3); }
    .bc-stm-card--brand:hover    { box-shadow: 0 16px 40px rgba(74,205,171,.15);  border-color: rgba(74,205,171,.3); }
    .bc-stm-card--woman:hover    { box-shadow: 0 16px 40px rgba(255,107,157,.15); border-color: rgba(255,107,157,.3); }
    .bc-stm-card--circular:hover { box-shadow: 0 16px 40px rgba(126,211,33,.12);  border-color: rgba(126,211,33,.3); }
    .bc-stm-card-bar { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 16px 16px 0 0; }
    .bc-stm-card--artisan  .bc-stm-card-bar { background: linear-gradient(90deg, #A63D24, #C88422); }
    .bc-stm-card--brand    .bc-stm-card-bar { background: linear-gradient(90deg, #4ACDAB, #5FE5C8); }
    .bc-stm-card--woman    .bc-stm-card-bar { background: linear-gradient(90deg, #FF6B9D, #FF8FC0); }
    .bc-stm-card--circular .bc-stm-card-bar { background: linear-gradient(90deg, #7ED321, #A5DE4D); }
    .bc-stm-card-head { display: flex; align-items: flex-start; gap: 12px; padding-top: 8px; }
    .bc-stm-avatar { width: 48px; height: 48px; min-width: 48px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,.18); flex-shrink: 0; }
    .bc-stm-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .bc-stm-av--artisan  { background: linear-gradient(135deg, #E0884B 0%, #7A2200 100%); border-color: rgba(255,153,51,.45); }
    .bc-stm-av--brand    { background: linear-gradient(135deg, #4ACDAB 0%, #0B7A65 100%); border-color: rgba(74,205,171,.45); }
    .bc-stm-av--woman    { background: linear-gradient(135deg, #FF6B9D 0%, #961542 100%); border-color: rgba(255,107,157,.45); }
    .bc-stm-av--circular { background: linear-gradient(135deg, #7ED321 0%, #38700A 100%); border-color: rgba(126,211,33,.45); }
    .bc-stm-card-meta { flex: 1; min-width: 0; }
    .bc-stm-type-tag { display: inline-flex; font-size: .5rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; padding: 2px 7px; border-radius: 100px; margin-bottom: 5px; }
    .bc-stm-tag--artisan  { background: rgba(255,153,51,.18);  color: #C88422; border: 1px solid rgba(255,153,51,.32); }
    .bc-stm-tag--brand    { background: rgba(74,205,171,.15);  color: #5FD9BE; border: 1px solid rgba(74,205,171,.32); }
    .bc-stm-tag--woman    { background: rgba(255,107,157,.15); color: #FF8FC0; border: 1px solid rgba(255,107,157,.32); }
    .bc-stm-tag--circular { background: rgba(126,211,33,.12);  color: #A5DE4D; border: 1px solid rgba(126,211,33,.32); }
    .bc-stm-name { font-size: .9rem; font-weight: 700; color: #fff; line-height: 1.3; margin-bottom: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bc-stm-role { font-size: .725rem; color: rgba(255,255,255,.52); font-weight: 500; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bc-stm-loc { font-size: .6375rem; color: rgba(255,255,255,.35); display: flex; align-items: center; gap: 3px; }
    .bc-stm-loc i { font-size: .475rem; }
    .bc-stm-quote { position: relative; margin: 0; padding-left: 22px; font-size: .74rem; line-height: 1.62; color: rgba(255,255,255,.62); font-style: italic; flex: 1; border-left: none; }
    .bc-stm-qmark { position: absolute; left: 0; top: -3px; font-size: 1.6rem; font-family: Georgia,serif; font-weight: 700; line-height: 1; font-style: normal; }
    .bc-stm-card--artisan  .bc-stm-qmark { color: rgba(255,153,51,.55); }
    .bc-stm-card--brand    .bc-stm-qmark { color: rgba(74,205,171,.55); }
    .bc-stm-card--woman    .bc-stm-qmark { color: rgba(255,107,157,.55); }
    .bc-stm-card--circular .bc-stm-qmark { color: rgba(126,211,33,.48); }
    .bc-stm-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 2px; }
    .bc-stm-badges { display: flex; gap: 6px; flex-wrap: wrap; }
    .bc-stm-badge { font-size: .6rem; font-weight: 600; padding: 2px 7px; border-radius: 100px; white-space: nowrap; }
    .bc-stm-badge--saffron { background: rgba(255,153,51,.18);  color: #C88422; border: 1px solid rgba(255,153,51,.3); }
    .bc-stm-badge--white   { background: rgba(255,255,255,.08); color: rgba(255,255,255,.62); border: 1px solid rgba(255,255,255,.15); }
    .bc-stm-badge--teal    { background: rgba(74,205,171,.15);  color: #5FD9BE; border: 1px solid rgba(74,205,171,.3); }
    .bc-stm-badge--rose    { background: rgba(255,107,157,.15); color: #FF8FC0; border: 1px solid rgba(255,107,157,.3); }
    .bc-stm-badge--green   { background: rgba(126,211,33,.12);  color: #A5DE4D; border: 1px solid rgba(126,211,33,.3); }
    .bc-stm-read-link { font-size: .6625rem; font-weight: 600; color: rgba(255,255,255,.36); text-decoration: none; display: flex; align-items: center; gap: 4px; white-space: nowrap; flex-shrink: 0; transition: color .2s ease; }
    .bc-stm-read-link i { font-size: .55rem; transition: transform .2s ease; }
    .bc-stm-card:hover .bc-stm-read-link { color: rgba(255,255,255,.78); }
    .bc-stm-card:hover .bc-stm-read-link i { transform: translateX(3px); }
    /* Responsive */
    @media (max-width: 1100px) { .bc-stm-inner { grid-template-columns: 40% 60%; gap: 44px; } }
    @media (max-width: 900px)  { .bc-stm-inner { grid-template-columns: 1fr; gap: 36px; } .bc-stm-deco-quote { font-size: 7.5rem; } .bc-stm-title { font-size: 1.9rem !important; } .bc-stm-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 540px)  { .bc-stm-grid { grid-template-columns: 1fr; gap: 12px; } .bc-stm-card { padding: 16px 16px 14px; } .bc-stm-title { font-size: 1.65rem !important; } }
  </style>

  <!-- Trending Products — Section-specific additions -->
  <style>
    /* ── Trending Section Background ── */
    .bc-trending-section {
      background: linear-gradient(180deg, var(--saffron-50, #FFFAF3) 0%, #ffffff 60%);
    }

    /* ── Trend Rank Badge (bottom-left of product image) ── */
    .bc-trend-rank {
      position: absolute;
      bottom: 10px;
      left: 10px;
      background: rgba(0, 0, 80, 0.86);
      color: #fff;
      font-family: var(--font-brand, 'Poppins', serif);
      font-size: 0.625rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      padding: 3px 8px;
      border-radius: var(--r-xs, 4px);
      z-index: 2;
      pointer-events: none;
      backdrop-filter: blur(2px);
    }

    /* ── Section header "fire" accent ── */
    .bc-trending-section .bc-section-label {
      color: var(--saffron-dark);
    }

    /* ── Trending stats strip ── */
    .bc-trending-stats {
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 28px;
    }

    .bc-trending-stat {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8125rem;
      color: var(--text-muted);
    }

    .bc-trending-stat i {
      font-size: 0.875rem;
      color: var(--saffron);
    }

    .bc-trending-stat strong {
      font-weight: 700;
      color: var(--text-secondary);
    }

    @media (max-width: 576px) {
      .bc-trending-stats { gap: 12px; }
    }
  </style>
</head>

<body>

<!-- ════════════════════════════════════════════
     PRELOADER
     ════════════════════════════════════════════ -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious India">
  <img
    src="assets/images/symbol.png"
    alt="Conscious India"
    class="bc-preloader-symbol"
    width="90"
    height="90"
    aria-hidden="true"
  />
  <div class="bc-preloader-brand">Conscious India</div>
  <div class="bc-preloader-chakra"></div>
</div>

<!-- ════════════════════════════════════════════
     SECTION 1 — TOP ANNOUNCEMENT BAR
     ════════════════════════════════════════════ -->
<!-- ════════════════════════════════════════════
     SECTION 2 — DESKTOP HEADER
     ════════════════════════════════════════════ -->
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>


<!-- ════════════════════════════════════════════
     MAIN CONTENT
     ════════════════════════════════════════════ -->
<main class="ci-home" id="main-content">
  <section class="ci-hero ci-hero-slider" id="home-hero" aria-label="Featured collections">
    <div class="ci-hero-slides" id="ci-hero-slides" aria-live="off"></div>
    <div class="ci-hero-controls" id="ci-hero-controls" hidden>
      <button type="button" id="ci-hero-prev" aria-label="Previous banner"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
      <span class="ci-hero-progress" id="ci-hero-progress" aria-hidden="true"></span>
      <div class="ci-hero-dots" id="ci-hero-dots" aria-label="Choose a banner"></div>
      <button type="button" id="ci-hero-next" aria-label="Next banner"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </div>
  </section>

  <section class="ci-maker-edit" id="meet-the-makers" aria-labelledby="ci-maker-heading">
    <div class="ci-maker-shell">
      <div class="ci-maker-intro">
        <figure class="ci-maker-portrait"><img src="assets/images/portfolio-artisan-stories.png" alt="An Indian artisan at work" loading="lazy"></figure>
        <div class="ci-maker-story"><p class="ci-maker-eyebrow">Meet the makers</p><h2 id="ci-maker-heading">Living craft traditions.</h2><p>Explore thoughtful work from makers across India. Every piece has a person, a place, and a process behind it.</p><a href="/conscious/artisans">Meet our makers <span aria-hidden="true">→</span></a></div>
      </div>
      <div class="ci-maker-products"><p class="ci-maker-eyebrow">The maker edit</p><div class="ci-maker-product-grid" id="ci-maker-products" aria-live="polite"><p class="ci-maker-loading">Loading current products…</p></div></div>
    </div>
  </section>

  <section class="ci-craft-explore" id="explore-by-craft" aria-labelledby="ci-craft-heading"><div class="ci-maker-shell"><div class="ci-craft-heading"><div><p class="ci-maker-eyebrow">Explore by craft</p><h2 id="ci-craft-heading">Different crafts. A more conscious tomorrow.</h2></div><a href="/conscious/shop">View all products <span aria-hidden="true">→</span></a></div><div class="ci-craft-grid" id="ci-craft-grid" aria-live="polite"><p class="ci-maker-loading">Loading crafts…</p></div></div></section>

  <section class="ci-maker-values" aria-label="Why shop Conscious India"><div class="ci-maker-shell"><div><i class="fa-solid fa-hands-holding-circle" aria-hidden="true"></i><h3>Meet Indian makers</h3><p>Discover the people and communities behind the work.</p></div><div><i class="fa-solid fa-leaf" aria-hidden="true"></i><h3>Choose thoughtfully</h3><p>Explore materials, processes, and product details.</p></div><div><i class="fa-solid fa-hand-sparkles" aria-hidden="true"></i><h3>Find handcrafted work</h3><p>Shop objects shaped by skill and tradition.</p></div><div><i class="fa-solid fa-location-dot" aria-hidden="true"></i><h3>Explore across India</h3><p>Find craft stories from many places and traditions.</p></div></div></section>

  <section class="ci-ed-standards" aria-labelledby="ci-standards-title">
    <div class="ci-ed-shell">
      <header class="ci-ed-section-head"><div><p class="ci-ed-kicker">The Conscious Standard</p><h2 id="ci-standards-title">Beautiful choices.<br><em>Better considered.</em></h2></div><p>We curate for material, maker and meaning—so every discovery feels good in your home and beyond it.</p></header>
      <div class="ci-ed-standard-grid">
        <article><figure><img src="assets/images/about/eco-friendly-indian-crafts.jpg" alt="Natural materials used in everyday Indian craft" loading="lazy"></figure><span>01</span><h3>Natural by choice</h3><p>Thoughtful materials with a lighter footprint and an honest story.</p></article>
        <article><figure><img src="assets/images/hero-banners/bd43235122d8b83240f843565d85918a23447502.png" alt="Indian ceramic artist painting a handmade bowl" loading="lazy"></figure><span>02</span><h3>Made with dignity</h3><p>Independent makers, skilled hands and work valued fairly.</p></article>
        <article><figure><img src="assets/images/portfolio-slow-home.png" alt="Timeless handcrafted objects in a warm home" loading="lazy"></figure><span>03</span><h3>Designed to endure</h3><p>Useful, beautiful pieces chosen to live with you for longer.</p></article>
      </div>
    </div>
  </section>

  <section class="ci-ed-living" id="marketplace" aria-labelledby="ci-living-title"><div class="ci-ed-shell">
    <header class="ci-ed-living-head"><p class="ci-ed-kicker">Curated living</p><h2 id="ci-living-title">Made for the rituals<br>of everyday life.</h2><a href="/conscious/shop">Explore all collections <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></header>
    <div class="ci-ed-living-grid">
      <a class="ci-ed-living-card ci-ed-living-card--wide" href="/conscious/home-decor"><img src="assets/images/portfolio-slow-home.png" alt="Warm handcrafted home decor" loading="lazy"><span><small>Home &amp; Living</small><strong>Objects with warmth and purpose</strong></span></a>
      <a class="ci-ed-living-card" href="/conscious/fashion"><img src="assets/images/eco-scene-2.png" alt="Naturally dyed clothing and textiles" loading="lazy"><span><small>Wear</small><strong>Natural fibre stories</strong></span></a>
      <a class="ci-ed-living-card" href="/conscious/eco"><img src="assets/images/about/eco-friendly-indian-crafts.jpg" alt="Natural self-care and daily ritual objects" loading="lazy"><span><small>Ritual</small><strong>Small, thoughtful essentials</strong></span></a>
    </div>
  </div></section>

  <section class="ci-ed-maker" aria-labelledby="ci-maker-story-title">
    <div class="ci-ed-maker-image"><img src="assets/images/about/artisan-weaving-natural-basket.jpg" alt="Indian artisan weaving a natural fibre basket" loading="lazy"></div>
    <div class="ci-ed-maker-copy"><p class="ci-ed-kicker">People behind the pieces</p><h2 id="ci-maker-story-title">Craft carries<br><em>memory forward.</em></h2><p>Every object begins with knowledge—a practiced hand, a local material and a tradition continually made relevant. Meet the people keeping those stories alive.</p><a class="ci-ed-button ci-ed-button--outline" href="/conscious/artisans">Meet our makers <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
  </section>

  <section class="ci-ed-journey" aria-labelledby="ci-journey-title"><div class="ci-ed-shell">
    <div class="ci-ed-journey-head"><div><p class="ci-ed-kicker">A better journey</p><h2 id="ci-journey-title">A better product<br>has a better journey.</h2></div><p>From raw materials to a long life in your home, we focus on choices that are kinder at every step.</p></div>
    <div class="ci-ed-journey-steps">
      <article><div class="ci-ed-step-icon ci-ed-step-icon--source" role="img" aria-label="Green leaves representing thoughtful sourcing"></div><span>01</span><h3>Source thoughtfully</h3><p>Natural, low-impact and ethically sourced materials.</p></article>
      <article><div class="ci-ed-step-icon ci-ed-step-icon--make" role="img" aria-label="Artisan hands weaving natural fibres"></div><span>02</span><h3>Make fairly</h3><p>Support skilled artisans with safe conditions and fair value.</p></article>
      <article><div class="ci-ed-step-icon ci-ed-step-icon--pack" role="img" aria-label="Low-waste kraft parcel with leaves"></div><span>03</span><h3>Pack lightly</h3><p>Low-waste, plastic-free packaging wherever possible.</p></article>
      <article><div class="ci-ed-step-icon ci-ed-step-icon--keep" role="img" aria-label="A warm home representing products made to last"></div><span>04</span><h3>Keep longer</h3><p>Well-made products designed to last, not be replaced.</p></article>
    </div>
  </div></section>

  <section class="ci-ed-words" aria-labelledby="ci-kind-words-title"><div class="ci-ed-shell ci-ed-words-grid">
    <div class="ci-ed-words-title"><p class="ci-ed-kicker">Kind words</p><h2 id="ci-kind-words-title">Thoughtful products.<br><em>Happier homes.</em></h2><a href="/conscious/stories">Read more stories <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
    <figure><img src="assets/images/home-kind-words.png" alt="A handmade ceramic cup, earthen vase and books in warm morning light" loading="lazy"></figure>
    <blockquote><i class="fa-solid fa-quote-left" aria-hidden="true"></i><p>The quality is beautiful, and you can feel the story behind each piece. It is rare to find a place that brings together such meaningful, well-made products from across India.</p><footer><span><strong>Ananya S.</strong><small>Bengaluru</small></span><span class="ci-ed-word-nav" aria-hidden="true"><b>←</b><b>→</b></span></footer></blockquote>
  </div></section>

  <section class="ci-ed-tomorrow" aria-labelledby="ci-tomorrow-title">
    <img src="assets/images/home-kinder-tomorrow.png" alt="Warm conscious home with handcrafted textiles and natural decor" loading="lazy"><div class="ci-ed-tomorrow-shade"></div>
    <div class="ci-ed-shell ci-ed-tomorrow-content"><p class="ci-ed-kicker">A kinder tomorrow</p><h2 id="ci-tomorrow-title">Buy less.<br>Love it longer.</h2><p>Choose products that are kinder to the earth, support real people and bring lasting joy to your home.</p><a class="ci-ed-button" href="/conscious/shop">Start shopping <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
  </section>

  <section class="ci-manifesto" hidden>
    <div class="ci-shell">
      <p class="ci-overline" data-ci-reveal>Not another marketplace</p>
      <h2 data-ci-lines>Everything here begins with one question:<br><em>is it kinder to the earth?</em></h2>
      <div class="ci-manifesto-grid">
        <figure class="ci-pebble p-one"><img src="assets/images/eco-scene-1.png" alt="Natural sustainable products"></figure>
        <p data-ci-reveal>We look beyond the label—into materials, making, durability, packaging, and the people receiving value. So your everyday purchases can support a more thoughtful system.</p>
        <figure class="ci-pebble p-two"><img src="assets/images/eco-scene-2.png" alt="Earth-friendly home essentials"></figure>
      </div>
    </div>
  </section>

  <section class="ci-story" id="earth-journey" hidden>
    <div class="ci-story-stage">
      <div class="ci-story-visuals">
        <figure class="ci-story-scene active"><img src="assets/images/eco-scene-2.png" alt="Products made with natural materials"></figure>
        <figure class="ci-story-scene"><img src="assets/images/eco-scene-3.png" alt="Naturally dyed handloom textiles"></figure>
        <figure class="ci-story-scene"><img src="assets/images/eco-scene-4.png" alt="Indian artisan creating sustainable goods"></figure>
        <div class="ci-story-count"><b>01</b><span>/ 03</span></div>
      </div>
      <div class="ci-story-copy">
        <article class="ci-story-panel active"><p class="ci-overline">01 · Material matters</p><h2>Born from<br>better sources.</h2><p>Natural fibres, renewable wood, recycled inputs and low-impact ingredients—chosen for how they feel today and where they go tomorrow.</p><a href="/conscious/eco">Shop earth-kind materials <b>↗</b></a></article>
        <article class="ci-story-panel"><p class="ci-overline">02 · Made slowly</p><h2>Craft over<br>convenience.</h2><p>Small-batch pieces preserve skill, reduce excess, and turn useful objects into things worth keeping.</p><a href="/conscious/handloom">Explore slow-made goods <b>↗</b></a></article>
        <article class="ci-story-panel"><p class="ci-overline">03 · People first</p><h2>Hands behind<br>every object.</h2><p>Know who made it, where it came from, and how your purchase helps independent Indian makers thrive.</p><a href="/conscious/artisans">Meet the makers <b>↗</b></a></article>
      </div>
      <div class="ci-story-dots"><button class="active" aria-label="Scene 1"></button><button aria-label="Scene 2"></button><button aria-label="Scene 3"></button></div>
    </div>
  </section>

  <section class="ci-categories" id="marketplace-retired" hidden>
    <div class="ci-shell">
      <div class="ci-section-head" data-ci-reveal><div><p class="ci-overline">Shop by world</p><h2>Bring nature<br>closer to home.</h2></div><a class="ci-link dark" href="/conscious/shop">View everything <span>↗</span></a></div>
      <div class="ci-category-grid">
        <a class="ci-cat tall" href="/conscious/home-decor"><img src="assets/images/eco-scene-2.png" alt="Natural home and living collection"><span><small>01 · Home</small><strong>Objects for slower spaces</strong><b>↗</b></span></a>
        <a class="ci-cat" href="/conscious/fashion"><img src="assets/images/eco-scene-3.png" alt="Natural fibre fashion collection"><span><small>02 · Wear</small><strong>Natural fibre stories</strong><b>↗</b></span></a>
        <a class="ci-cat" href="/conscious/eco"><img src="assets/images/eco-scene-1.png" alt="Plastic-free eco essentials"><span><small>03 · Ritual</small><strong>Everyday, without waste</strong><b>↗</b></span></a>
        <a class="ci-cat wide" href="/conscious/artisans"><img src="assets/images/eco-scene-4.png" alt="Indian artisan-made collection"><span><small>04 · Craft</small><strong>Made by Indian hands</strong><b>↗</b></span></a>
      </div>
    </div>
  </section>

  <section class="ci-product-journey" id="trending-products" hidden>
    <div class="ci-product-pin">
      <div class="ci-product-heading"><p class="ci-overline">The conscious edit</p><h2>Good things,<br>in motion.</h2><p>Low-impact pieces for the rituals and rooms that make up a life.</p><a href="/conscious/shop">Shop all products <b>↗</b></a></div>
      <div class="ci-product-rail">
        <a class="ci-product-card" href="/conscious/product-detail"><figure><img src="assets/images/eco-scene-1.png" alt="Plastic-free self-care set"><em>New</em></figure><small>SELF CARE · PLASTIC FREE</small><h3>Forest ritual set</h3><p>₹1,290</p></a>
        <a class="ci-product-card" href="/conscious/product-detail"><figure><img src="assets/images/eco-scene-2.png" alt="Natural home collection"><em>Made slowly</em></figure><small>HOME · NATURAL MATERIALS</small><h3>Earth &amp; home edit</h3><p>₹2,450</p></a>
        <a class="ci-product-card" href="/conscious/product-detail"><figure><img src="assets/images/eco-scene-3.png" alt="Naturally dyed handloom textiles"><em>Handloom</em></figure><small>WEAR · NATURAL DYE</small><h3>Woven in sunlight</h3><p>₹3,190</p></a>
        <a class="ci-product-card" href="/conscious/product-detail"><figure><img src="assets/images/eco-scene-4.png" alt="Artisan-crafted collection"><em>Maker first</em></figure><small>CRAFT · SMALL BATCH</small><h3>Hands of India edit</h3><p>₹1,850</p></a>
      </div>
    </div>
  </section>

  <section class="ci-cycle" hidden>
    <div class="ci-cycle-bg"><img src="assets/images/eco-scene-4.png" alt="Indian maker and natural craft"></div>
    <div class="ci-cycle-content ci-shell">
      <p class="ci-overline">From earth · through hands · back with care</p>
      <h2 data-ci-lines>A better product<br>has a better journey.</h2>
      <div class="ci-cycle-steps">
        <div><b>01</b><strong>Source thoughtfully</strong><p>Renewable, reclaimed and natural inputs.</p></div>
        <div><b>02</b><strong>Make fairly</strong><p>Human skill valued over anonymous scale.</p></div>
        <div><b>03</b><strong>Pack lightly</strong><p>Less plastic and less unnecessary material.</p></div>
        <div><b>04</b><strong>Keep longer</strong><p>Useful, repairable and made to be loved.</p></div>
      </div>
    </div>
  </section>

  <section class="ci-impact" id="impact" hidden>
    <div class="ci-shell"><p class="ci-overline" data-ci-reveal>Proof, not promises</p><div class="ci-impact-head"><h2 data-ci-lines>Small choices.<br><em>Real movement.</em></h2><p data-ci-reveal>We are building a marketplace where impact is visible, maker value is protected, and every parcel carries less waste.</p></div>
      <div class="ci-impact-stats" data-ci-reveal><div><strong data-count="120" data-suffix="+">0</strong><span>conscious makers</span></div><div><strong data-count="85" data-suffix="%">0</strong><span>plastic-light packaging</span></div><div><strong data-count="24" data-suffix="">0</strong><span>heritage crafts</span></div><div><strong data-count="18" data-suffix="k+">0</strong><span>mindful orders</span></div></div>
    </div>
  </section>

  <section class="ci-final" hidden><div class="ci-final-image"><img src="assets/images/home-sustainable-hero.png" alt="Sustainable products in nature"></div><div class="ci-final-copy"><p class="ci-overline">Choose what stays</p><h2>Buy less.<br><em>Love it longer.</em></h2><a class="ci-btn ci-btn-primary" href="/conscious/shop">Enter the marketplace <b>↗</b></a></div></section>
</main>

<main class="ci-old-home" aria-hidden="true">

<!-- ────────────────────────────────────────────
     1. HERO
     ──────────────────────────────────────────── -->
<section class="bc-hero" id="bc-hero" aria-label="Hero Banner">
  <!-- Background elements -->
  <div class="bc-hero-pattern"></div>
  <div class="bc-hero-glow bc-hero-glow-saffron" aria-hidden="true"></div>
  <div class="bc-hero-glow bc-hero-glow-gold" aria-hidden="true"></div>
  <div class="bc-hero-dots" id="bc-hero-dots" aria-hidden="true"></div>

  <div class="bc-hero-content container">
    <div class="bc-hero-inner">

      <!-- Left: Copy -->
      <div class="bc-hero-left">
        <div class="bc-hero-badge bc-hero-anim-1" aria-label="World's first conscious commerce platform">
          <span class="dot"></span>
          🌍 World's First Conscious Commerce Platform
        </div>

        <h1 class="bc-hero-title bc-hero-anim-2">
          World's First <span class="accent">Commerce</span><br/>
          &amp; Events Platform
        </h1>

        <p class="bc-hero-subtitle bc-hero-anim-3">
          Connecting Sustainable Brands, Artisans, Creators &amp; Conscious Consumers Across The Globe.
          Community, Commerce, Events &amp; Sustainability — all in one place.
        </p>

        <div class="bc-hero-ctas bc-hero-anim-4">
          <a href="/conscious/shop" class="bc-btn bc-btn-primary bc-btn-lg">
            <i class="fa-solid fa-bag-shopping"></i> Explore Marketplace
          </a>
          <a href="/conscious/community" class="bc-btn bc-btn-outline-white bc-btn-lg">
            <i class="fa-solid fa-leaf"></i> Join Community
          </a>
        </div>

        <div class="bc-hero-stats bc-hero-anim-5">
          <div class="bc-hero-stat">
            <span class="bc-hero-stat-value">Growing</span>
            <span class="bc-hero-stat-label">Community</span>
          </div>
          <div class="bc-hero-stat-divider"></div>
          <div class="bc-hero-stat">
            <span class="bc-hero-stat-value">Verified</span>
            <span class="bc-hero-stat-label">Brands</span>
          </div>
          <div class="bc-hero-stat-divider"></div>
          <div class="bc-hero-stat">
            <span class="bc-hero-stat-value">Curated</span>
            <span class="bc-hero-stat-label">Partners</span>
          </div>
          <div class="bc-hero-stat-divider"></div>
          <div class="bc-hero-stat">
            <span class="bc-hero-stat-value">Active</span>
            <span class="bc-hero-stat-label">Artisan Network</span>
          </div>
        </div>
      </div>

      <!-- Right: Visual -->
      <div class="bc-hero-visual bc-hero-anim-right" aria-hidden="true">

        <!-- Spinning Mandala Background -->
        <div class="bc-hero-mandala">
          <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <circle cx="100" cy="100" r="95" fill="none" stroke="currentColor" stroke-width="1"/>
            <circle cx="100" cy="100" r="78" fill="none" stroke="currentColor" stroke-width="0.5"/>
            <circle cx="100" cy="100" r="55" fill="none" stroke="currentColor" stroke-width="0.5"/>
            <circle cx="100" cy="100" r="30" fill="none" stroke="currentColor" stroke-width="1"/>
            <circle cx="100" cy="100" r="10" fill="currentColor" opacity="0.5"/>
            <g stroke="currentColor" stroke-width="0.8">
              <line x1="100" y1="5"  x2="100" y2="45"/>  <line x1="100" y1="155" x2="100" y2="195"/>
              <line x1="5"   y1="100" x2="45"  y2="100"/> <line x1="155" y1="100" x2="195" y2="100"/>
              <line x1="30"  y1="30"  x2="58"  y2="58"/>  <line x1="142" y1="142" x2="170" y2="170"/>
              <line x1="170" y1="30"  x2="142" y2="58"/>  <line x1="58"  y1="142" x2="30"  y2="170"/>
              <line x1="100" y1="5"  x2="87"  y2="44"/>   <line x1="113" y1="156" x2="100" y2="195"/>
              <line x1="63"  y1="15" x2="72"  y2="53"/>   <line x1="128" y1="147" x2="137" y2="185"/>
              <line x1="30"  y1="63" x2="53"  y2="72"/>   <line x1="147" y1="128" x2="185" y2="137"/>
              <line x1="5"   y1="100" x2="44" y2="87"/>   <line x1="156" y1="113" x2="195" y2="100"/>
            </g>
            <ellipse cx="100" cy="62" rx="6" ry="12" fill="currentColor" opacity="0.6"/>
            <ellipse cx="100" cy="138" rx="6" ry="12" fill="currentColor" opacity="0.6"/>
            <ellipse cx="62" cy="100" rx="12" ry="6" fill="currentColor" opacity="0.6"/>
            <ellipse cx="138" cy="100" rx="12" ry="6" fill="currentColor" opacity="0.6"/>
          </svg>
        </div>

        <!-- Main Display — Brand Symbol -->
        <div class="bc-hero-craft-showcase">
          <div class="bc-hero-craft-main bc-hero-craft-main--symbol">
            <img
              src="assets/images/symbol.png"
              alt=""
              class="bc-hero-symbol-img"
              width="200"
              height="200"
              aria-hidden="true"
              loading="eager"
            />
          </div>
        </div>

        <!-- Floating Teasers: Brand · Artisan · Event -->
        <div class="bc-hero-floating-card card-1">
          <div class="bc-floating-card-icon green">🌿</div>
          <div class="bc-floating-card-info">
            <span class="bc-floating-card-name">EarthWeave Co.</span>
            <span class="bc-floating-card-price">Verified Brand · Fashion</span>
          </div>
        </div>

        <div class="bc-hero-floating-card card-2">
          <div class="bc-floating-card-icon gold">🧵</div>
          <div class="bc-floating-card-info">
            <span class="bc-floating-card-name">Pashmina Shawl</span>
            <span class="bc-floating-card-price">₹8,999 · Kashmir Artisan</span>
          </div>
        </div>

        <div class="bc-hero-floating-card card-3">
          <div class="bc-floating-card-icon saffron">🎪</div>
          <div class="bc-floating-card-info">
            <span class="bc-floating-card-name">Conscious Summer Pop-Up</span>
            <span class="bc-floating-card-price">Jul 12–14 · New Delhi</span>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>


<!-- ────────────────────────────────────────────
     TRUST BADGES STRIP
     ──────────────────────────────────────────── -->
<div class="bc-trust-strip" role="complementary" aria-label="Trust signals">
  <div class="container">
    <div class="bc-trust-inner">

      <div class="bc-trust-item bc-reveal">
        <div class="bc-trust-icon saffron"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></div>
        <div>
          <div class="bc-trust-info-title">100% Authentic</div>
          <div class="bc-trust-info-desc">Handcrafted &amp; sustainably verified</div>
        </div>
      </div>

      <div class="bc-trust-divider" aria-hidden="true"></div>

      <div class="bc-trust-item bc-reveal delay-1">
        <div class="bc-trust-icon navy"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
        <div>
          <div class="bc-trust-info-title">Secure Payments</div>
          <div class="bc-trust-info-desc">UPI, Cards, and Net Banking</div>
        </div>
      </div>

      <div class="bc-trust-divider" aria-hidden="true"></div>

      <div class="bc-trust-item bc-reveal delay-2" hidden aria-hidden="true">
        <div class="bc-trust-icon green"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
        <div>
          <div class="bc-trust-info-title">Pan-India Delivery</div>
          <div class="bc-trust-info-desc">Free above ₹999 · Express available</div>
        </div>
      </div>

      <div class="bc-trust-divider" aria-hidden="true"></div>

      <div class="bc-trust-item bc-reveal delay-3">
        <div class="bc-trust-icon gold"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i></div>
        <div>
          <div class="bc-trust-info-title">Easy Returns</div>
          <div class="bc-trust-info-desc">7-day hassle-free return policy</div>
        </div>
      </div>

      <div class="bc-trust-divider" aria-hidden="true"></div>

      <div class="bc-trust-item bc-reveal delay-4">
        <div class="bc-trust-icon saffron"><i class="fa-solid fa-certificate" aria-hidden="true"></i></div>
        <div>
          <div class="bc-trust-info-title">Verified Partners</div>
          <div class="bc-trust-info-desc">Background-checked brands &amp; artisans</div>
        </div>
      </div>

    </div>
  </div>
</div>


<!-- ────────────────────────────────────────────
     6. MARKETPLACE CATEGORIES
     ──────────────────────────────────────────── -->
<section class="section-py bg-warm" id="marketplace" aria-labelledby="categories-heading">
  <div class="container">

    <div class="bc-section-header centered bc-reveal">
      <div class="bc-section-label">Explore Our Collection</div>
      <h2 class="bc-section-title" id="categories-heading">Shop by Conscious Category</h2>
      <p class="bc-section-desc">From ancient Rajasthani blue pottery to contemporary eco-friendly crafts — discover the rich diversity of India's handmade traditions.</p>
    </div>

    <div class="bc-categories-grid">

      <a href="/conscious/pottery" class="bc-category-card bc-cat-1" aria-label="Pottery & Ceramics">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Pottery &amp; Ceramics</div>
          <div class="bc-cat-count">8,400+ products</div>
        </div>
      </a>

      <a href="/conscious/jewelry" class="bc-category-card bc-cat-2" aria-label="Handmade Jewelry">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Handmade Jewelry</div>
          <div class="bc-cat-count">22,000+ products</div>
        </div>
      </a>

      <a href="/conscious/wood" class="bc-category-card bc-cat-3" aria-label="Wood Crafts">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Wood Crafts</div>
          <div class="bc-cat-count">5,200+ products</div>
        </div>
      </a>

      <a href="/conscious/home-decor" class="bc-category-card bc-cat-4" aria-label="Home Decor">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Home Decor</div>
          <div class="bc-cat-count">18,600+ products</div>
        </div>
      </a>

      <a href="/conscious/handloom" class="bc-category-card bc-cat-5" aria-label="Handloom Textiles">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Handloom</div>
          <div class="bc-cat-count">14,300+ products</div>
        </div>
      </a>

      <a href="/conscious/paintings" class="bc-category-card bc-cat-6" aria-label="Paintings">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Paintings</div>
          <div class="bc-cat-count">9,800+ products</div>
        </div>
      </a>

      <a href="/conscious/traditional-art" class="bc-category-card bc-cat-7" aria-label="Traditional Art">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Traditional Art</div>
          <div class="bc-cat-count">7,100+ products</div>
        </div>
      </a>

      <a href="/conscious/fashion" class="bc-category-card bc-cat-8" aria-label="Fashion Accessories">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Fashion Accessories</div>
          <div class="bc-cat-count">11,400+ products</div>
        </div>
      </a>

      <a href="/conscious/gifts" class="bc-category-card bc-cat-9" aria-label="Gift Items">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Gift Items</div>
          <div class="bc-cat-count">6,700+ products</div>
        </div>
      </a>

      <a href="/conscious/eco" class="bc-category-card bc-cat-10" aria-label="Eco Friendly Products">
        <div class="bc-cat-bg" aria-hidden="true"></div>
        <div class="bc-cat-gradient-overlay" aria-hidden="true"></div>
        <div class="bc-cat-content">
          <div class="bc-cat-name">Eco Friendly</div>
          <div class="bc-cat-count">4,900+ products</div>
        </div>
      </a>

    </div>

    <div class="text-center mt-5 bc-reveal">
      <a href="/conscious/shop" class="bc-btn bc-btn-navy bc-btn-lg">
        Browse All Categories <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>

  



<!-- ════════════════════════════════════════════
     OUR TRENDING PRODUCTS
     ════════════════════════════════════════════ -->
<section class="section-py bc-trending-section" id="trending-products" aria-labelledby="trending-heading">
  <div class="container">

    <!-- Section Header -->
    <div class="bc-section-header bc-reveal" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:28px;">
      <div>
        <div class="bc-section-label">What's Hot Right Now</div>
        <h2 class="bc-section-title" id="trending-heading">Our Trending Products</h2>
        <p class="bc-section-desc" style="max-width:520px;">Handpicked bestsellers flying off artisan shelves — authentic crafts that conscious buyers across India love most right now.</p>
      </div>
      <a href="/conscious/shop" class="bc-btn bc-btn-outline">Shop All Products <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <!-- Live stats strip -->
    <div class="bc-trending-stats bc-reveal delay-1">
      <div class="bc-trending-stat"><i class="fa-solid fa-fire"></i> <span><strong>1,240+</strong> orders placed today</span></div>
      <div class="bc-trending-stat"><i class="fa-solid fa-arrow-trend-up"></i> <span><strong>8 crafts</strong> trending this week</span></div>
      <div class="bc-trending-stat"><i class="fa-solid fa-star"></i> <span>Average rating <strong>4.9★</strong></span></div>
      <div class="bc-trending-stat"><i class="fa-solid fa-truck-fast"></i> <span>Free shipping above <strong>₹999</strong></span></div>
    </div>

    <!-- Filter Tabs -->
    <div class="bc-product-tabs bc-reveal delay-1" role="tablist" aria-label="Filter trending products by category">
      <button class="bc-product-tab active" data-filter="all"      role="tab" aria-selected="true">All Trending</button>
      <button class="bc-product-tab"        data-filter="pottery"   role="tab">Pottery</button>
      <button class="bc-product-tab"        data-filter="jewelry"   role="tab">Jewelry</button>
      <button class="bc-product-tab"        data-filter="textiles"  role="tab">Textiles</button>
      <button class="bc-product-tab"        data-filter="wood"      role="tab">Wood Crafts</button>
      <button class="bc-product-tab"        data-filter="paintings" role="tab">Paintings</button>
      <button class="bc-product-tab"        data-filter="art"       role="tab">Traditional Art</button>
    </div>

    <!-- ── Products Carousel ── -->
    <div class="bc-products-carousel bc-reveal delay-2" role="region" aria-label="Trending products">

      <button class="bc-product-carousel-btn bc-product-carousel-prev" aria-label="Previous products">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <div class="bc-products-carousel-viewport">
        <div class="bc-products-grid">

          <!-- 1 — Jaipur Blue Pottery Vase — pottery -->
          <div class="bc-product-card" data-category="pottery" aria-label="Jaipur Blue Pottery Vase">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-1">
                <img src="https://i.etsystatic.com/60980066/r/il/bae196/7103271779/il_1080xN.7103271779_8v0v.jpg"
                     alt="Jaipur Blue Pottery Vase"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-hot">🔥 Bestseller</span>
              <div class="bc-trend-rank">#1</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--saffron-pale)">🏺</div>
                <span class="bc-product-artisan-name">Rajesh Kumawat · Jaipur</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Hand-Painted Jaipur Blue Pottery Vase</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Rajasthan · Wheel-thrown &amp; glazed</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.9</span>
                <span class="bc-product-rating-count">(384 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹1,899</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹2,499</span>
                  <span class="bc-product-price-off" style="margin-left:4px">24% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 2 — Kundan Necklace — jewelry -->
          <div class="bc-product-card" data-category="jewelry" aria-label="Meenakari Kundan Necklace Set">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-2">
                <img src="https://i.pinimg.com/originals/dc/d6/18/dcd618e0b1e6445f031bd55fc4ad204e.jpg"
                     alt="Meenakari Kundan Necklace Set"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-hot">🔥 Hot</span>
              <div class="bc-trend-rank">#2</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--gold-pale)">💎</div>
                <span class="bc-product-artisan-name">Seema Jewels · Jaipur</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Meenakari Kundan Necklace Set</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Rajasthan · 24K Gold Polish</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">5.0</span>
                <span class="bc-product-rating-count">(219 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹4,299</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹5,999</span>
                  <span class="bc-product-price-off" style="margin-left:4px">28% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 3 — Pure Pashmina Shawl — textiles -->
          <div class="bc-product-card" data-category="textiles" aria-label="Pure Pashmina Shawl">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-3">
                <img src="https://i.pinimg.com/originals/0c/f1/9d/0cf19d19caa0dc0a0e44729335b5286d.jpg"
                     alt="Pure Pashmina Hand-Embroidered Shawl"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-new">New Arrival</span>
              <div class="bc-trend-rank">#3</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot">🧵</div>
                <span class="bc-product-artisan-name">Weavers of Kashmir</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Pure Pashmina Hand-Embroidered Shawl</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Kashmir · 100% Pure Pashmina</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.8</span>
                <span class="bc-product-rating-count">(176 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹8,999</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹11,999</span>
                  <span class="bc-product-price-off" style="margin-left:4px">25% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 4 — Madhubani Painting — paintings -->
          <div class="bc-product-card" data-category="paintings" aria-label="Madhubani Painting on Canvas">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-4">
                <img src="https://i.pinimg.com/originals/7d/0d/14/7d0d142eaf2ab899e4e8cb5f69f03fe4.png"
                     alt="Madhubani Fish Painting on Canvas"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-hot">🔥 Hot</span>
              <div class="bc-trend-rank">#4</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--saffron-pale)">🎨</div>
                <span class="bc-product-artisan-name">Durgabai Devi · Bihar</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Authentic Madhubani Fish Painting on Canvas</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Bihar · Natural Colour Pigments</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.9</span>
                <span class="bc-product-rating-count">(302 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹3,499</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹4,999</span>
                  <span class="bc-product-price-off" style="margin-left:4px">30% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 5 — Sandalwood Jewellery Box — wood -->
          <div class="bc-product-card" data-category="wood" aria-label="Hand-Carved Sandalwood Jewellery Box">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-5">
                <img src="https://www.memeraki.com/cdn/shop/files/Red-Sandalwood-Jewelry-Box--by-Om-Prakash-1_2400x.png?v=1726057016"
                     alt="Hand-Carved Sandalwood Jewellery Box"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-new">Bestseller</span>
              <div class="bc-trend-rank">#5</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--green-pale)">🪵</div>
                <span class="bc-product-artisan-name">Mysore Wood Artisans</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Hand-Carved Sandalwood Jewellery Box</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Karnataka · Aged Sandalwood</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.7</span>
                <span class="bc-product-rating-count">(143 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹2,199</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹2,899</span>
                  <span class="bc-product-price-off" style="margin-left:4px">24% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 6 — Warli Wall Hanging — art -->
          <div class="bc-product-card" data-category="art" aria-label="Warli Tribal Art Wall Hanging">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-6">
                <img src="https://cdn.magicdecor.in/com/2024/01/17124728/Vilage-Life-Ancient-Indian-Warli-Art-Wallpaper-for-Wall-M-710x488.jpg"
                     alt="Warli Tribal Art Wall Hanging"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-hot">🔥 Hot</span>
              <div class="bc-trend-rank">#6</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--saffron-pale)">🖼</div>
                <span class="bc-product-artisan-name">Jivya Mashe · Palghar</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Warli Tribal Art Wall Hanging — Earth &amp; White</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Maharashtra · Tribal Warli Art</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.8</span>
                <span class="bc-product-rating-count">(97 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹1,499</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹1,999</span>
                  <span class="bc-product-price-off" style="margin-left:4px">25% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 7 — Dhokra Figurine — art -->
          <div class="bc-product-card" data-category="art" aria-label="Dhokra Lost-Wax Cast Figurine">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-7">
                <img src="https://ancientcrafting.com/wp-content/uploads/2025/06/Tribal-Bastar-Woman-with-Supa-Dhokra-Figurine.jpg"
                     alt="Dhokra Tribal Figurine"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-new">Limited</span>
              <div class="bc-trend-rank">#7</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--gold-pale)">🏺</div>
                <span class="bc-product-artisan-name">Dhokra Collective · Bastar</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Dhokra Lost-Wax Cast Tribal Figurine</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;Chhattisgarh · 4,000-Year Tradition</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.9</span>
                <span class="bc-product-rating-count">(88 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹2,799</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹3,499</span>
                  <span class="bc-product-price-off" style="margin-left:4px">20% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

          <!-- 8 — Kantha Cushion Cover — textiles -->
          <div class="bc-product-card" data-category="textiles" aria-label="Kantha Embroidered Cushion Covers">
            <div class="bc-product-img-wrap">
              <div class="bc-product-img bc-prod-bg-8">
                <img src="https://www.sundariihandmade.com/cdn/shop/products/IMG_1918.jpg?v=1667571926&width=1920"
                     alt="Kantha Embroidered Cushion Cover Set"
                     class="bc-product-img-icon"
                     width="220" height="220"
                     loading="lazy" />
              </div>
              <span class="bc-product-badge bc-product-badge-sale">−20%</span>
              <div class="bc-trend-rank">#8</div>
              <div class="bc-product-actions">
                <button class="bc-product-action-btn wishlist" aria-label="Add to Wishlist"><i class="fa-regular fa-heart"></i></button>
                <button class="bc-product-action-btn quickview" aria-label="Quick View"><i class="fa-regular fa-eye"></i></button>
                <button class="bc-product-action-btn share" aria-label="Share"><i class="fa-solid fa-share-nodes"></i></button>
              </div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan">
                <div class="bc-product-artisan-dot" style="background:var(--navy-pale)">🧵</div>
                <span class="bc-product-artisan-name">Kantha SHG · West Bengal</span>
              </div>
              <a href="/conscious/product-detail" class="bc-product-title">Kantha Hand-Embroidered Cushion Cover (Set of 2)</a>
              <div class="bc-product-state"><i class="fa-solid fa-location-dot" style="font-size:.625rem"></i>&nbsp;West Bengal · Hand Kantha Stitch</div>
              <div class="bc-product-rating">
                <span style="color:var(--gold);font-size:.75rem">★★★★★</span>
                <span class="bc-product-rating-val">4.8</span>
                <span class="bc-product-rating-count">(214 reviews)</span>
              </div>
              <div class="bc-product-price-row">
                <div>
                  <span class="bc-product-price">₹1,199</span>
                  <span class="bc-product-price-original" style="margin-left:5px">₹1,499</span>
                  <span class="bc-product-price-off" style="margin-left:4px">20% off</span>
                </div>
                <button class="bc-product-add-btn" aria-label="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
              </div>
            </div>
          </div>

        </div><!-- /bc-products-grid -->
      </div><!-- /bc-products-carousel-viewport -->

      <button class="bc-product-carousel-btn bc-product-carousel-next" aria-label="Next products">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
      <div class="bc-product-carousel-pagination" aria-label="Carousel pagination"></div>

    </div><!-- /bc-products-carousel -->

    <!-- View All CTA -->
    <div class="text-center mt-5 bc-reveal">
      <a href="/conscious/shop" class="bc-btn bc-btn-navy bc-btn-lg">
        <i class="fa-solid fa-bag-shopping"></i> Explore All Products
      </a>
      <p style="font-size:0.8125rem;color:var(--text-muted);margin-top:10px;">Handcrafted by verified artisans · Fast pan-India delivery</p>
    </div>

  </div>
</section>


<!-- ────────────────────────────────────────────
     2. FEATURED UPCOMING EVENTS
     ──────────────────────────────────────────── -->
<section class="section-py bc-events-section" id="events" aria-labelledby="events-heading">
  <div class="container">

    <!-- Section Header -->
    <div class="bc-section-header bc-reveal" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:36px;">
      <div>
        <div class="bc-section-label">Happening Now</div>
        <h2 class="bc-section-title" id="events-heading">Featured Upcoming Events</h2>
        <p class="bc-section-desc" style="max-width:520px;">Pop-ups, exhibitions, artisan markets, sustainability conferences &amp; workshops — be part of India's growing conscious living community.</p>
      </div>
      <a href="/conscious/events" class="bc-btn bc-btn-outline">View More Events <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <!-- Filter Tabs -->
    <div class="bc-event-tabs bc-reveal delay-1" role="tablist" aria-label="Event type filter">
      <button class="bc-event-tab active" data-efilter="all"          role="tab" aria-selected="true">All Events</button>
      <button class="bc-event-tab"        data-efilter="popup"        role="tab">Pop-Ups</button>
      <button class="bc-event-tab"        data-efilter="exhibition"   role="tab">Exhibitions</button>
      <button class="bc-event-tab"        data-efilter="market"       role="tab">Artisan Markets</button>
      <button class="bc-event-tab"        data-efilter="conference"   role="tab">Conferences</button>
      <button class="bc-event-tab"        data-efilter="workshop"     role="tab">Workshops</button>
      <button class="bc-event-tab"        data-efilter="corporate"    role="tab">Corporate Collabs</button>
      <button class="bc-event-tab"        data-efilter="university"   role="tab">University Collabs</button>
    </div>

    <!-- ── FEATURED EVENT ── -->
    <div class="bc-event-featured bc-reveal" data-etype="conference" aria-label="Featured Event">
      <div class="bc-event-feat-bg" aria-hidden="true"></div>
      <div class="bc-event-feat-overlay" aria-hidden="true"></div>

      <div class="bc-event-feat-body">
        <div class="bc-event-feat-left">
          <div class="bc-event-feat-badges">
            <span class="bc-event-type-badge type-conference">
              <i class="fa-solid fa-star"></i> Featured
            </span>
            <span class="bc-event-live-badge">
              <span class="bc-live-dot"></span> Registration Open
            </span>
          </div>

          <h3 class="bc-event-feat-title">Sustainable Living Summit 2025</h3>
          <p class="bc-event-feat-desc">India's premier sustainability conference — bringing together conscious brands, policymakers, artisans &amp; creators for a greener future. Keynotes, panels, exhibitor booths &amp; networking.</p>

          <div class="bc-event-feat-meta">
            <span class="bc-event-meta-item">
              <i class="fa-regular fa-calendar"></i> September 20, 2025
            </span>
            <span class="bc-event-meta-item">
              <i class="fa-solid fa-location-dot"></i> Koramangala, Bengaluru
            </span>
            <span class="bc-event-meta-item">
              <i class="fa-regular fa-clock"></i> 9:00 AM – 6:00 PM
            </span>
            <span class="bc-event-meta-item">
              <i class="fa-solid fa-users"></i> 60+ Speakers · 9 Brand Categories
            </span>
          </div>

          <div class="bc-event-feat-actions">
            <a href="/conscious/events/sustainable-living-summit" class="bc-btn bc-btn-primary bc-btn-lg">
              <i class="fa-solid fa-ticket"></i> Register Free
            </a>
            <a href="/conscious/events/sustainable-living-summit" class="bc-btn bc-btn-outline-white">
              Learn More <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </div>

        <div class="bc-event-feat-right" aria-hidden="true">
          <div class="bc-event-feat-countdown" id="bc-event-countdown">
            <div class="bc-countdown-title">Event Starts In</div>
            <div class="bc-countdown-grid">
              <div class="bc-countdown-unit">
                <span class="bc-countdown-val" id="cd-days">12</span>
                <span class="bc-countdown-lbl">Days</span>
              </div>
              <div class="bc-countdown-sep">:</div>
              <div class="bc-countdown-unit">
                <span class="bc-countdown-val" id="cd-hrs">08</span>
                <span class="bc-countdown-lbl">Hours</span>
              </div>
              <div class="bc-countdown-sep">:</div>
              <div class="bc-countdown-unit">
                <span class="bc-countdown-val" id="cd-min">34</span>
                <span class="bc-countdown-lbl">Mins</span>
              </div>
              <div class="bc-countdown-sep">:</div>
              <div class="bc-countdown-unit">
                <span class="bc-countdown-val" id="cd-sec">50</span>
                <span class="bc-countdown-lbl">Secs</span>
              </div>
            </div>
          </div>

          <div class="bc-event-feat-stats">
            <div class="bc-event-feat-stat">
              <span class="bc-event-feat-stat-val">60+</span>
              <span class="bc-event-feat-stat-lbl">Speakers</span>
            </div>
            <div class="bc-event-feat-stat-div"></div>
            <div class="bc-event-feat-stat">
              <span class="bc-event-feat-stat-val">1</span>
              <span class="bc-event-feat-stat-lbl">Day</span>
            </div>
            <div class="bc-event-feat-stat-div"></div>
            <div class="bc-event-feat-stat">
              <span class="bc-event-feat-stat-val">Free</span>
              <span class="bc-event-feat-stat-lbl">Entry</span>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /featured event -->

    <!-- ── EVENT CARDS GRID ── -->
    <div class="bc-events-grid" id="bc-events-grid">

      <!-- Event 1 — Workshop -->
      <article class="bc-event-card bc-reveal delay-1" data-etype="workshop" aria-label="Natural Dyeing Workshop">
        <div class="bc-event-card-head ev-workshop">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">JUL</span>
            <span class="bc-ev-day">27</span>
          </div>
          <span class="bc-event-type-badge type-workshop">Workshop</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/free-photo/talented-woman-doing-pottery_23-2151709007.jpg?t=st=1782123880~exp=1782127480~hmac=dd7c60b07cd850ae3aeceea8ce915f459f1afbf80b6845ae911aa76987b9f7f0&w=740" alt="Workshop"></div>
          <h4 class="bc-event-card-title">Natural Dyeing Workshop with Master Artisans</h4>
          <p class="bc-event-card-desc">Learn traditional plant-based dyeing techniques directly from artisans who've preserved this craft for generations. Kit delivered to your door.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 5 PM – 7 PM</span>
            <span><i class="fa-solid fa-video"></i> Zoom · Kit Included</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot"></span> ₹799 · 20 seats left
            </div>
            <a href="/conscious/events/natural-dyeing-workshop" class="bc-event-cta-btn">Book Seat <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Event 2 — Artisan Market -->
      <article class="bc-event-card bc-reveal delay-2" data-etype="market" aria-label="Conscious Summer Artisan Market">
        <div class="bc-event-card-head ev-market">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">JUL</span>
            <span class="bc-ev-day">12</span>
          </div>
          <span class="bc-event-type-badge type-market"><i class="fa-solid fa-leaf"></i> Artisan Market</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/free-photo/women-happily-shopping-buying-consumer-products-customer-day_23-2151623440.jpg?ga=GA1.1.579367412.1781162634&semt=ais_incoming&w=740&q=80" alt="Artisan Market"></div>
          <h4 class="bc-event-card-title">Conscious Summer Artisan Market</h4>
          <p class="bc-event-card-desc">100+ sustainable brands &amp; artisans under one roof. Shop, discover, and connect with India's finest conscious creators.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 10 AM – 8 PM</span>
            <span><i class="fa-solid fa-location-dot"></i> Lado Sarai, New Delhi</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot market"></span> Free Entry
            </div>
            <a href="/conscious/events/artisan-market" class="bc-event-cta-btn">Know More <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Event 3 — Exhibition -->
      <article class="bc-event-card bc-reveal delay-3" data-etype="exhibition" aria-label="Artisan Heritage Exhibition">
        <div class="bc-event-card-head ev-exhibition">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">AUG</span>
            <span class="bc-ev-day">03</span>
          </div>
          <span class="bc-event-type-badge type-exhibition">Exhibition</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/free-photo/talented-child-doing-pottery_23-2151693886.jpg?t=st=1782124045~exp=1782127645~hmac=9bbf23c2406d469e3d36f6d99967b4852404c33a9528bed79e6b3d78fa4829b4&w=740" alt="Exhibition"></div>
          <h4 class="bc-event-card-title">Artisan Heritage Exhibition</h4>
          <p class="bc-event-card-desc">A curated showcase of India's rare craft traditions — from Madhubani to Pattachitra — told through artisan stories.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 11 AM – 8 PM</span>
            <span><i class="fa-solid fa-location-dot"></i> BKC, Mumbai</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot exhibition"></span> ₹199 Entry
            </div>
            <a href="/conscious/events/artisan-heritage-exhibition" class="bc-event-cta-btn">Know More <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Event 4 — University Collaboration -->
      <article class="bc-event-card bc-reveal delay-1" data-etype="university" aria-label="University Design Fest">
        <div class="bc-event-card-head ev-university">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">AUG</span>
            <span class="bc-ev-day">22</span>
          </div>
          <span class="bc-event-type-badge type-university">University Collab</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/premium-photo/buddhist-monks-creating-colorful-mandala-with-sand_14117-1063342.jpg?w=740" alt="University Collaboration"></div>
          <h4 class="bc-event-card-title">Symbiosis × Conscious India Design Fest</h4>
          <p class="bc-event-card-desc">Students &amp; artisans co-create sustainable product lines — bridging tradition with contemporary design thinking.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 10 AM – 5 PM</span>
            <span><i class="fa-solid fa-location-dot"></i> Symbiosis, Pune</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot university"></span> Open to All
            </div>
            <a href="/conscious/events/university-design-fest" class="bc-event-cta-btn">Join Now <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Event 5 — Pop-Up -->
      <article class="bc-event-card bc-reveal delay-2" data-etype="popup" aria-label="Conscious Summer Pop-Up">
        <div class="bc-event-card-head ev-popup">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">JUL</span>
            <span class="bc-ev-day">14</span>
          </div>
          <span class="bc-event-type-badge type-popup">Pop-Up</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/premium-photo/group-people-are-working-pottery-studio-they-are-all-wearing-casual-clothes-are-covered-clay_14117-402479.jpg?w=740" alt="Pop-Up"></div>
          <h4 class="bc-event-card-title">Conscious Summer Pop-Up 2025</h4>
          <p class="bc-event-card-desc">A weekend pop-up celebrating sustainable fashion, organic food &amp; wellness brands — live demos &amp; exclusive launches.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 11 AM – 9 PM</span>
            <span><i class="fa-solid fa-location-dot"></i> Lado Sarai, Delhi</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot popup"></span> Free Entry
            </div>
            <a href="/conscious/events/conscious-summer-popup" class="bc-event-cta-btn">Know More <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </article>

      <!-- Event 6 — Corporate Collaboration -->
      <article class="bc-event-card bc-reveal delay-3" data-etype="corporate" aria-label="ESG Corporate Gifting Showcase">
        <div class="bc-event-card-head ev-corporate">
          <div class="bc-event-date-badge">
            <span class="bc-ev-month">OCT</span>
            <span class="bc-ev-day">05</span>
          </div>
          <span class="bc-event-type-badge type-corporate">Corporate Collab</span>
        </div>
        <div class="bc-event-card-body">
          <div class="bc-event-card-icon" aria-hidden="true"><img src="https://img.magnific.com/premium-photo/art-gallery-full-people-looking-colorful-paintings_14117-348228.jpg?w=740" alt="ESG Corporate Gifting Showcase"></div>
          <h4 class="bc-event-card-title">ESG Corporate Gifting Showcase</h4>
          <p class="bc-event-card-desc">Exclusive event for HR &amp; procurement teams exploring sustainable gifting solutions from verified artisans &amp; brands.</p>
          <div class="bc-event-card-meta">
            <span><i class="fa-regular fa-clock"></i> 10 AM – 6 PM</span>
            <span><i class="fa-solid fa-location-dot"></i> HITEC City, Hyderabad</span>
          </div>
          <div class="bc-event-card-footer">
            <div class="bc-event-slots">
              <span class="bc-slots-dot corporate"></span> B2B Only
            </div>
            <a href="/conscious/events/esg-corporate-showcase" class="bc-event-cta-btn">Notify Me <i class="fa-solid fa-bell"></i></a>
          </div>
        </div>
      </article>

    </div><!-- /events grid -->

    <!-- View All CTA -->
    <div class="text-center mt-5 bc-reveal">
      <a href="/conscious/events" class="bc-btn bc-btn-navy bc-btn-lg">
        <i class="fa-regular fa-calendar"></i> View More Events
      </a>
    </div>

  </div>
</section>


<!-- ────────────────────────────────────────────
     3. SUSTAINABLE BRANDS DIRECTORY
     ──────────────────────────────────────────── -->
<!-- <section class="section-py" id="brands" aria-labelledby="brands-heading">
  <div class="container">

    <div class="bc-section-header bc-reveal" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:32px;">
      <div>
        <div class="bc-section-label">Featured</div>
        <h2 class="bc-section-title" id="brands-heading">Sustainable Brands Directory</h2>
        <p class="bc-section-desc">Discover verified brands committed to conscious commerce, circular economy &amp; a greener planet.</p>
      </div>
      <a href="/conscious/brands" class="bc-btn bc-btn-outline">Browse All Brands <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    
    <div class="bc-brand-tabs bc-reveal delay-1" role="tablist" aria-label="Brand category filter">
      <button class="bc-brand-tab active" data-bfilter="all" role="tab" aria-selected="true">All</button>
      <button class="bc-brand-tab" data-bfilter="fashion" role="tab">Sustainable Fashion</button>
      <button class="bc-brand-tab" data-bfilter="food" role="tab">Organic Food</button>
      <button class="bc-brand-tab" data-bfilter="wellness" role="tab">Wellness</button>
      <button class="bc-brand-tab" data-bfilter="skincare" role="tab">Natural Skincare</button>
      <button class="bc-brand-tab" data-bfilter="decor" role="tab">Home Decor</button>
      <button class="bc-brand-tab" data-bfilter="zerowaste" role="tab">Zero Waste</button>
      <button class="bc-brand-tab" data-bfilter="esg" role="tab">ESG Brands</button>
      <button class="bc-brand-tab" data-bfilter="circular" role="tab">Circular Economy</button>
    </div>

    <div class="bc-vendors-grid">

      <div class="bc-vendor-card bc-reveal delay-1" data-bcat="fashion,circular">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-1" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">EarthWeave Co.</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Auroville, Tamil Nadu</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Verified Brand</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Sustainable Fashion</span>
          <span class="bc-vendor-cat-tag">Circular Economy</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.9★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">1.8K</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">120</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

      <div class="bc-vendor-card bc-reveal delay-2" data-bcat="food">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-2" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Annapurna Organics</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Pune, Maharashtra</div>
            <span class="bc-vendor-verified cert-gold"><i class="fa-solid fa-circle-check"></i> Organic Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Organic Food</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.8★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">3.1K</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">86</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

      <div class="bc-vendor-card bc-reveal delay-3" data-bcat="skincare,wellness">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-4" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">PureRoots Skincare</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Bengaluru, Karnataka</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Verified Brand</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Natural Skincare</span>
          <span class="bc-vendor-cat-tag">Wellness</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.9★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">2.2K</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">64</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

      <div class="bc-vendor-card bc-reveal delay-1" data-bcat="decor">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-3" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Mitti Home</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Jaipur, Rajasthan</div>
            <span class="bc-vendor-verified cert-navy"><i class="fa-solid fa-circle-check"></i> Carbon Neutral</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Home Decor</span>
          <span class="bc-vendor-cat-tag">Eco-Friendly</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.7★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">980</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">142</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

      <div class="bc-vendor-card bc-reveal delay-2" data-bcat="circular,zerowaste">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-5" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">LoopLife Brands</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Dharavi, Mumbai</div>
            <span class="bc-vendor-verified"><i class="fa-solid fa-circle-check"></i> Verified Brand</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Circular Economy</span>
          <span class="bc-vendor-cat-tag">Zero Waste</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">4.8★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">1.4K</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">58</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

      <div class="bc-vendor-card bc-reveal delay-3" data-bcat="esg,wellness">
        <div class="bc-vendor-card-top">
          <div class="bc-vendor-logo v-6" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
          <div class="bc-vendor-info">
            <div class="bc-vendor-name">Satvik Wellness</div>
            <div class="bc-vendor-location"><i class="fa-solid fa-location-dot"></i> Rishikesh, Uttarakhand</div>
            <span class="bc-vendor-verified cert-saffron"><i class="fa-solid fa-circle-check"></i> ESG Certified</span>
          </div>
        </div>
        <div class="bc-vendor-cats">
          <span class="bc-vendor-cat-tag">Wellness</span>
          <span class="bc-vendor-cat-tag">ESG Brand</span>
        </div>
        <div class="bc-vendor-stats">
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">5.0★</div><div class="bc-vendor-stat-key">Rating</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">760</div><div class="bc-vendor-stat-key">Orders</div></div>
          <div class="bc-vendor-stat"><div class="bc-vendor-stat-val">37</div><div class="bc-vendor-stat-key">Products</div></div>
        </div>
      </div>

    </div>
  </div>
</section> -->
 

<!-- ────────────────────────────────────────────
     4. STORIES THAT MATTER
     ──────────────────────────────────────────── -->
<section class="bc-artisan-bg bc-stm-section section-py" id="stories" aria-labelledby="stories-heading">

  <!-- Ambient decorative glows -->
  <div class="bc-stm-glow bc-stm-glow-l" aria-hidden="true"></div>
  <div class="bc-stm-glow bc-stm-glow-r" aria-hidden="true"></div>

  <div class="container">
    <div class="bc-stm-inner">

      <!-- ── Left: Editorial Panel ── -->
      <div class="bc-stm-left bc-reveal-left">
        <div class="bc-stm-deco-quote" aria-hidden="true">"</div>

        <div class="bc-section-label bc-stm-label">Story-First Commerce</div>
        <h2 class="bc-section-title bc-stm-title" id="stories-heading">Stories That Matter</h2>
        <p class="bc-stm-desc">Every product has a person behind it. We lead with the human story before the sale — artisans, sustainable brands, women entrepreneurs &amp; circular economy pioneers shaping conscious commerce in India.</p>

        <!-- Story-type category pills -->
        <div class="bc-stm-type-pills">
          <span class="bc-stm-pill bc-stm-pill--artisan">🏺 Artisans</span>
          <span class="bc-stm-pill bc-stm-pill--brand">🌿 Brands</span>
          <span class="bc-stm-pill bc-stm-pill--woman">👩 Women Entrepreneurs</span>
          <span class="bc-stm-pill bc-stm-pill--circular">♻️ Circular Economy</span>
        </div>

        <a href="/conscious/stories" class="bc-btn bc-btn-primary bc-btn-lg bc-stm-cta">
          <i class="fa-solid fa-book-open"></i> Read All Stories
        </a>
      </div>

      <!-- ── Right: 2×2 Story Cards ── -->
      <div class="bc-stm-grid bc-reveal-right">

        <!-- Card 1: Artisan -->
        <article class="bc-stm-card bc-stm-card--artisan" role="article">
          <div class="bc-stm-card-bar" aria-hidden="true"></div>
          <header class="bc-stm-card-head">
            <div class="bc-stm-avatar bc-stm-av--artisan">
              <img src="assets/images/user.png" alt="Kamla Devi, Block Print Weaver" width="48" height="48" loading="lazy" />
            </div>
            <div class="bc-stm-card-meta">
              <span class="bc-stm-type-tag bc-stm-tag--artisan">Artisan Story</span>
              <div class="bc-stm-name">Kamla Devi</div>
              <div class="bc-stm-role">Block Print Weaver</div>
              <div class="bc-stm-loc"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Jaipur, Rajasthan</div>
            </div>
          </header>
          <blockquote class="bc-stm-quote">
            <span class="bc-stm-qmark" aria-hidden="true">"</span>
            My hands carry five generations of this craft. Now my work reaches conscious homes across the world.
          </blockquote>
          <footer class="bc-stm-card-foot">
            <div class="bc-stm-badges">
              <span class="bc-stm-badge bc-stm-badge--saffron">⭐ 4.9</span>
              <span class="bc-stm-badge bc-stm-badge--white">✓ Verified</span>
            </div>
            <a href="/conscious/stories" class="bc-stm-read-link" aria-label="Read Kamla Devi's story">
              Read Story <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
          </footer>
        </article>

        <!-- Card 2: Sustainable Brand -->
        <article class="bc-stm-card bc-stm-card--brand" role="article">
          <div class="bc-stm-card-bar" aria-hidden="true"></div>
          <header class="bc-stm-card-head">
            <div class="bc-stm-avatar bc-stm-av--brand">
              <img src="assets/images/user.png" alt="Rohan Mehta, Founder EarthWeave Co." width="48" height="48" loading="lazy" />
            </div>
            <div class="bc-stm-card-meta">
              <span class="bc-stm-type-tag bc-stm-tag--brand">Sustainable Brand</span>
              <div class="bc-stm-name">Rohan Mehta</div>
              <div class="bc-stm-role">Founder, EarthWeave Co.</div>
              <div class="bc-stm-loc"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Auroville, Tamil Nadu</div>
            </div>
          </header>
          <blockquote class="bc-stm-quote">
            <span class="bc-stm-qmark" aria-hidden="true">"</span>
            We turned down funding to stay circular. Slow growth is the right growth for planet-first brands.
          </blockquote>
          <footer class="bc-stm-card-foot">
            <div class="bc-stm-badges">
              <span class="bc-stm-badge bc-stm-badge--teal">✓ Verified Brand</span>
            </div>
            <a href="/conscious/stories" class="bc-stm-read-link" aria-label="Read Rohan Mehta's story">
              Read Story <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
          </footer>
        </article>

        <!-- Card 3: Woman Entrepreneur -->
        <article class="bc-stm-card bc-stm-card--woman" role="article">
          <div class="bc-stm-card-bar" aria-hidden="true"></div>
          <header class="bc-stm-card-head">
            <div class="bc-stm-avatar bc-stm-av--woman">
              <img src="assets/images/user.png" alt="Priya Nair, Founder Coir Collective" width="48" height="48" loading="lazy" />
            </div>
            <div class="bc-stm-card-meta">
              <span class="bc-stm-type-tag bc-stm-tag--woman">Woman Entrepreneur</span>
              <div class="bc-stm-name">Priya Nair</div>
              <div class="bc-stm-role">Founder, Coir Collective</div>
              <div class="bc-stm-loc"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Thiruvananthapuram, Kerala</div>
            </div>
          </header>
          <blockquote class="bc-stm-quote">
            <span class="bc-stm-qmark" aria-hidden="true">"</span>
            I built this with ₹4,000 and a WhatsApp group. Today our collective employs 28 women artisans.
          </blockquote>
          <footer class="bc-stm-card-foot">
            <div class="bc-stm-badges">
              <span class="bc-stm-badge bc-stm-badge--rose">🏆 Top Seller</span>
            </div>
            <a href="/conscious/stories" class="bc-stm-read-link" aria-label="Read Priya Nair's story">
              Read Story <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
          </footer>
        </article>

        <!-- Card 4: Circular Economy -->
        <article class="bc-stm-card bc-stm-card--circular" role="article">
          <div class="bc-stm-card-bar" aria-hidden="true"></div>
          <header class="bc-stm-card-head">
            <div class="bc-stm-avatar bc-stm-av--circular">
              <img src="assets/images/user.png" alt="Arjun Sharma, Founder LoopLife" width="48" height="48" loading="lazy" />
            </div>
            <div class="bc-stm-card-meta">
              <span class="bc-stm-type-tag bc-stm-tag--circular">Circular Economy</span>
              <div class="bc-stm-name">Arjun Sharma</div>
              <div class="bc-stm-role">Founder, LoopLife</div>
              <div class="bc-stm-loc"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Dharavi, Mumbai</div>
            </div>
          </header>
          <blockquote class="bc-stm-quote">
            <span class="bc-stm-qmark" aria-hidden="true">"</span>
            We turn Mumbai's plastic waste into premium home goods — designed for India's conscious consumer.
          </blockquote>
          <footer class="bc-stm-card-foot">
            <div class="bc-stm-badges">
              <span class="bc-stm-badge bc-stm-badge--green">✓ Verified Brand</span>
            </div>
            <a href="/conscious/stories" class="bc-stm-read-link" aria-label="Read Arjun Sharma's story">
              Read Story <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
          </footer>
        </article>

      </div><!-- /bc-stm-grid -->
    </div><!-- /bc-stm-inner -->
  </div>
</section>


<!-- ────────────────────────────────────────────
     5. CREATOR SHOPS
     ──────────────────────────────────────────── -->
<section class="section-py bc-influencer-bg" id="creators" aria-labelledby="creators-heading">
  <div class="container">
    <div class="bc-influencer-inner">

      <!-- Visual: Creator Cards -->
      <div class="bc-influencer-visual bc-reveal-left">
        <div class="bc-influencer-cards-grid">
          <div class="bc-inf-card featured">
            <div class="bc-inf-avatar" style="background:#ffffff99;font-size:1.75rem;" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
            <div class="bc-inf-name">Meera Iyer</div>
            <div class="bc-inf-niche">Sustainability &amp; Slow Living</div>
            <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 240K Followers</div>
            <div style="margin-top:10px;font-size:0.75rem;color:rgba(255,255,255,0.55);">Earned ₹1.4L last month</div>
          </div>
          <div class="bc-inf-card">
            <div class="bc-inf-avatar" style="background:var(--saffron-pale);font-size:1.5rem;" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
            <div class="bc-inf-name">Ananya Rawat</div>
            <div class="bc-inf-niche">Slow Fashion</div>
            <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 185K Followers</div>
          </div>
          <div class="bc-inf-card">
            <div class="bc-inf-avatar" style="background:var(--green-pale);font-size:1.5rem;" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
            <div class="bc-inf-name">Kabir Sehgal</div>
            <div class="bc-inf-niche">Zero Waste Living</div>
            <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 312K Followers</div>
          </div>
          <div class="bc-inf-card">
            <div class="bc-inf-avatar" style="background:var(--navy-pale);font-size:1.5rem;" aria-hidden="true"><img src="assets/images/user.png" alt="user"></div>
            <div class="bc-inf-name">Dev Rao</div>
            <div class="bc-inf-niche">Upcycled Design</div>
            <div class="bc-inf-reach"><i class="fa-solid fa-users"></i> 220K Followers</div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="bc-influencer-content bc-reveal-right">
        <div class="bc-section-label">Creator Commerce</div>
        <h2 class="bc-section-title" id="creators-heading">Creator Shops — Curated by Sustainability Creators</h2>
        <p class="bc-section-desc">Real creators. Real reviews. Shoppable collections built around conscious living — not just content. Creator profiles, video reviews, curated collections &amp; brand collaborations, all in one shop.</p>

        <div class="bc-influencer-perks">
          <div class="bc-influencer-perk">
            <div class="bc-influencer-perk-icon"><i class="fa-solid fa-percent" aria-hidden="true"></i></div>
            <div class="bc-influencer-perk-text">
              <h6>Up to 15% Affiliate Commission</h6>
              <p>Among the highest commission rates in India's conscious living space.</p>
            </div>
          </div>
          <div class="bc-influencer-perk">
            <div class="bc-influencer-perk-icon"><i class="fa-solid fa-video" aria-hidden="true"></i></div>
            <div class="bc-influencer-perk-text">
              <h6>Shoppable Video Reviews</h6>
              <p>Publish authentic reviews that link directly to a shoppable creator collection.</p>
            </div>
          </div>
          <div class="bc-influencer-perk">
            <div class="bc-influencer-perk-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
            <div class="bc-influencer-perk-text">
              <h6>Real-time Earnings Dashboard</h6>
              <p>Track clicks, conversions &amp; payouts in a powerful creator portal.</p>
            </div>
          </div>
        </div>

        <a href="/conscious/creators" class="bc-btn bc-btn-primary bc-btn-lg">
          <i class="fa-solid fa-star"></i> Become a Creator
        </a>
        <p style="font-size:0.8rem;color:var(--text-muted);margin-top:10px;">Free to join · No minimum followers required</p>
      </div>

    </div>
  </div>
</section>





<!-- ────────────────────────────────────────────
     7. IMPACT DASHBOARD
     ──────────────────────────────────────────── -->
<section class="bc-impact-bg section-py" id="impact" aria-labelledby="impact-heading">
  <div class="container">

    <div class="bc-section-header centered bc-reveal">
      <div class="bc-section-label">Our Impact</div>
      <h2 class="bc-section-title" id="impact-heading">Building a Conscious Economy</h2>
      <p class="bc-section-desc">Every purchase, every event, every story — creating measurable change for artisans, brands &amp; the planet.</p>
    </div>

    <div class="bc-impact-grid">

      <div class="bc-artisan-stat-card bc-reveal delay-1">
        <div class="bc-impact-icon"><i class="fa-solid fa-hands-holding-circle"></i></div>
        <div class="bc-artisan-stat-num" data-count="5000" data-suffix="+">0</div>
        <div class="bc-artisan-stat-label">Artisans Supported</div>
      </div>

      <div class="bc-artisan-stat-card bc-reveal delay-2">
        <div class="bc-impact-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        <div class="bc-artisan-stat-num" data-count="2.5" data-suffix=" Cr+" data-prefix="₹" data-float="true">₹0</div>
        <div class="bc-artisan-stat-label">Revenue Generated</div>
      </div>

      <div class="bc-artisan-stat-card bc-reveal delay-3">
        <div class="bc-impact-icon"><i class="fa-solid fa-person-dress"></i></div>
        <div class="bc-artisan-stat-num" data-count="800" data-suffix="+">0</div>
        <div class="bc-artisan-stat-label">Women Entrepreneurs Onboarded</div>
      </div>

      <div class="bc-artisan-stat-card bc-reveal delay-1">
        <div class="bc-impact-icon"><i class="fa-solid fa-leaf"></i></div>
        <div class="bc-artisan-stat-num" data-count="250" data-suffix="+">0</div>
        <div class="bc-artisan-stat-label">Sustainable Brands Supported</div>
      </div>

      <div class="bc-artisan-stat-card bc-reveal delay-2">
        <div class="bc-impact-icon"><i class="fa-solid fa-people-roof"></i></div>
        <div class="bc-artisan-stat-num" data-count="48" data-suffix="+">0</div>
        <div class="bc-artisan-stat-label">Exhibitions Conducted</div>
      </div>

      <div class="bc-artisan-stat-card bc-reveal delay-3">
        <div class="bc-impact-icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="bc-artisan-stat-num" data-count="2.5" data-suffix=" Tonnes" data-float="true">0 Tonnes</div>
        <div class="bc-artisan-stat-label">Plastic Waste Avoided</div>
      </div>

    </div>

    <div class="bc-impact-cta bc-reveal">
      <a href="/conscious/impact-report" class="bc-btn bc-btn-primary bc-btn-lg">
        <i class="fa-solid fa-file-arrow-down"></i> Download Impact Report
      </a>
      <p class="bc-impact-cta-note">Full ESG metrics available for investors &amp; partners</p>
    </div>

  </div>
</section>


<!-- ────────────────────────────────────────────
     8. SUSTAINABLE CORPORATE GIFTING
     ──────────────────────────────────────────── -->
<section class="section-py bc-corp-section" id="corporate-gifting" aria-labelledby="corp-heading">
  <div class="container">
    <div class="bc-corp-inner">

      <div class="bc-reveal-left">
        <div class="bc-section-label">B2B Partnership</div>
        <h2 class="bc-section-title" id="corp-heading">Sustainable Corporate Gifting</h2>
        <p class="bc-section-desc">Transform your corporate gifting into a meaningful ESG initiative. Handcrafted, artisan-made gifts that tell a story — perfect for employees, clients &amp; conferences.</p>

        <div class="bc-corp-targets">
          <div class="bc-corp-tag"><i class="fa-solid fa-people-group"></i> HR Teams</div>
          <div class="bc-corp-tag"><i class="fa-solid fa-gift"></i> Employee Gifts</div>
          <div class="bc-corp-tag"><i class="fa-solid fa-box-open"></i> Festive Hampers</div>
          <div class="bc-corp-tag"><i class="fa-solid fa-briefcase"></i> Conference Kits</div>
          <div class="bc-corp-tag"><i class="fa-solid fa-leaf"></i> ESG Initiatives</div>
          <div class="bc-corp-tag"><i class="fa-solid fa-clipboard-check"></i> CSR Programs</div>
        </div>

        <div class="bc-corp-actions">
          <a href="/conscious/corporate-gifting" class="bc-btn bc-btn-primary bc-btn-lg">Get a Custom Quote</a>
          <a href="/conscious/corporate-catalogue" class="bc-btn bc-btn-outline bc-btn-lg">Download Catalogue</a>
        </div>
      </div>

      <div class="bc-corp-card bc-reveal-right">
        <div class="bc-corp-card-head">
          <h3>Gift With Purpose</h3>
          <p>Starting at ₹499 per gift · MOQ: 25 units</p>
        </div>
        <div class="bc-corp-pkg-list">
          <div class="bc-corp-pkg-item">
            <span class="pkg-ic">🎋</span>
            <span class="pkg-name">Artisan Essentials Kit</span>
            <span class="pkg-price">From ₹499</span>
          </div>
          <div class="bc-corp-pkg-item">
            <span class="pkg-ic">🌺</span>
            <span class="pkg-name">Wellness &amp; Self-Care Hamper</span>
            <span class="pkg-price">From ₹999</span>
          </div>
          <div class="bc-corp-pkg-item">
            <span class="pkg-ic">🏺</span>
            <span class="pkg-name">Heritage Craft Collection</span>
            <span class="pkg-price">From ₹1,499</span>
          </div>
          <div class="bc-corp-pkg-item">
            <span class="pkg-ic">✨</span>
            <span class="pkg-name">Premium Executive Hamper</span>
            <span class="pkg-price">From ₹2,999</span>
          </div>
        </div>
        <a href="/conscious/corporate-gifting" class="bc-btn-white">Request Corporate Proposal <i class="fa-solid fa-arrow-right"></i></a>
      </div>

    </div>
  </div>
</section>


<!-- ────────────────────────────────────────────
     9. JOIN THE CONSCIOUS COMMUNITY
     ──────────────────────────────────────────── -->
<section class="section-py bc-newsletter-section" id="community" aria-labelledby="community-heading">
  <div class="container">
    <div class="bc-newsletter-inner" style="max-width:880px;">

      <div class="bc-section-label">Free to Join</div>
      <h2 class="bc-section-title" id="community-heading">Join The Conscious Community</h2>
      <p class="bc-section-desc">More than a marketplace — a movement. Connect with a growing community of conscious consumers, artisans &amp; brands across India.</p>

      <div class="bc-community-grid">
        <div class="bc-community-card bc-reveal delay-1">
          <div class="bc-community-icon"><i class="fa-solid fa-microphone"></i></div>
          <h6>Live Webinars</h6>
          <p>Monthly sessions with sustainability leaders &amp; master artisans.</p>
        </div>
        <div class="bc-community-card bc-reveal delay-2">
          <div class="bc-community-icon"><i class="fa-solid fa-hands"></i></div>
          <h6>Skill Workshops</h6>
          <p>Hands-on learning from craft traditions across India.</p>
        </div>
        <div class="bc-community-card bc-reveal delay-3">
          <div class="bc-community-icon"><i class="fa-solid fa-newspaper"></i></div>
          <h6>Newsletter &amp; Blog</h6>
          <p>Weekly stories on sustainable living &amp; artisan spotlights.</p>
        </div>
        <div class="bc-community-card bc-reveal delay-1">
          <div class="bc-community-icon"><i class="fa-solid fa-people-arrows"></i></div>
          <h6>Networking</h6>
          <p>Connect with brands, artisans, creators &amp; conscious consumers.</p>
        </div>
        <div class="bc-community-card bc-reveal delay-2">
          <div class="bc-community-icon"><i class="fa-solid fa-seedling"></i></div>
          <h6>Volunteer Programs</h6>
          <p>Give back through artisan upskilling &amp; sustainability drives.</p>
        </div>
        <div class="bc-community-card bc-reveal delay-3">
          <div class="bc-community-icon"><i class="fa-solid fa-trophy"></i></div>
          <h6>Sustainability Challenges</h6>
          <p>Monthly community challenges with rewards &amp; recognition.</p>
        </div>
      </div>

      <form class="bc-newsletter-form" id="bc-newsletter-form" aria-label="Newsletter subscription form">
        <input
          type="email"
          class="bc-newsletter-input"
          id="bc-newsletter-email"
          placeholder="Enter your email address…"
          aria-label="Email address"
          required
        />
        <button type="submit" class="bc-newsletter-submit" id="bc-newsletter-submit">
          Join Free <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </button>
      </form>

      <div class="bc-newsletter-perks">
        <div class="bc-newsletter-perk"><i class="fa-solid fa-circle-check"></i> No spam, ever</div>
        <div class="bc-newsletter-perk"><i class="fa-solid fa-circle-check"></i> Unsubscribe any time</div>
        <div class="bc-newsletter-perk"><i class="fa-solid fa-circle-check"></i> Exclusive member discounts</div>
        <div class="bc-newsletter-perk"><i class="fa-solid fa-circle-check"></i> Weekly conscious living stories</div>
      </div>

    </div>
  </div>
</section>

 <!-- ────────────────────────────────────────────
     10. SELL WITH CONSCIOUS INDIA
     ──────────────────────────────────────────── --> 
<section class="section-py" id="sell" aria-labelledby="sell-heading">
  <div class="container">

    <div class="bc-section-header centered bc-reveal">
      <div class="bc-section-label">Grow With Us</div>
      <h2 class="bc-section-title" id="sell-heading">Sell With Conscious India</h2>
      <p class="bc-section-desc">Two paths, one mission — reaching conscious consumers who care about your story as much as your product.</p>
    </div>

    <div class="bc-sell-grid">

   
      <div class="bc-sell-card bc-reveal-left">
        <div class="bc-sell-head brand">
          <span class="bc-sell-tag">For Sustainable Brands</span>
          <h3>Brand Partner</h3>
          <p>Reach verified conscious consumers through marketplace, events &amp; creator networks.</p>
        </div>
        <div class="bc-sell-body">
          <div class="bc-sell-bens">
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon navy"><i class="fa-solid fa-bag-shopping"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Marketplace Access</h6>
                <p>List your products in our curated sustainable brands directory.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon navy"><i class="fa-solid fa-people-roof"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Exhibitions &amp; Pop-Ups</h6>
                <p>Premium booth space at curated conscious events across India.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon navy"><i class="fa-solid fa-star"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Creator Collaborations</h6>
                <p>Get matched with relevant sustainability creators for authentic promotion.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon navy"><i class="fa-solid fa-gift"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Corporate Gifting</h6>
                <p>Access B2B gifting orders from companies running ESG programs.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon navy"><i class="fa-solid fa-leaf"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Community Access</h6>
                <p>Engage with a growing community of conscious consumers.</p>
              </div>
            </div>
          </div>
          <a href="/conscious/become-brand-partner" class="bc-btn bc-btn-navy bc-btn-lg" style="width:100%;">Apply as a Brand</a>
        </div>
      </div>

   
      <div class="bc-sell-card bc-reveal-right">
        <div class="bc-sell-head artisan">
          <span class="bc-sell-tag">For Artisans</span>
          <h3>Artisan Partner</h3>
          <p>Take your craft to conscious consumers, with storytelling support &amp; wider platform reach.</p>
        </div>
        <div class="bc-sell-body">
          <div class="bc-sell-bens">
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon saffron"><i class="fa-solid fa-store"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Digital Storefront</h6>
                <p>Your own branded shop page — no tech knowledge required.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon saffron"><i class="fa-solid fa-people-roof"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Event Participation</h6>
                <p>Exhibit at pop-ups, artisan markets &amp; sustainability fairs.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon saffron"><i class="fa-solid fa-book-open"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Storytelling Support</h6>
                <p>Our team helps craft your artisan story for maximum impact.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon saffron"><i class="fa-solid fa-globe"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Wider Reach</h6>
                <p>Access buyers across India and international conscious markets.</p>
              </div>
            </div>
            <div class="bc-sell-ben">
              <div class="bc-sell-ben-icon saffron"><i class="fa-solid fa-hand-holding-dollar"></i></div>
              <div class="bc-sell-ben-text">
                <h6>Fair Pricing &amp; Payouts</h6>
                <p>Transparent commissions with direct bank transfers within 7 days.</p>
              </div>
            </div>
          </div>
          <a href="/conscious/become-artisan-partner" class="bc-btn bc-btn-primary bc-btn-lg" style="width:100%;">Join as an Artisan</a>
        </div>
      </div>

    </div>
  </div>
</section> 


 <!-- ────────────────────────────────────────────
     11. EXHIBIT WITH CONSCIOUS INDIA
     ────────────────────────────────────────────  -->
 <section class="bc-vendor-cta-section section-py-sm" id="exhibit" aria-labelledby="exhibit-heading">
  <div class="container">
    <div class="bc-vendor-cta-inner">

      <div class="bc-reveal-left">
        <div class="bc-section-label" style="color:rgba(255,255,255,0.8)">Exhibitor Program</div>
        <h2 class="bc-section-title" id="exhibit-heading">Exhibit With Conscious India</h2>
        <p class="bc-section-desc">Join India's fastest-growing conscious commerce events. Premium locations, high footfall, media exposure &amp; direct sales — all in one platform.</p>

        <div class="bc-vendor-cta-steps">
          <div class="bc-vendor-cta-step">
            <div class="bc-vendor-cta-step-num"><i class="fa-solid fa-location-dot" style="font-size:0.75rem"></i></div> Premium Locations
          </div>
          <div class="bc-vendor-cta-step">
            <div class="bc-vendor-cta-step-num"><i class="fa-solid fa-users" style="font-size:0.75rem"></i></div> High Footfall
          </div>
          <div class="bc-vendor-cta-step">
            <div class="bc-vendor-cta-step-num"><i class="fa-solid fa-camera" style="font-size:0.75rem"></i></div> Media Exposure
          </div>
          <div class="bc-vendor-cta-step">
            <div class="bc-vendor-cta-step-num"><i class="fa-solid fa-handshake" style="font-size:0.75rem"></i></div> Networking
          </div>
        </div>
      </div>

      <div class="bc-vendor-cta-actions bc-reveal-right">
        <a href="/conscious/become-exhibitor" class="bc-btn-white">
          <i class="fa-solid fa-people-roof"></i> Become An Exhibitor
        </a>
        <div class="bc-vendor-cta-note">
          <i class="fa-solid fa-circle-check" style="color:rgba(255,255,255,0.6)"></i>
          Conscious consumers · Influencer coverage · Sales opportunities
        </div>
      </div>

    </div>
  </div>
</section> 


</main>


<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<!-- Back to Top Button -->
<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>


<!-- ════════════════════════════════════════════
     SCRIPTS
     ════════════════════════════════════════════ -->
<!-- Bootstrap 5 JS Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Conscious India Main JS -->
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
<script src="assets/js/gsap.min.js?v=2"></script>
<script src="assets/js/ScrollTrigger.min.js?v=2"></script>
<script src="assets/js/home-editorial-sections.js?v=20261026-1"></script>
<script src="assets/js/home-hero.js?v=20261026-autoplay2"></script>
<script src="assets/js/home-maker.js?v=20261026-images1"></script>

</body>
</html>
