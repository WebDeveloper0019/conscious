<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <title>OTP Verification — Conscious India</title>
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
      max-width: 450px;
      margin: 0 auto;
    }
    .otp-input-group {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin: 30px 0;
    }
    .otp-input {
      width: 50px;
      height: 60px;
      border: 2px solid var(--border-warm);
      border-radius: var(--r-sm);
      text-align: center;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--navy);
      transition: all var(--t-fast);
    }
    .otp-input:focus {
      border-color: var(--saffron);
      box-shadow: 0 0 0 4px var(--saffron-pale);
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
      <h1 class="bc-section-title">Verify OTP</h1>
      <p class="bc-section-desc mb-0">Enter the 4-digit code sent to</p>
      <p class="fw-bold text-navy">+91 98XXX XXXXX</p>
      
      <div class="otp-input-group">
        <input type="text" maxlength="1" class="otp-input" autofocus>
        <input type="text" maxlength="1" class="otp-input">
        <input type="text" maxlength="1" class="otp-input">
        <input type="text" maxlength="1" class="otp-input">
      </div>
      
      <div class="d-grid mb-4">
        <button class="bc-btn bc-btn-primary" onclick="window.location.href='/conscious/customer/dashboard'">Verify & Proceed</button>
      </div>

      <div class="text-muted" style="font-size: 0.875rem;">
        Resend code in <span id="timer" class="fw-bold">00:59</span>
      </div>
      <button class="btn btn-link text-saffron p-0 mt-2 d-none" id="resend-otp" style="font-size: 0.875rem; font-weight: 600;">Resend OTP</button>
    </div>
  </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/conscious/includes/public-footer.php'; ?>

<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
<script>
  // OTP Input Auto-focus
  const inputs = document.querySelectorAll('.otp-input');
  inputs.forEach((input, index) => {
    input.addEventListener('keyup', (e) => {
      if (e.target.value.length === 1 && index < inputs.length - 1) {
        inputs[index + 1].focus();
      }
      if (e.key === 'Backspace' && index > 0) {
        inputs[index - 1].focus();
      }
    });
  });

  // Timer logic
  let timeLeft = 59;
  const timerDisplay = document.getElementById('timer');
  const resendBtn = document.getElementById('resend-otp');
  
  const countdown = setInterval(() => {
    if (timeLeft <= 0) {
      clearInterval(countdown);
      timerDisplay.parentElement.classList.add('d-none');
      resendBtn.classList.remove('d-none');
    } else {
      timerDisplay.innerText = `00:${timeLeft < 10 ? '0' : ''}${timeLeft}`;
      timeLeft--;
    }
  }, 1000);
</script>
</body>
</html>
