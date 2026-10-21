<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Review your selected handcrafted products and proceed to checkout. Secure payments, easy returns." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Shopping Cart | Conscious Indi" />
  <meta property="og:description" content="Review your cart and checkout securely. Handcrafted products from verified Indian artisans." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Cart — Conscious Indi | Your Shopping Cart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Cart layout ===== */
.cart-main-grid{display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start;}
.cart-sidebar-sticky{position:sticky;top:80px;}

/* ===== Cart item rows ===== */
.cart-item-row{padding:24px;display:grid;grid-template-columns:90px 1fr auto;gap:20px;align-items:start;border-bottom:1px solid var(--border-light);}
.cart-item-row:last-child{border-bottom:none;}
.cart-item-thumb{width:90px;height:90px;border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;font-size:2.5rem;flex-shrink:0;}
.cart-item-price-col{text-align:right;}

/* ===== Responsive breakpoints ===== */
@media (max-width:991px){
  .cart-main-grid{grid-template-columns:1fr;gap:24px;}
  .cart-sidebar-sticky{position:static;top:auto;}
}
@media (max-width:575px){
  .cart-item-row{grid-template-columns:72px 1fr;padding:16px;gap:14px;}
  .cart-item-thumb{width:72px;height:72px;font-size:2rem;}
  .cart-item-price-col{grid-column:1 / -1;text-align:left;display:flex;justify-content:space-between;align-items:center;margin-top:8px;}
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
            <li class="breadcrumb-item active" aria-current="page">Cart</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Shopping cart">
  <div class="container">
    <div class="bc-section-header">
      <div class="bc-section-label">Your Selection</div>
      <h1 class="bc-section-title" id="cart-heading">Shopping Cart <span style="font-size:1rem;color:var(--text-muted);font-weight:400;">(3 items)</span></h1>
    </div>
    <div class="cart-main-grid">

      <!-- Cart Items -->
      <div class="bc-reveal-left">
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);overflow:hidden;">
          <!-- Item 1 -->
          <div class="cart-item-row">
            <div class="cart-item-thumb" style="background:linear-gradient(135deg,#C88422,#4A210D);">🏺</div>
            <div>
              <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:4px;color:var(--text-primary);">Blue Pottery Vase — Jaipur Heritage</h3>
              <p style="font-size:0.8125rem;color:var(--text-muted);margin-bottom:8px;">by Rajan Kumhar · Size: Medium</p>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="bc-badge bc-badge-green" style="font-size:0.625rem;"><i class="fa-solid fa-circle-check"></i> In Stock</span>
                <button style="font-size:0.75rem;color:var(--saffron-dark);background:none;border:none;cursor:pointer;"><i class="fa-regular fa-heart"></i> Save</button>
                <button style="font-size:0.75rem;color:#E53E3E;background:none;border:none;cursor:pointer;"><i class="fa-regular fa-trash-can"></i> Remove</button>
              </div>
            </div>
            <div class="cart-item-price-col">
              <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:10px;">₹1,299</div>
              <div style="display:flex;align-items:center;border:1.5px solid var(--border-warm);border-radius:var(--r-md);overflow:hidden;">
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">−</button>
                <span style="width:36px;text-align:center;font-weight:600;font-size:0.9375rem;">1</span>
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">+</button>
              </div>
            </div>
          </div>
          <!-- Item 2 -->
          <div class="cart-item-row">
            <div class="cart-item-thumb" style="background:linear-gradient(135deg,#C88422,#6B4E08);">💍</div>
            <div>
              <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:4px;color:var(--text-primary);">Kundan Meenakari Necklace Set</h3>
              <p style="font-size:0.8125rem;color:var(--text-muted);margin-bottom:8px;">by Silver Craft Studios · Color: Gold</p>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="bc-badge bc-badge-green" style="font-size:0.625rem;"><i class="fa-solid fa-circle-check"></i> In Stock</span>
                <button style="font-size:0.75rem;color:var(--saffron-dark);background:none;border:none;cursor:pointer;"><i class="fa-regular fa-heart"></i> Save</button>
                <button style="font-size:0.75rem;color:#E53E3E;background:none;border:none;cursor:pointer;"><i class="fa-regular fa-trash-can"></i> Remove</button>
              </div>
            </div>
            <div class="cart-item-price-col">
              <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:10px;">₹3,499</div>
              <div style="display:flex;align-items:center;border:1.5px solid var(--border-warm);border-radius:var(--r-md);overflow:hidden;">
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">−</button>
                <span style="width:36px;text-align:center;font-weight:600;font-size:0.9375rem;">1</span>
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">+</button>
              </div>
            </div>
          </div>
          <!-- Item 3 -->
          <div class="cart-item-row">
            <div class="cart-item-thumb" style="background:linear-gradient(135deg,#4B572C,#1A4228);">🌿</div>
            <div>
              <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:4px;color:var(--text-primary);">Bamboo Dining Set — Zero Waste</h3>
              <p style="font-size:0.8125rem;color:var(--text-muted);margin-bottom:8px;">by EarthWeave Co.</p>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="bc-badge bc-badge-green" style="font-size:0.625rem;"><i class="fa-solid fa-circle-check"></i> In Stock</span>
                <button style="font-size:0.75rem;color:var(--saffron-dark);background:none;border:none;cursor:pointer;"><i class="fa-regular fa-heart"></i> Save</button>
                <button style="font-size:0.75rem;color:#E53E3E;background:none;border:none;cursor:pointer;"><i class="fa-regular fa-trash-can"></i> Remove</button>
              </div>
            </div>
            <div class="cart-item-price-col">
              <div style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:10px;">₹899</div>
              <div style="display:flex;align-items:center;border:1.5px solid var(--border-warm);border-radius:var(--r-md);overflow:hidden;">
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">−</button>
                <span style="width:36px;text-align:center;font-weight:600;font-size:0.9375rem;">2</span>
                <button style="width:32px;height:32px;font-size:1rem;background:var(--bg-cream);border:none;cursor:pointer;color:var(--text-secondary);">+</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Coupon -->
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:20px;margin-top:16px;display:flex;gap:12px;">
          <i class="fa-solid fa-tag" style="color:var(--saffron);font-size:1.25rem;margin-top:2px;"></i>
          <div style="flex:1;">
            <div style="font-weight:600;color:var(--text-primary);margin-bottom:8px;">Apply Coupon / Gift Card</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <input type="text" placeholder="Enter coupon code" style="flex:1;min-width:150px;padding:10px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.875rem;background:var(--bg-cream);" />
              <button class="bc-btn bc-btn-outline bc-btn-sm">Apply</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="bc-reveal-right cart-sidebar-sticky">
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:24px;">
          <h2 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;margin-bottom:20px;color:var(--text-primary);padding-bottom:12px;border-bottom:1px solid var(--border-light);">Order Summary</h2>
          <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--text-secondary);"><span>Subtotal (4 items)</span><span style="font-weight:600;">₹6,596</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--text-secondary);"><span>Delivery</span><span style="color:var(--green);font-weight:600;">Free</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--green);"><span><i class="fa-solid fa-tag"></i> Discount (FIRST10)</span><span>− ₹659</span></div>
            <div style="height:1px;background:var(--border-light);"></div>
            <div style="display:flex;justify-content:space-between;font-size:1.125rem;font-weight:700;color:var(--text-primary);"><span>Total</span><span>₹5,937</span></div>
            <div style="font-size:0.75rem;color:var(--text-muted);">Inclusive of GST. You save ₹659 on this order.</div>
          </div>
          <a href="frontend/modules/customer/checkout.php" class="bc-btn bc-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:15px;">
            <i class="fa-solid fa-lock"></i> Proceed to Checkout
          </a>
          <div style="text-align:center;margin-top:12px;font-size:0.75rem;color:var(--text-muted);">
            <i class="fa-solid fa-shield-halved" style="color:var(--green);"></i> 100% Secure &amp; Encrypted Payment
          </div>
          <div style="display:flex;justify-content:center;gap:6px;margin-top:12px;flex-wrap:wrap;">
            <span class="bc-payment-badge" style="background:var(--bg-cream);color:var(--text-muted);">UPI</span>
            <span class="bc-payment-badge" style="background:var(--bg-cream);color:var(--text-muted);">Visa</span>
            <span class="bc-payment-badge" style="background:var(--bg-cream);color:var(--text-muted);">Mastercard</span>
            <span class="bc-payment-badge" style="background:var(--bg-cream);color:var(--text-muted);">RuPay</span>
          </div>
        </div>

        <!-- Artisan Impact -->
        <div style="background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:var(--r-lg);padding:20px;margin-top:16px;color:white;">
          <div style="font-size:0.875rem;font-weight:700;margin-bottom:8px;"><i class="fa-solid fa-leaf" style="color:var(--saffron-light);"></i> Your Conscious Impact</div>
          <p style="font-size:0.8125rem;color:rgba(255,255,255,0.75);line-height:1.6;">This purchase directly supports 3 artisan families and contributes to sustainable craft preservation in India.</p>
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