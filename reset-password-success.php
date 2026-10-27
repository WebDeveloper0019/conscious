<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <title>Password Reset Successful — Conscious India</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
  <style>
    .bc-auth-section {
      min-height: 80vh;
      display: flex;
      align-items: center;
      padding: 60px 0;
      background: var(--bg-cream);
    }
    .bc-auth-card {
      background: #fff;
      padding: 40px;
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-md);
      text-align: center;
      max-width: 500px;
      margin: 0 auto;
    }
    .bc-success-icon {
      width: 80px;
      height: 80px;
      background: var(--green-pale);
      color: var(--green);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      margin: 0 auto 24px;
    }
  </style>
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-header.php'; ?>

<!-- PRELOADER -->
<div id="bc-preloader" role="status" aria-label="Loading Conscious India">
  <img src="assets/images/symbol.png" alt="Conscious India" class="bc-preloader-symbol" width="90" height="90" aria-hidden="true" />
  <div class="bc-preloader-brand">Conscious India</div>
  <div class="bc-preloader-chakra"></div>
</div>

<main class="bc-auth-section">
  <div class="container">
    <div class="bc-auth-card">
      <div class="bc-success-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h1 class="bc-section-title">Password Reset</h1>
      <p class="bc-section-desc mb-4">
        Your password has been successfully reset. You can now log in with your new credentials.
      </p>
      
      <div class="d-grid">
        <a href="portals/modules/customer/auth/login.php" class="bc-btn bc-btn-primary">Back to Login</a>
      </div>
    </div>
  </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
</body>
</html>
