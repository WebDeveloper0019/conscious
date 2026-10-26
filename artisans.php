<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover India's master artisans — potters, weavers, painters, carvers and jewelers. Shop directly from the makers." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Artisans of India | Conscious India" />
  <meta property="og:description" content="Meet the master craftspeople behind India's handcrafted treasures. Shop direct from artisans." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Artisans — Conscious India | Meet India's Master Craftspeople</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
/* Artisan directory page card styles */
.bc-artisan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;}
.bc-artisan-profile-card{background:white;border:1.5px solid var(--border-warm);border-radius:var(--r-xl);overflow:hidden;display:flex;flex-direction:column;transition:all var(--t);}
.bc-artisan-profile-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-4px);border-color:rgba(255,153,51,0.3);}
.bc-artisan-cover{height:120px;position:relative;flex-shrink:0;}
.bc-artisan-body{padding:0 20px 20px;flex:1;display:flex;flex-direction:column;margin-top:-32px;}
.bc-artisan-avatar{width:64px;height:64px;border-radius:50%;border:3px solid white;flex-shrink:0;margin-bottom:10px;}
.bc-artisan-meta{display:flex;justify-content:flex-end;margin-bottom:8px;}
.bc-artisan-gen{font-size:0.6875rem;font-weight:700;padding:4px 10px;border-radius:var(--r-full);background:var(--saffron-pale);color:var(--saffron-dark);display:inline-flex;align-items:center;gap:4px;}
.bc-artisan-name{font-family:var(--font-display);font-size:1.125rem;font-weight:700;color:var(--text-primary);margin-bottom:4px;}
.bc-artisan-craft{font-size:0.8125rem;color:var(--text-muted);margin-bottom:10px;}
.bc-artisan-quote{font-size:0.8125rem;color:var(--text-secondary);line-height:1.6;font-style:italic;margin-bottom:16px;padding:10px 14px;background:var(--bg-cream);border-radius:var(--r-md);border-left:3px solid var(--saffron);flex:1;}
.bc-artisan-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;}
.bc-artisan-stat{text-align:center;padding:10px 6px;background:var(--bg-cream);border-radius:var(--r-md);}
.bc-artisan-stat-val{display:block;font-family:var(--font-display);font-size:1.125rem;font-weight:800;color:var(--saffron-dark);}
.bc-artisan-stat-key{display:block;font-size:0.6875rem;color:var(--text-muted);margin-top:2px;}
.bc-artisan-cta{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--saffron-pale);border-radius:var(--r-md);color:var(--saffron-dark);text-decoration:none;font-size:0.875rem;font-weight:600;transition:all var(--t);}
.bc-artisan-cta:hover{background:var(--saffron);color:white;}
@media(max-width:900px){.bc-artisan-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:600px){.bc-artisan-grid{grid-template-columns:1fr;}}
</style>
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

<div class="bc-breadcrumb-wrap">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artisans</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm" style="background:linear-gradient(135deg,#8B4513,#3D1A06);" aria-labelledby="artisans-heading">
  <div class="container">
    <div class="bc-reveal" style="text-align:center;color:white;">
      <div class="bc-section-label" style="color:var(--saffron-light);justify-content:center;">The Hands Behind Every Piece</div>
      <h1 class="bc-section-title" style="color:white;" id="artisans-heading">Meet India's Master Artisans</h1>
      <p class="bc-section-desc" style="color:rgba(255,255,255,0.75);margin:0 auto;">Thousands of years of craft tradition, carried forward by these extraordinary hands.</p>
    </div>
  </div>
</section>

<section class="section-py" style="background:var(--bg-cream);">
  <div class="container">
    <!-- Filter Pills -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:32px;padding:16px 20px;background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);">
      <span style="font-size:0.8125rem;font-weight:600;color:var(--text-muted);align-self:center;margin-right:4px;">Filter by Craft:</span>
      <button class="bc-event-tab active bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">All</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Pottery</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Weaving</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Jewelry</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Painting</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Wood Carving</button>
      <button class="bc-event-tab bc-btn-sm" style="border-radius:var(--r-full);padding:6px 16px;">Embroidery</button>
      <span style="font-size:0.8125rem;font-weight:600;color:var(--text-muted);align-self:center;margin-left:12px;margin-right:4px;">State:</span>
      <select style="border:1.5px solid var(--border-warm);border-radius:var(--r-full);padding:6px 14px;font-size:0.8125rem;background:var(--bg-cream);color:var(--text-secondary);">
        <option>All States</option>
        <option>Rajasthan</option><option>Uttar Pradesh</option><option>West Bengal</option>
        <option>Gujarat</option><option>Kashmir</option><option>Tamil Nadu</option>
      </select>
    </div>

    <!-- Artisan Grid -->
    <div class="bc-artisan-grid">

      <article class="bc-artisan-profile-card bc-reveal" aria-label="Artisan: Rajan Kumhar">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#C88422,#4A210D);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">🏺</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,var(--saffron),var(--gold));color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🧑</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen"><i class="fa-solid fa-circle-check"></i> Verified</span>
          </div>
          <h3 class="bc-artisan-name">Rajan Kumhar</h3>
          <div class="bc-artisan-craft">Master Potter · Jaipur, Rajasthan</div>
          <p class="bc-artisan-quote">"Every vase I throw carries the spirit of my ancestors — 400 years of Blue Pottery runs through my hands."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">28</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">87</div><div class="bc-artisan-stat-key">Products</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">4.9★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

      <article class="bc-artisan-profile-card bc-reveal delay-1" aria-label="Artisan: Kamla Devi">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#FF6B6B,#8B0000);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">🎨</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,#FF6B6B,#C0392B);color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👩</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen" style="background:var(--saffron-pale);color:var(--saffron-dark);"><i class="fa-solid fa-female"></i> Woman Artisan</span>
          </div>
          <h3 class="bc-artisan-name">Kamla Devi</h3>
          <div class="bc-artisan-craft">Madhubani Painter · Mithila, Bihar</div>
          <p class="bc-artisan-quote">"My mother taught me. Her mother taught her. Now I teach my daughters — the stories must live on."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">35</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">52</div><div class="bc-artisan-stat-key">Works</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">5.0★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

      <article class="bc-artisan-profile-card bc-reveal delay-2" aria-label="Artisan: Mohammad Yusuf">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#C88422,#4A3200);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">💍</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,var(--gold),var(--gold-dark));color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🧔</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen" style="background:var(--gold-pale);color:var(--gold-dark);"><i class="fa-solid fa-award"></i> National Award</span>
          </div>
          <h3 class="bc-artisan-name">Mohammad Yusuf</h3>
          <div class="bc-artisan-craft">Silver Filigree Jeweler · Karimnagar, Telangana</div>
          <p class="bc-artisan-quote">"I turn silver wire thinner than hair into flowers you can wear. It takes a lifetime to learn this patience."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">42</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">134</div><div class="bc-artisan-stat-key">Pieces</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">4.9★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

      <article class="bc-artisan-profile-card bc-reveal" aria-label="Artisan: Savitri Meghwal">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#6A0DAD,#2A0045);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">🪡</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,#6A0DAD,#4A0080);color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👵</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen" style="background:var(--saffron-pale);color:var(--saffron-dark);"><i class="fa-solid fa-female"></i> Woman Artisan</span>
          </div>
          <h3 class="bc-artisan-name">Savitri Meghwal</h3>
          <div class="bc-artisan-craft">Kantha Embroiderer · Barmer, Rajasthan</div>
          <p class="bc-artisan-quote">"Each kantha stitch is a prayer. I embroider my hopes into every cloth for the family that will use it."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">22</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">68</div><div class="bc-artisan-stat-key">Works</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">4.8★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

      <article class="bc-artisan-profile-card bc-reveal delay-1" aria-label="Artisan: Kiran Lal">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#1A5C1A,#0A2A0A);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">🪵</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,var(--green),#0A4A0A);color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🧑</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen"><i class="fa-solid fa-circle-check"></i> Verified</span>
          </div>
          <h3 class="bc-artisan-name">Kiran Lal</h3>
          <div class="bc-artisan-craft">Wood Carver · Saharanpur, UP</div>
          <p class="bc-artisan-quote">"I can hear what the wood wants to become. My chisel just helps it get there."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">18</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">93</div><div class="bc-artisan-stat-key">Pieces</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">4.7★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

      <article class="bc-artisan-profile-card bc-reveal delay-2" aria-label="Artisan: Fatima Bibi">
        <div class="bc-artisan-cover" style="background:linear-gradient(135deg,#0A3A6E,#30391B);">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:4rem;opacity:0.3;">🧵</div>
        </div>
        <div class="bc-artisan-body">
          <div class="bc-artisan-avatar" style="background:linear-gradient(135deg,var(--navy),#30391B);color:white;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👩</div>
          <div class="bc-artisan-meta">
            <span class="bc-artisan-gen" style="background:var(--navy-pale);color:var(--navy);"><i class="fa-solid fa-female"></i> Woman Weaver</span>
          </div>
          <h3 class="bc-artisan-name">Fatima Bibi</h3>
          <div class="bc-artisan-craft">Kashmiri Weaver · Srinagar, J&amp;K</div>
          <p class="bc-artisan-quote">"A Pashmina shawl takes three months of my life. When you wear it, carry that time with care."</p>
          <div class="bc-artisan-stats">
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">31</div><div class="bc-artisan-stat-key">Years</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">47</div><div class="bc-artisan-stat-key">Shawls</div></div>
            <div class="bc-artisan-stat"><div class="bc-artisan-stat-val">5.0★</div><div class="bc-artisan-stat-key">Rating</div></div>
          </div>
          <a href="/conscious/artisan-profile" class="bc-artisan-cta">View Profile &amp; Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      </article>

    </div>
    <div style="text-align:center;margin-top:40px;">
      <button class="bc-btn bc-btn-outline"><i class="fa-solid fa-plus"></i> Load More Artisans</button>
    </div>
  </div>
</section>

<section class="section-py" style="background:linear-gradient(135deg,var(--navy),var(--navy-dark));" aria-label="Become artisan partner">
  <div class="container">
    <div class="bc-reveal" style="text-align:center;color:white;">
      <div class="bc-section-label" style="color:var(--saffron-light);justify-content:center;">Join Our Community</div>
      <h2 class="bc-section-title" style="color:white;">Are You an Artisan?</h2>
      <p class="bc-section-desc" style="color:rgba(255,255,255,0.75);margin:0 auto 28px;">Join 1,200+ verified artisans already selling on Conscious India. No listing fees, no commissions until you sell.</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
        <a href="/conscious/become-artisan-partner" class="bc-btn bc-btn-primary"><i class="fa-solid fa-hands"></i> Apply as Artisan</a>
        <a href="/conscious/seller-guidelines" class="bc-btn bc-btn-outline-white">Read Guidelines</a>
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