<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Failed — Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
  <style>
    .status-card {
      background: #fff;
      padding: 60px 40px;
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-md);
      text-align: center;
      max-width: 600px;
      margin: 0 auto;
    }
    .status-icon.failed {
      width: 80px;
      height: 80px;
      background: #fff5f5;
      color: #e53e3e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      margin: 0 auto 30px;
    }
  </style>
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>

<main class="section-py bg-cream">
  <div class="container">
    <div class="status-card">
      <div class="status-icon failed">
        <i class="fa-solid fa-circle-xmark"></i>
      </div>
      <h1 class="bc-section-title">Payment Failed</h1>
      <p class="bc-section-desc mx-auto">Unfortunately, your payment could not be processed. No money has been deducted from your account.</p>
      
      <div class="alert alert-danger mt-4 text-start" style="font-size: 0.9rem;">
        <strong>Reason:</strong> Insufficient funds or transaction declined by your bank.
      </div>

      <div class="mt-5 d-grid gap-3">
        <a href="portals/modules/customer/checkout.php" class="bc-btn bc-btn-primary">Try Again</a>
        <a href="/conscious/contact" class="bc-btn bc-btn-outline">Contact Support</a>
      </div>
      <div class="mt-4">
        <a href="portals/modules/customer/cart.php" class="text-muted small">Return to Cart</a>
      </div>
    </div>
  </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
</body>
</html>
