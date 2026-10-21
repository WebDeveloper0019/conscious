<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Track your Conscious Indi order in real-time. Get live updates on your handcrafted product delivery." />
  <meta name="author" content="Conscious Indi" />
  <meta property="og:title" content="Track Your Order | Conscious Indi" />
  <meta property="og:description" content="Real-time order tracking for your handcrafted product delivery from Conscious Indi." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious Indi" />
  <title>Track Order — Conscious Indi | Real-time Order Tracking</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* Track Order — UI improvements */
.track-order-card {
  background: #fff;
  border: 1px solid var(--border-warm);
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 8px 28px rgba(55, 40, 20, 0.05);
}

.track-order-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.track-order-field label {
  display: block;
  margin-bottom: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.track-order-field input {
  width: 100%;
  min-height: 48px;
  padding: 12px 14px;
  border: 1px solid var(--border-warm);
  border-radius: 10px;
  background: #fff;
  color: var(--text-primary);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.track-order-field input:focus {
  border-color: var(--saffron);
  box-shadow: 0 0 0 3px rgba(255, 153, 51, 0.14);
}

.track-order-submit {
  grid-column: 1 / -1;
  width: 100%;
  min-height: 48px;
  justify-content: center;
  border-radius: 10px;
}

.track-order-summary {
  display: flex;
  align-items: center;
  gap: 18px;
  padding-bottom: 24px;
  margin-bottom: 26px;
  border-bottom: 1px solid var(--border-light);
}

.track-order-product-icon {
  width: 68px;
  height: 68px;
  flex: 0 0 68px;
  display: grid;
  place-items: center;
  border-radius: 16px;
  background: #fbf0df;
  color: #4A210D;
  font-size: 1.8rem;
}

.track-order-timeline {
  position: relative;
  padding-left: 42px;
}

.track-order-step {
  position: relative;
  padding-bottom: 28px;
}

.track-order-step:last-child {
  padding-bottom: 0;
}

.track-order-step::before {
  content: "";
  position: absolute;
  left: -32px;
  top: 3px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid var(--border-warm);
  z-index: 1;
}

.track-order-step.is-complete::before {
  content: "✓";
  display: grid;
  place-items: center;
  color: #fff;
  background: var(--green);
  border-color: var(--green);
  font-size: 12px;
  font-weight: 700;
}

.track-order-step.is-current::before {
  border: 5px solid var(--saffron);
  box-shadow: 0 0 0 4px rgba(255, 153, 51, 0.13);
}

.track-order-step:not(:last-child)::after {
  content: "";
  position: absolute;
  left: -22px;
  top: 25px;
  bottom: 0;
  width: 2px;
  background: var(--border-warm);
}

.track-order-step.is-complete:not(:last-child)::after {
  background: var(--green);
}

.track-order-step.is-pending {
  opacity: 0.55;
}

@media (max-width: 575px) {
  .track-order-card {
    padding: 20px 16px;
    border-radius: 16px;
  }

  .track-order-form {
    grid-template-columns: 1fr;
  }

  .track-order-summary {
    align-items: flex-start;
    gap: 12px;
  }

  .track-order-product-icon {
    width: 52px;
    height: 52px;
    flex-basis: 52px;
    border-radius: 12px;
    font-size: 1.4rem;
  }
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

<div class="bc-breadcrumb-wrap" style="background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;">
  <div class="container">
    <nav aria-label="Breadcrumb">
      <ol class="breadcrumb mb-0" style="font-size:0.8125rem;">
        <li class="breadcrumb-item"><a href="/conscious/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Track Order</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py" style="background:var(--bg-cream);" aria-label="Track order">
  <div class="container" style="max-width:760px;">
    <div class="bc-section-header centered bc-reveal">
      <div class="bc-section-label">Order Tracking</div>
      <h1 class="bc-section-title">Track Your Order</h1>
      <p class="bc-section-desc">Enter your order ID or registered mobile number to track your delivery status.</p>
    </div>

    <!-- Lookup Form -->
    <div class="track-order-card bc-reveal" style="margin-bottom:24px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
          <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Order ID</label>
          <input type="text" value="#CI-2025-084721" style="width:100%;padding:12px 16px;border:1.5px solid var(--saffron);border-radius:var(--r-md);font-size:0.9375rem;background:var(--saffron-pale);" />
        </div>
        <div>
          <label style="font-size:0.8125rem;font-weight:600;color:var(--text-primary);margin-bottom:6px;display:block;">Mobile Number</label>
          <input type="tel" placeholder="Registered mobile number" style="width:100%;padding:12px 16px;border:1.5px solid var(--border-warm);border-radius:var(--r-md);font-size:0.9375rem;background:var(--bg-cream);" />
        </div>
      </div>
<button class="bc-btn bc-btn-primary track-order-submit" type="button">
  <i class="fa-solid fa-magnifying-glass"></i> Track Order
</button>
    </div>

    <!-- Tracking Results -->
<div class="track-order-card bc-reveal">
      <!-- Order Summary -->
      <div class="track-order-summary">
        <div style="width:72px;height:72px;border-radius:var(--r-md);background:linear-gradient(135deg,#C88422,#4A210D);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">🏺</div>
        <div style="flex:1;">
          <div style="font-family:var(--font-display);font-size:1.0625rem;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Blue Pottery Vase + 2 more items</div>
          <div style="font-size:0.875rem;color:var(--text-muted);margin-bottom:8px;">Order #CI-2025-084721 · Placed 15 Jan 2025 · ₹5,937</div>
          <span class="bc-badge" style="background:var(--saffron-pale);color:var(--saffron-dark);font-size:0.75rem;"><i class="fa-solid fa-truck"></i> In Transit — Arriving 22–24 Jan</span>
        </div>
      </div>

      <!-- Progress Tracker -->
      <h3 style="font-family:var(--font-display);font-size:1.0625rem;font-weight:700;margin-bottom:28px;color:var(--text-primary);">Shipment Status</h3>
      <div style="position:relative;padding-left:32px;">
        <!-- Vertical line -->
        <div style="position:absolute;left:11px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--green) 60%,var(--border-warm) 60%);"></div>

        <!-- Step 1: Placed -->
        <div style="position:relative;margin-bottom:28px;display:flex;gap:16px;align-items:flex-start;">
          <div style="position:absolute;left:-21px;width:24px;height:24px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;flex-shrink:0;"><i class="fa-solid fa-check"></i></div>
          <div><div style="font-weight:700;color:var(--text-primary);margin-bottom:3px;">Order Placed</div><div style="font-size:0.8125rem;color:var(--text-muted);">15 Jan 2025, 10:42 AM</div><div style="font-size:0.8125rem;color:var(--green);margin-top:4px;">Payment confirmed via UPI</div></div>
        </div>

        <!-- Step 2: Packed -->
        <div style="position:relative;margin-bottom:28px;display:flex;gap:16px;align-items:flex-start;">
          <div style="position:absolute;left:-21px;width:24px;height:24px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;flex-shrink:0;"><i class="fa-solid fa-check"></i></div>
          <div><div style="font-weight:700;color:var(--text-primary);margin-bottom:3px;">Packed &amp; Ready</div><div style="font-size:0.8125rem;color:var(--text-muted);">15 Jan 2025, 3:15 PM</div><div style="font-size:0.8125rem;color:var(--green);margin-top:4px;">Handcrafted items carefully packaged</div></div>
        </div>

        <!-- Step 3: Shipped (active) -->
        <div style="position:relative;margin-bottom:28px;display:flex;gap:16px;align-items:flex-start;">
          <div style="position:absolute;left:-21px;width:24px;height:24px;border-radius:50%;background:var(--saffron);display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;flex-shrink:0;box-shadow:var(--shadow-saffron);animation:pulse 2s infinite;"><i class="fa-solid fa-truck"></i></div>
          <div>
            <div style="font-weight:700;color:var(--saffron-dark);margin-bottom:3px;">In Transit <span style="font-size:0.6875rem;background:var(--saffron);color:white;padding:2px 8px;border-radius:var(--r-full);margin-left:6px;">CURRENT</span></div>
            <div style="font-size:0.8125rem;color:var(--text-muted);">16 Jan 2025, 9:00 AM</div>
            <div style="font-size:0.8125rem;color:var(--text-secondary);margin-top:4px;">Shipper: Delhivery · AWB: DEL4821039712</div>
            <div style="font-size:0.8125rem;color:var(--text-secondary);">Current Location: Mumbai Hub → Hyderabad</div>
          </div>
        </div>

        <!-- Step 4: Out for Delivery -->
        <div style="position:relative;margin-bottom:28px;display:flex;gap:16px;align-items:flex-start;opacity:0.45;">
          <div style="position:absolute;left:-21px;width:24px;height:24px;border-radius:50%;background:var(--border-warm);border:2px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--text-light);font-size:0.75rem;flex-shrink:0;"><i class="fa-solid fa-person-walking"></i></div>
          <div><div style="font-weight:700;color:var(--text-secondary);margin-bottom:3px;">Out for Delivery</div><div style="font-size:0.8125rem;color:var(--text-muted);">Expected 22–23 Jan 2025</div></div>
        </div>

        <!-- Step 5: Delivered -->
        <div style="position:relative;display:flex;gap:16px;align-items:flex-start;opacity:0.45;">
          <div style="position:absolute;left:-21px;width:24px;height:24px;border-radius:50%;background:var(--border-warm);border:2px solid var(--border-warm);display:flex;align-items:center;justify-content:center;color:var(--text-light);font-size:0.75rem;flex-shrink:0;"><i class="fa-solid fa-house"></i></div>
          <div><div style="font-weight:700;color:var(--text-secondary);margin-bottom:3px;">Delivered</div><div style="font-size:0.8125rem;color:var(--text-muted);">Expected 22–24 Jan 2025</div></div>
        </div>
      </div>

      <div style="margin-top:28px;padding-top:24px;border-top:1px solid var(--border-light);display:flex;gap:12px;flex-wrap:wrap;">
        <button class="bc-btn bc-btn-outline bc-btn-sm"><i class="fa-regular fa-envelope"></i> Email Update</button>
        <button class="bc-btn bc-btn-outline bc-btn-sm"><i class="fa-brands fa-whatsapp"></i> WhatsApp Update</button>
        <a href="/conscious/contact" class="bc-btn bc-btn-outline bc-btn-sm"><i class="fa-regular fa-message"></i> Contact Support</a>
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