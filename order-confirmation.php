<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Your order has been placed successfully. Track your handcrafted product delivery from Conscious Indi." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Order Confirmed | Conscious Indi" />
  <meta property="og:description" content="Your order is confirmed! Track delivery of your handcrafted Indian products." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Order Confirmed — Conscious Indi | Thank You!</title>
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
            <li class="breadcrumb-item"><a href="frontend/modules/customer/cart.php">Cart</a></li>
            <li class="breadcrumb-item"><a href="frontend/modules/customer/checkout.php">Checkout</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order Confirmed</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Order confirmed">
  <div class="container" style="max-width:700px;">
    <div class="bc-reveal" style="text-align:center;">
      <!-- Success Animation -->
      <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green-light));display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 8px 32px rgba(19,136,8,0.3);">
        <i class="fa-solid fa-check" style="font-size:2.5rem;color:white;"></i>
      </div>
      <div class="bc-section-label" style="justify-content:center;color:var(--green-dark);">Order Placed Successfully!</div>
      <h1 style="font-family:var(--font-display);font-size:clamp(1.75rem,4vw,2.5rem);font-weight:800;color:var(--text-primary);margin-bottom:12px;">
        Thank you, Priya! 🎉
      </h1>
      <p style="font-size:1.0625rem;color:var(--text-muted);margin-bottom:32px;line-height:1.7;">
        Your order has been placed and is being processed. You will receive a confirmation email and SMS shortly.
      </p>

      <!-- Order Info Box -->
      <div style="background:white;border-radius:var(--r-xl);border:1.5px solid var(--border-warm);padding:28px;margin-bottom:28px;text-align:left;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border-light);">
          <div><div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Order ID</div><div style="font-weight:700;color:var(--navy);font-size:1rem;font-family:var(--font-display);">#CI-2025-084721</div></div>
          <div><div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Date</div><div style="font-weight:600;color:var(--text-primary);">15 January 2025</div></div>
          <div><div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Payment</div><div style="font-weight:600;color:var(--text-primary);">UPI — Success ✓</div></div>
          <div><div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:6px;">Expected Delivery</div><div style="font-weight:600;color:var(--green);">22–24 January 2025</div></div>
        </div>

        <!-- Items Summary -->
        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border-light);">
          <div style="display:flex;gap:14px;align-items:center;">
            <div style="width:56px;height:56px;border-radius:var(--r-md);background:linear-gradient(135deg,#C88422,#4A210D);display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;">🏺</div>
            <div style="flex:1;"><div style="font-weight:600;color:var(--text-primary);font-size:0.9375rem;">Blue Pottery Vase — Jaipur Heritage</div><div style="font-size:0.8125rem;color:var(--text-muted);">by Rajan Kumhar · Qty: 1</div></div>
            <div style="font-weight:700;color:var(--text-primary);">₹1,299</div>
          </div>
          <div style="display:flex;gap:14px;align-items:center;">
            <div style="width:56px;height:56px;border-radius:var(--r-md);background:linear-gradient(135deg,#C88422,#6B4E08);display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;">💍</div>
            <div style="flex:1;"><div style="font-weight:600;color:var(--text-primary);font-size:0.9375rem;">Kundan Meenakari Necklace Set</div><div style="font-size:0.8125rem;color:var(--text-muted);">by Silver Craft Studios · Qty: 1</div></div>
            <div style="font-weight:700;color:var(--text-primary);">₹3,499</div>
          </div>
          <div style="display:flex;gap:14px;align-items:center;">
            <div style="width:56px;height:56px;border-radius:var(--r-md);background:linear-gradient(135deg,#4B572C,#1A4228);display:flex;align-items:center;justify-content:center;font-size:1.75rem;flex-shrink:0;">🌿</div>
            <div style="flex:1;"><div style="font-weight:600;color:var(--text-primary);font-size:0.9375rem;">Bamboo Dining Set — Zero Waste</div><div style="font-size:0.8125rem;color:var(--text-muted);">by EarthWeave Co. · Qty: 2</div></div>
            <div style="font-weight:700;color:var(--text-primary);">₹1,798</div>
          </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:1rem;font-weight:600;color:var(--text-secondary);">Total Paid</span>
          <span style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;color:var(--text-primary);">₹5,937</span>
        </div>
      </div>

      <!-- Delivery Address -->
      <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:20px;margin-bottom:28px;text-align:left;display:flex;gap:14px;">
        <div style="width:40px;height:40px;border-radius:var(--r-sm);background:var(--saffron-pale);display:flex;align-items:center;justify-content:center;color:var(--saffron-dark);flex-shrink:0;">
          <i class="fa-solid fa-location-dot"></i>
        </div>
        <div>
          <div style="font-weight:700;color:var(--text-primary);margin-bottom:4px;">Delivering to</div>
          <div style="font-size:0.9375rem;color:var(--text-secondary);line-height:1.6;">Priya Sharma<br/>42, Banjara Hills, Near Apollo Hospital<br/>Hyderabad, Telangana – 500034</div>
        </div>
      </div>

      <!-- CTAs -->
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="frontend/modules/customer/tracking.php" class="bc-btn bc-btn-primary"><i class="fa-regular fa-clock"></i> Track Your Order</a>
        <a href="/conscious/shop" class="bc-btn bc-btn-navy"><i class="fa-solid fa-bag-shopping"></i> Continue Shopping</a>
        <a href="frontend/modules/customer/orders.php" class="bc-btn bc-btn-outline"><i class="fa-regular fa-file-lines"></i> View All Orders</a>
      </div>

      <!-- Conscious Impact -->
      <div style="margin-top:40px;padding:24px;background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:var(--r-xl);color:white;text-align:left;">
        <div style="font-size:1rem;font-weight:700;margin-bottom:8px;"><i class="fa-solid fa-leaf" style="color:var(--saffron-light);"></i> Your Conscious Impact</div>
        <p style="font-size:0.9375rem;color:rgba(255,255,255,0.8);line-height:1.7;margin:0;">This order has directly supported <strong style="color:var(--saffron-light);">3 artisan families</strong> and contributed ₹200 to Conscious Indi's sustainability fund. You've helped preserve traditional Indian craftsmanship. <strong style="color:var(--saffron-light);">Thank you for shopping conscious!</strong></p>
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