<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#A63D24">
  <meta name="description" content="Current published event details from Conscious India.">
  <title>Event details | Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/bharatcraft.css">
  <style>
    .el-shell{min-height:62vh;padding:clamp(30px,6vw,76px) 0;background:#FFF8E9}.el-back{display:inline-flex;gap:8px;align-items:center;margin-bottom:22px;color:#7d2e1b;font-weight:700;text-decoration:none}.el-status{padding:16px;border:1px solid #e4c9b5;border-radius:14px;background:#fff;color:#4A210D}.el-card{display:grid;grid-template-columns:minmax(260px,.9fr) minmax(0,1.1fr);overflow:hidden;border:1px solid #ead8c7;border-radius:24px;background:#fff;box-shadow:0 18px 45px rgba(74,33,13,.1)}.el-media{min-height:420px;background:#f2e6d6;display:grid;place-items:center;color:#A63D24;font-size:54px}.el-media img{width:100%;height:100%;object-fit:cover}.el-copy{padding:clamp(24px,5vw,48px)}.el-kicker{color:#A63D24;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}.el-copy h1{margin:8px 0 14px;color:#4A210D;font:700 clamp(1.8rem,4vw,2.8rem)/1.16 Poppins,sans-serif}.el-description{color:#63554d;line-height:1.75}.el-facts{display:grid;gap:10px;margin:24px 0}.el-facts p{display:flex;gap:11px;margin:0;color:#4A210D}.el-facts i{width:18px;color:#A63D24}.el-tickets{margin-top:25px;padding-top:22px;border-top:1px solid #ead8c7}.el-ticket{display:flex;justify-content:space-between;gap:20px;padding:12px 0;border-bottom:1px solid #f1e6db}.el-ticket small{display:block;color:#75665d}.el-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}@media(max-width:760px){.el-shell{padding:24px 0 100px}.el-card{grid-template-columns:1fr;border-radius:18px}.el-media{min-height:230px;max-height:320px}.el-copy{padding:22px}.el-actions .bc-btn{width:100%;justify-content:center}}
  </style>
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'].'/conscious/includes/public-header.php'; ?>
<main id="main-content" class="el-shell"><div class="container">
  <a class="el-back" href="/conscious/"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Back to Conscious India</a>
  <p id="el-status" class="el-status" role="status">Loading current event details…</p>
  <article id="el-event" class="el-card" hidden></article>
</div></main>
<?php require $_SERVER['DOCUMENT_ROOT'].'/conscious/includes/public-footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261024-2"></script>
<script src="assets/js/event-detail-live.js?v=20261024-1"></script>
</body>
</html>
