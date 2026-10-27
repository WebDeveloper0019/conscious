<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Shop authentic handcrafted handmade jewelry from verified Indian artisans on Conscious India." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Handmade Jewelry | Conscious India" />
  <meta property="og:description" content="Authentic handcrafted handmade jewelry from India's finest artisans." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Handmade Jewelry — Conscious India | Handcrafted Indian Handmade Jewelry</title>
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
            <li class="breadcrumb-item active" aria-current="page">Handmade Jewelry</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm" style="background:linear-gradient(135deg,#C88422,var(--navy-dark));" aria-labelledby="cat-heading">
  <div class="container">
    <div style="text-align:center;color:white;padding:20px 0;" class="bc-reveal">
      <div style="font-size:4rem;margin-bottom:16px;">💍</div>
      <h1 style="font-family:var(--font-display);font-size:clamp(2rem,4vw,3rem);font-weight:800;color:white;margin-bottom:12px;" id="cat-heading">Handmade Jewelry</h1>
      <p style="font-size:1.0625rem;color:rgba(255,255,255,0.8);max-width:540px;margin:0 auto 20px;">Exquisite handcrafted jewelry — Kundan, Meenakari, Dhokra, silver filigree and more.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <span style="padding:6px 16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:var(--r-full);font-size:0.8125rem;color:white;">420+ Products</span>
        <span style="padding:6px 16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:var(--r-full);font-size:0.8125rem;color:white;">✓ All Verified Artisans</span>
        <span style="padding:6px 16px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:var(--r-full);font-size:0.8125rem;color:white;">Free Shipping ₹999+</span>
      </div>
    </div>
  </div>
</section>

<!-- Filter + Product Grid -->
<section class="section-py" style="background:var(--bg-cream);" aria-label="Shop products">
  <div class="container">
    <div style="display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start;">

      <!-- Sidebar Filters -->
      <aside class="bc-reveal-left" aria-label="Product filters" style="position:sticky;top:80px;">
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:24px;">
          <h2 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;margin-bottom:20px;color:var(--text-primary);padding-bottom:12px;border-bottom:1px solid var(--border-light);">
            <i class="fa-solid fa-sliders" style="color:var(--saffron);margin-right:8px;"></i> Filters
          </h2>

          <!-- Category Filter -->
          <div style="margin-bottom:24px;">
            <h3 style="font-size:0.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-primary);margin-bottom:12px;">Category</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;">
                <input type="checkbox" checked style="accent-color:var(--saffron);"> All Products
              </label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Pottery &amp; Ceramics</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Handmade Jewelry</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Handloom &amp; Textiles</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Paintings</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Wood Crafts</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Home Decor</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Traditional Art</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Eco Friendly</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Fashion Accessories</label>
            </div>
          </div>

          <!-- Price Range -->
          <div style="margin-bottom:24px;">
            <h3 style="font-size:0.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-primary);margin-bottom:12px;">Price Range</h3>
            <div style="display:flex;gap:8px;margin-bottom:10px;">
              <input type="number" placeholder="Min ₹" style="flex:1;padding:8px 10px;border:1.5px solid var(--border-warm);border-radius:var(--r-sm);font-size:0.875rem;color:var(--text-primary);background:var(--bg-cream);width:100%;" />
              <input type="number" placeholder="Max ₹" style="flex:1;padding:8px 10px;border:1.5px solid var(--border-warm);border-radius:var(--r-sm);font-size:0.875rem;color:var(--text-primary);background:var(--bg-cream);width:100%;" />
            </div>
            <input type="range" min="0" max="50000" value="25000" style="width:100%;accent-color:var(--saffron);" />
            <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-top:4px;"><span>₹0</span><span>₹50,000</span></div>
          </div>

          <!-- Rating -->
          <div style="margin-bottom:24px;">
            <h3 style="font-size:0.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-primary);margin-bottom:12px;">Customer Rating</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="radio" name="rating" style="accent-color:var(--saffron);"> ★★★★★ &nbsp;(4.5+)</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="radio" name="rating" style="accent-color:var(--saffron);"> ★★★★☆ &nbsp;(4.0+)</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="radio" name="rating" style="accent-color:var(--saffron);"> ★★★☆☆ &nbsp;(3.0+)</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="radio" name="rating" checked style="accent-color:var(--saffron);"> All Ratings</label>
            </div>
          </div>

          <!-- Brand Type -->
          <div style="margin-bottom:24px;">
            <h3 style="font-size:0.8125rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-primary);margin-bottom:12px;">Seller Type</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Verified Artisans</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> Sustainable Brands</label>
              <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;"><input type="checkbox" style="accent-color:var(--saffron);"> GI Tagged Products</label>
            </div>
          </div>

          <button class="bc-btn bc-btn-primary" style="width:100%;">Apply Filters</button>
          <button class="bc-btn bc-btn-outline" style="width:100%;margin-top:8px;">Clear All</button>
        </div>
      </aside>

      <!-- Product Grid -->
      <div>
        <!-- Sort Bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding:14px 20px;background:white;border-radius:var(--r-md);border:1.5px solid var(--border-warm);">
          <span style="font-size:0.875rem;color:var(--text-muted);">Showing <strong style="color:var(--text-primary);">248 products</strong></span>
          <div style="display:flex;align-items:center;gap:12px;">
            <label style="font-size:0.875rem;color:var(--text-muted);">Sort by:</label>
            <select style="border:1.5px solid var(--border-warm);border-radius:var(--r-sm);padding:6px 12px;font-size:0.875rem;color:var(--text-secondary);background:var(--bg-cream);">
              <option>Featured</option>
              <option>Price: Low to High</option>
              <option>Price: High to Low</option>
              <option>Newest First</option>
              <option>Best Rated</option>
              <option>Best Selling</option>
            </select>
            <div style="display:flex;gap:4px;">
              <button style="width:32px;height:32px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--saffron);color:white;display:flex;align-items:center;justify-content:center;" aria-label="Grid view"><i class="fa-solid fa-grid-2"></i></button>
              <button style="width:32px;height:32px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);display:flex;align-items:center;justify-content:center;" aria-label="List view"><i class="fa-solid fa-list"></i></button>
            </div>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bc-product-tabs" style="margin-bottom:20px;">
          <button class="bc-product-tab active" data-filter="all">All</button>
          <button class="bc-product-tab" data-filter="pottery">Pottery</button>
          <button class="bc-product-tab" data-filter="jewelry">Jewelry</button>
          <button class="bc-product-tab" data-filter="handloom">Handloom</button>
          <button class="bc-product-tab" data-filter="paintings">Paintings</button>
          <button class="bc-product-tab" data-filter="wood">Wood Crafts</button>
          <button class="bc-product-tab" data-filter="eco">Eco</button>
        </div>

        <!-- Products -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
          <!-- Product Cards -->
          <article class="bc-product-card bc-reveal" data-category="pottery">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🏺</div>
              <span class="bc-product-badge bc-product-badge-new">New</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Rajan Kumhar</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Blue Pottery Vase — Jaipur Craft</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.9</span><span class="bc-product-rating-count">(87)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,299</span> <span class="bc-product-price-original">₹1,799</span> <span class="bc-product-price-off">28% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-1" data-category="jewelry">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">💍</div>
              <span class="bc-product-badge bc-product-badge-hot">Hot</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--gold-pale);">🧑</div><span class="bc-product-artisan-name">by Silver Craft Studios</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Kundan Meenakari Necklace Set</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span><span class="bc-product-rating-count">(143)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹3,499</span> <span class="bc-product-price-original">₹4,999</span> <span class="bc-product-price-off">30% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-2" data-category="handloom">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🧵</div>
              <span class="bc-product-badge bc-product-badge-new">New</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--navy-pale);">🧑</div><span class="bc-product-artisan-name">by Weavers of Varanasi</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Banarasi Silk Dupatta — Handwoven</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span><span class="bc-product-rating-count">(92)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹2,199</span> <span class="bc-product-price-original">₹2,999</span> <span class="bc-product-price-off">27% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal" data-category="paintings">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-4" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🎨</div>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Madhubani Masters</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Madhubani Painting — Mithila Folk Art</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">5.0</span><span class="bc-product-rating-count">(56)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹4,500</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-1" data-category="wood">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-5" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🪵</div>
              <span class="bc-product-badge bc-product-badge-sale">Sale</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--green-pale);">🧑</div><span class="bc-product-artisan-name">by Saharanpur Woodcraft</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Sheesham Wood Carved Box — Intricate</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★☆</div><span class="bc-product-rating-val">4.6</span><span class="bc-product-rating-count">(74)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,799</span> <span class="bc-product-price-original">₹2,499</span> <span class="bc-product-price-off">28% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-2" data-category="eco">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-6" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🌿</div>
              <span class="bc-product-badge bc-product-badge-new">Eco</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button><button class="bc-product-action-btn" aria-label="Quick view"><i class="fa-regular fa-eye"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--green-pale);">🧑</div><span class="bc-product-artisan-name">by EarthWeave Co.</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Bamboo Dining Set — Zero Waste</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.9</span><span class="bc-product-rating-count">(211)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹899</span> <span class="bc-product-price-original">₹1,299</span> <span class="bc-product-price-off">31% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal" data-category="pottery">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-7" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🫖</div>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Khurja Pottery</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Hand-painted Terracotta Tea Set</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span><span class="bc-product-rating-count">(63)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,599</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-1" data-category="jewelry">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-8" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">📿</div>
              <span class="bc-product-badge bc-product-badge-hot">Hot</span>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--gold-pale);">🧑</div><span class="bc-product-artisan-name">by Tribal Adornments</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Dhokra Brass Tribal Necklace</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span><span class="bc-product-rating-count">(38)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹2,799</span> <span class="bc-product-price-original">₹3,599</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>

          <article class="bc-product-card bc-reveal delay-2" data-category="handloom">
            <div class="bc-product-img-wrap"><div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:4rem;">🧶</div>
              <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
            </div>
            <div class="bc-product-body">
              <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--navy-pale);">🧑</div><span class="bc-product-artisan-name">by Kashmir Looms</span></div>
              <h3 class="bc-product-title"><a href="/conscious/product-detail">Pure Pashmina Shawl — Hand-embroidered</a></h3>
              <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.9</span><span class="bc-product-rating-count">(182)</span></div>
              <div class="bc-product-price-row"><div><span class="bc-product-price">₹8,999</span> <span class="bc-product-price-original">₹12,000</span> <span class="bc-product-price-off">25% off</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
            </div>
          </article>
        </div>

        <!-- Pagination -->
        <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:40px;">
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-chevron-left"></i></button>
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--saffron);background:var(--saffron);color:white;font-weight:600;">1</button>
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);color:var(--text-secondary);cursor:pointer;">2</button>
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);color:var(--text-secondary);cursor:pointer;">3</button>
          <span style="color:var(--text-muted);">...</span>
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);color:var(--text-secondary);cursor:pointer;">12</button>
          <button style="width:38px;height:38px;border-radius:var(--r-sm);border:1.5px solid var(--border-warm);background:var(--bg-cream);color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Handmade Jewelry products">
  <div class="container">
    <div class="bc-product-tabs" style="margin-bottom:24px;">
      <button class="bc-product-tab active" data-filter="all">All Handmade Jewelry</button>
      <button class="bc-product-tab" data-filter="jewelry">Bestsellers</button>
      <button class="bc-product-tab" data-filter="jewelry">New Arrivals</button>
      <button class="bc-product-tab" data-filter="jewelry">GI Tagged</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
      <article class="bc-product-card bc-reveal" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-1" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">💍</div>
          <span class="bc-product-badge bc-product-badge-new">New</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Master Artisan</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Handcrafted Handmade Jewelry Piece I</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span><span class="bc-product-rating-count">(40)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,299</span><span class="bc-product-price-original">₹1,799</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-2" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-2" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">🎨</div>
          <span class="bc-product-badge bc-product-badge-hot">Hot</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Craft Studio</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Premium Handmade Jewelry Collection</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span><span class="bc-product-rating-count">(53)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹2,499</span><span class="bc-product-price-original">₹3,299</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-3" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-3" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">💍</div>
          <span class="bc-product-badge bc-product-badge-sale">Sale</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Local Artisan</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Traditional Handmade Jewelry — Heritage</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span><span class="bc-product-rating-count">(66)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹899</span><span class="bc-product-price-original">₹1,199</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-4" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-4" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">✨</div>
          <span class="bc-product-badge bc-product-badge-new">New</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Artisan Guild</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Handmade Handmade Jewelry Gift Set</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span><span class="bc-product-rating-count">(79)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,599</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-1" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-5" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">💍</div>
          <span class="bc-product-badge bc-product-badge-hot">Hot</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by Master Craftsman</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Premium Handmade Jewelry — Limited Ed.</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.8</span><span class="bc-product-rating-count">(92)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹3,299</span><span class="bc-product-price-original">₹4,499</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
      <article class="bc-product-card bc-reveal delay-2" data-category="jewelry">
        <div class="bc-product-img-wrap">
          <div class="bc-product-img bc-prod-bg-6" style="display:flex;align-items:center;justify-content:center;font-size:3.5rem;">🌿</div>
          <span class="bc-product-badge bc-product-badge-new">New</span>
          <div class="bc-product-actions"><button class="bc-product-action-btn wishlist" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></button></div>
        </div>
        <div class="bc-product-body">
          <div class="bc-product-artisan"><div class="bc-product-artisan-dot" style="background:var(--saffron-pale);">🧑</div><span class="bc-product-artisan-name">by EarthWeave Co.</span></div>
          <h3 class="bc-product-title"><a href="/conscious/product-detail">Eco Handmade Jewelry — Sustainable</a></h3>
          <div class="bc-product-rating"><div class="bc-stars">★★★★★</div><span class="bc-product-rating-val">4.7</span><span class="bc-product-rating-count">(105)</span></div>
          <div class="bc-product-price-row"><div><span class="bc-product-price">₹1,099</span><span class="bc-product-price-original">₹1,399</span></div><button class="bc-product-add-btn" aria-label="Add to cart"><i class="fa-solid fa-plus"></i></button></div>
        </div>
      </article>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="/conscious/shop" class="bc-btn bc-btn-navy"><i class="fa-solid fa-store"></i> View All Products in Shop</a>
    </div>
  </div>
</section>

<section class="section-py bg-cream" aria-label="About Handmade Jewelry">
  <div class="container" style="max-width:800px;">
    <div class="bc-reveal">
      <h2 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">About Handmade Jewelry in India</h2>
      <p style="font-size:1rem;color:var(--text-secondary);line-height:1.8;margin-bottom:16px;">Indian jewelry-making is one of the world's oldest art forms. From Rajasthan's Kundan and Meenakari work to Odisha's Dhokra cast metal jewelry, each piece tells a cultural story.</p>
      <a href="/conscious/artisans" class="bc-btn bc-btn-outline"><i class="fa-solid fa-hands"></i> Meet Our Artisans</a>
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