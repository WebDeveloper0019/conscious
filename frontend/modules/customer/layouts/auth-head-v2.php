<?php
$pageTitle = $pageTitle ?? 'Customer access';
$authPage = $authPage ?? 'login';
$isRegister = $authPage === 'register';
$storyTitle = $isRegister ? 'A more thoughtful way to shop starts here.' : 'Welcome back to conscious shopping.';
$storyCopy = $isRegister ? 'Create one account to discover verified makers, understand product claims and follow every order clearly.' : 'Track vendor-managed deliveries, manage returns and see the people and impact behind every purchase.';
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="strict-origin-when-cross-origin"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#203b2a"><meta name="csrf-token" content="">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Conscious Indi</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../../../../assets/css/bharatcraft.css"><link rel="stylesheet" href="../../../../assets/css/conscious-pages.css"><link rel="stylesheet" href="../../../shared/assets/css/tokens.css"><link rel="stylesheet" href="../assets/css/app.css"><link rel="stylesheet" href="../assets/css/auth.css">
</head><body class="ca-auth-page" data-auth-page="<?= htmlspecialchars($authPage, ENT_QUOTES, 'UTF-8') ?>"><a class="cc-skip" href="#customer-auth-form">Skip to form</a>
<main class="ca-shell"><section class="ca-story" aria-label="About Conscious Indi"><div class="ca-story-orb ca-story-orb-one" aria-hidden="true"></div><div class="ca-story-orb ca-story-orb-two" aria-hidden="true"></div>
<a class="ca-logo" href="../../../../index.html" aria-label="Conscious Indi home"><span class="ca-logo-mark"><i class="fa-solid fa-leaf"></i></span><span>Conscious Indi</span></a>
<div class="ca-story-content"><span class="ca-eyebrow">Crafted with purpose</span><h1><?= htmlspecialchars($storyTitle, ENT_QUOTES, 'UTF-8') ?></h1><p><?= htmlspecialchars($storyCopy, ENT_QUOTES, 'UTF-8') ?></p>
<div class="ca-trust-list"><div class="ca-trust-item"><i class="fa-solid fa-shield-heart"></i><span><strong>Verified makers</strong><small>Clear seller and claim review</small></span></div><div class="ca-trust-item"><i class="fa-solid fa-route"></i><span><strong>Transparent delivery</strong><small>Manual updates directly from vendors</small></span></div><div class="ca-trust-item"><i class="fa-solid fa-seedling"></i><span><strong>Evidence-led impact</strong><small>Understand materials and practices</small></span></div></div></div>
<p class="ca-story-foot">Made for India's conscious commerce community.</p></section>
<section class="ca-panel"><div class="ca-panel-top"><a href="../../../../index.html"><i class="fa-solid fa-arrow-left"></i> Back to shop</a><span><i class="fa-solid fa-lock"></i> Secure access</span></div><div class="ca-card-wrap">
