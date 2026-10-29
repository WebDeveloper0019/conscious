<?php
$pageTitle = $pageTitle ?? 'Admin access';
$authStep = $authStep ?? 'Secure administrator access';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#4B572C">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Conscious India Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../../../assets/css/bharatcraft.css">
    <link rel="stylesheet" href="../../../../assets/css/conscious-pages.css">
    <link rel="stylesheet" href="../../../shared/assets/css/tokens.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../../../shared/assets/css/brand-typography.css?v=20261026">
</head>
<body class="ac-auth-page">
<a class="ac-skip" href="#auth-main">Skip to access form</a>
<main class="ac-auth-shell" id="auth-main">
    <section class="ac-auth-brand" aria-label="Conscious India administration">
        <a class="ac-auth-logo" href="../../../../" aria-label="Conscious India home">
            <span>CI</span><strong>Conscious India</strong>
        </a>
        <p class="ac-auth-kicker">Protected operations workspace</p>
        <h1>Responsible commerce needs responsible administration.</h1>
        <p>Access is restricted, monitored and recorded. Never share credentials, verification codes or recovery links.</p>
        <ul class="ac-auth-points">
            <li><i class="fa-solid fa-shield-halved"></i> Multi-factor authentication</li>
            <li><i class="fa-solid fa-user-lock"></i> Role-based least privilege</li>
            <li><i class="fa-solid fa-clock-rotate-left"></i> Audited administrative actions</li>
        </ul>
    </section>
    <section class="ac-auth-panel">
        <div class="ac-auth-card">
            <p class="ac-auth-step"><?= htmlspecialchars($authStep, ENT_QUOTES, 'UTF-8') ?></p>
