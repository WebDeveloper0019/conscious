<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Complete your purchase securely. Multiple payment options — UPI, Cards, Net Banking, COD." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Checkout | Conscious India" />
  <meta property="og:description" content="Secure checkout for handcrafted Indian products. UPI, Cards & Net Banking accepted." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Checkout — Conscious India | Secure Payment</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ===== Checkout layout ===== */
.co-main-grid{display:grid;grid-template-columns:1fr 360px;gap:32px;align-items:start;}
.co-sidebar-sticky{position:sticky;top:80px;}
.co-form-card{background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:28px;}
.co-address-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

/* ===== Responsive breakpoints ===== */
@media (max-width:991px){
  .co-main-grid{grid-template-columns:1fr;gap:24px;}
  .co-sidebar-sticky{position:static;top:auto;}
}
@media (max-width:575px){
  .co-form-card{padding:18px;}
  .co-address-grid{grid-template-columns:1fr;gap:14px;}
}
@media (max-width:420px){
  .co-step-label{font-size:0.6875rem;}
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
            <li class="breadcrumb-item"><a href="portals/modules/customer/cart.php">Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Checkout">
  <div class="container">
    <!-- Step Indicator -->
    <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:40px;" aria-label="Checkout steps">
      <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--saffron);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;box-shadow:var(--shadow-saffron);">1</div>
        <span class="co-step-label" style="font-size:0.75rem;font-weight:600;color:var(--saffron-dark);">Address</span>
      </div>
      <div style="flex:1;max-width:80px;height:2px;background:var(--saffron);margin:0 8px;margin-bottom:16px;"></div>
      <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--border-warm);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;">2</div>
        <span class="co-step-label" style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">Payment</span>
      </div>
      <div style="flex:1;max-width:80px;height:2px;background:var(--border-warm);margin:0 8px;margin-bottom:16px;"></div>
      <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--border-warm);color:var(--text-muted);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;">3</div>
        <span class="co-step-label" style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">Review</span>
      </div>
    </div>

    <div class="co-main-grid">

      <!-- Step 1: Address Form -->
      <div class="bc-reveal-left">
        <div class="co-form-card">
          <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:24px;display:flex;align-items:center;gap:10px;">
            <span style="width:32px;height:32px;border-radius:50%;background:var(--saffron-pale);color:var(--saffron-dark);display:flex;align-items:center;justify-content:center;font-size:0.9375rem;font-weight:700;">1</span>
            Delivery Address
          </h2>

          <div class="co-address-grid">
            <div>
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">First Name *</label>
              <input type="text" placeholder="Priya" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div>
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Last Name *</label>
              <input type="text" placeholder="Sharma" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div style="grid-column:span 2;">
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Mobile Number *</label>
              <div style="display:flex;gap:8px;">
                <div style="padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);background:var(--bg-cream);font-size:0.9375rem;color:var(--text-secondary);white-space:nowrap;">🇮🇳 +91</div>
                <input type="tel" placeholder="98765 43210" style="flex:1;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
              </div>
            </div>
            <div style="grid-column:span 2;">
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Address Line 1 *</label>
              <input type="text" placeholder="House No, Street, Locality" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div style="grid-column:span 2;">
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Address Line 2</label>
              <input type="text" placeholder="Apartment, landmark (optional)" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div>
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">City *</label>
              <input type="text" placeholder="Mumbai" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div>
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">PIN Code *</label>
              <input type="text" placeholder="400001" style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);" />
            </div>
            <div style="grid-column:span 2;">
              <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">State *</label>
              <select style="width:100%;padding:11px 14px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);color:var(--text-primary);">
                <option value="">Select State</option>
                <option>Maharashtra</option><option>Rajasthan</option><option>Uttar Pradesh</option>
                <option>West Bengal</option><option>Gujarat</option><option>Karnataka</option>
                <option>Tamil Nadu</option><option>Delhi</option><option>Madhya Pradesh</option>
              </select>
            </div>
            <div style="grid-column:span 2;">
              <label style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:var(--text-secondary);cursor:pointer;">
                <input type="checkbox" style="accent-color:var(--saffron);width:16px;height:16px;" />
                Save this address for future orders
              </label>
            </div>
          </div>

          <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border-light);">
            <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">Delivery Speed</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--saffron);border-radius:var(--r-md);background:var(--saffron-pale);cursor:pointer;flex-wrap:wrap;">
                <input type="radio" name="delivery" checked style="accent-color:var(--saffron);" />
                <div style="flex:1;min-width:140px;">
                  <div style="font-weight:600;font-size:0.9375rem;color:var(--text-primary);">Standard Delivery</div>
                  <div style="font-size:0.8125rem;color:var(--text-muted);">Delivered in 5–7 business days</div>
                </div>
                <div style="font-weight:700;color:var(--green);">Free</div>
              </label>
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);cursor:pointer;flex-wrap:wrap;">
                <input type="radio" name="delivery" style="accent-color:var(--saffron);" />
                <div style="flex:1;min-width:140px;">
                  <div style="font-weight:600;font-size:0.9375rem;color:var(--text-primary);">Express Delivery</div>
                  <div style="font-size:0.8125rem;color:var(--text-muted);">Delivered in 2–3 business days</div>
                </div>
                <div style="font-weight:700;color:var(--text-primary);">₹99</div>
              </label>
            </div>
          </div>

          <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border-light);">
            <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;">Payment Method</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:2px solid var(--saffron);border-radius:var(--r-md);background:var(--saffron-pale);cursor:pointer;">
                <input type="radio" name="pay" checked style="accent-color:var(--saffron);" />
                <i class="fa-brands fa-google-pay" style="font-size:1.5rem;color:var(--navy);"></i>
                <span style="font-weight:600;font-size:0.9375rem;color:var(--text-primary);">UPI / Google Pay / PhonePe</span>
              </label>
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);cursor:pointer;">
                <input type="radio" name="pay" style="accent-color:var(--saffron);" />
                <i class="fa-regular fa-credit-card" style="font-size:1.25rem;color:var(--navy);"></i>
                <span style="font-weight:500;font-size:0.9375rem;color:var(--text-secondary);">Credit / Debit Card</span>
              </label>
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);cursor:pointer;">
                <input type="radio" name="pay" style="accent-color:var(--saffron);" />
                <i class="fa-solid fa-building-columns" style="font-size:1.25rem;color:var(--navy);"></i>
                <span style="font-weight:500;font-size:0.9375rem;color:var(--text-secondary);">Net Banking / EMI</span>
              </label>
              <label style="display:flex;align-items:center;gap:14px;padding:14px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);cursor:pointer;">
                <input type="radio" name="pay" style="accent-color:var(--saffron);" />
                <i class="fa-solid fa-money-bill-wave" style="font-size:1.25rem;color:var(--green);"></i>
                <span style="font-weight:500;font-size:0.9375rem;color:var(--text-secondary);">Cash on Delivery</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Sidebar -->
      <div class="bc-reveal-right co-sidebar-sticky">
        <div style="background:white;border-radius:var(--r-lg);border:1.5px solid var(--border-warm);padding:24px;margin-bottom:16px;">
          <h2 style="font-family:var(--font-display);font-size:1.0625rem;font-weight:700;color:var(--text-primary);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border-light);">Order Summary</h2>
          <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:48px;height:48px;border-radius:var(--r-sm);background:linear-gradient(135deg,#C88422,#4A210D);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">🏺</div>
              <div style="flex:1;font-size:0.8125rem;font-weight:600;color:var(--text-primary);">Blue Pottery Vase</div>
              <div style="font-weight:700;font-size:0.9375rem;white-space:nowrap;">₹1,299</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:48px;height:48px;border-radius:var(--r-sm);background:linear-gradient(135deg,#C88422,#6B4E08);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">💍</div>
              <div style="flex:1;font-size:0.8125rem;font-weight:600;color:var(--text-primary);">Kundan Necklace Set</div>
              <div style="font-weight:700;font-size:0.9375rem;white-space:nowrap;">₹3,499</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
              <div style="width:48px;height:48px;border-radius:var(--r-sm);background:linear-gradient(135deg,#4B572C,#1A4228);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">🌿</div>
              <div style="flex:1;font-size:0.8125rem;font-weight:600;color:var(--text-primary);">Bamboo Dining Set ×2</div>
              <div style="font-weight:700;font-size:0.9375rem;white-space:nowrap;">₹1,798</div>
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--text-secondary);"><span>Subtotal</span><span style="font-weight:600;">₹6,596</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--text-secondary);"><span>Delivery</span><span style="color:var(--green);font-weight:600;">Free</span></div>
            <div style="display:flex;justify-content:space-between;font-size:0.9375rem;color:var(--green);"><span>Discount</span><span>−₹659</span></div>
            <div style="height:1px;background:var(--border-light);"></div>
            <div style="display:flex;justify-content:space-between;font-size:1.125rem;font-weight:800;color:var(--text-primary);"><span>Total Payable</span><span>₹5,937</span></div>
          </div>
          <a href="/conscious/order-confirmation" class="bc-btn bc-btn-primary" style="width:100%;justify-content:center;font-size:1rem;padding:15px;">
            <i class="fa-solid fa-lock"></i> Place Order — ₹5,937
          </a>
        </div>
        <div style="background:var(--green-pale);border-radius:var(--r-md);border:1px solid rgba(19,136,8,0.15);padding:14px 16px;font-size:0.8125rem;color:var(--green-dark);display:flex;gap:8px;">
          <i class="fa-solid fa-shield-halved" style="margin-top:1px;"></i>
          <span>Your payment is 100% secured. Protected by Razorpay payment gateway.</span>
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