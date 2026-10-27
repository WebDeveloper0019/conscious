<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Explore product details, seller information, live stock and delivery options on Conscious India." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Product details | Conscious India" />
  <meta property="og:description" content="Explore handcrafted products on Conscious India." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Product details | Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}

/* ============ PRODUCT DETAIL PAGE RESPONSIVE ============ */
.bc-pd-layout{ display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:start; }

.bc-pd-buy-row{ display:flex; gap:12px; margin-bottom:20px; align-items:center; flex-wrap:wrap; }
.bc-pd-buy-row .bc-btn-primary{ flex:1; justify-content:center; min-width:150px; }

.bc-pd-tabs{
  display:flex; gap:0; border-bottom:2px solid var(--border-warm); margin-bottom:32px;
  overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none;
}
.bc-pd-tabs::-webkit-scrollbar{ display:none; }
.bc-pd-tabs button{ flex:0 0 auto; }

.bc-pd-specs{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }

.bc-pd-related-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }

@media (max-width:900px){
  .bc-pd-layout{ grid-template-columns:1fr; gap:32px; }
  .bc-pd-related-grid{ grid-template-columns:repeat(3,1fr); }
}

@media (max-width:768px){
  .bc-pd-related-grid{ grid-template-columns:repeat(2,1fr); gap:16px; }
}

@media (max-width:480px){
  .bc-pd-specs{ grid-template-columns:1fr; }
  .bc-pd-buy-row .bc-btn-primary{ flex:1 1 100%; order:3; }
}

@media (max-width:420px){
  .bc-pd-related-grid{ grid-template-columns:1fr; }
}
.pd-back{display:inline-block;margin-bottom:22px;color:var(--saffron-dark);font-weight:700;text-decoration:none}.pd-demo-notice{padding:12px 16px;border:1px solid #e7be70;border-radius:12px;background:#fff4dd;color:#7d5300}.pd-live-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:clamp(24px,5vw,65px);align-items:start}.pd-live-hero{display:block;width:100%;max-height:650px;aspect-ratio:1;object-fit:cover;border-radius:20px;border:1px solid var(--border-warm);background:#f4ece1}.pd-live-thumbs{display:flex;gap:10px;margin-top:12px}.pd-live-thumbs button{padding:0;border:1px solid var(--border-warm);border-radius:8px;overflow:hidden;background:white}.pd-live-thumbs img{width:72px;height:72px;object-fit:cover}.pd-live-maker{margin:0 0 8px;color:var(--saffron-dark);font-weight:700}.pd-live-info h1{font-family:var(--font-display);font-size:clamp(1.8rem,3vw,2.6rem);line-height:1.2;color:var(--text-primary)}.pd-live-short{color:var(--text-secondary)}.pd-live-price{display:flex;gap:16px;align-items:baseline;margin:20px 0}.pd-live-price strong{font-size:2rem}.pd-live-price del{color:var(--text-muted)}.pd-live-stock{color:var(--green-dark);font-weight:700}.pd-live-controls{display:flex;gap:12px;margin:20px 0}.pd-live-controls label{display:flex;flex:1;flex-direction:column;gap:6px;font-weight:700}.pd-live-controls select,.pd-live-controls input{width:100%;padding:10px;border:1px solid var(--border-warm);border-radius:9px;background:white}.pd-live-actions{display:flex;flex-wrap:wrap;gap:10px}.pd-live-actions .bc-btn{min-height:44px}.pd-live-feedback:not([hidden]){margin:15px 0;padding:13px 16px;border:1px solid #e3ad87;border-radius:11px;background:#fff3e8;color:#733c1b;font-weight:600;line-height:1.55}.pd-live-feedback a{color:#264d35;text-decoration:underline;font-weight:800}.pd-live-delivery{margin-top:20px;color:var(--text-secondary);line-height:1.6}.pd-live-description{margin-top:44px;padding:26px;border:1px solid var(--border-warm);border-radius:18px;background:white}.pd-live-description h2{font-family:var(--font-display);font-size:1.4rem}.pd-live-specs{display:grid;grid-template-columns:150px 1fr;gap:8px 20px;margin:20px 0 0}.pd-live-specs dt{color:var(--text-muted)}.pd-live-specs dd{margin:0;font-weight:600}@media(max-width:760px){.pd-live-layout{grid-template-columns:1fr}.pd-live-controls{flex-direction:column}.pd-live-specs{grid-template-columns:1fr}.pd-live-specs dd{margin-bottom:9px}}
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
<main id="main-content"><section class="section-py" style="background:var(--bg-cream);min-height:60vh" aria-label="Product detail"><div class="container"><p id="pd-status" class="pd-live-feedback" role="status">Loading product details…</p><div id="pd-live"></div></div></section></main>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
<script src="assets/js/product-detail-live.js?v=20261017-2"></script>
</body>
</html>
