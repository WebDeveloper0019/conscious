<?php
declare(strict_types=1);
require_once __DIR__.'/../../../api/lib/helpers.php';
$session=current_auth_session(false);
if(!$session||$session['principal_type']!=='influencer'){header('Location: auth/login.php');exit;}
$check=get_db()->prepare('SELECT status FROM influencers WHERE id=:id LIMIT 1');$check->execute(['id'=>$session['principal_id']]);
if(in_array($check->fetchColumn(),[false,'suspended','blocked','inactive'],true)){header('Location: auth/login.php');exit;}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Creator Studio — Conscious Indi</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"><link rel="stylesheet" href="../../../assets/css/bharatcraft.css"><link rel="stylesheet" href="assets/css/influencer.css"></head><body><div class="if-app"><aside class="if-sidebar" id="if-sidebar"><a class="if-brand" href="../../../index.html"><i class="fa-solid fa-leaf"></i> Conscious Indi</a><nav aria-label="Creator studio"><a href="#overview" data-nav="overview" class="active"><i class="fa-solid fa-gauge"></i> Overview</a><a href="#application" data-nav="application"><i class="fa-solid fa-id-card"></i> Profile & verification</a><a href="#packages" data-nav="packages"><i class="fa-solid fa-box-open"></i> Service Packages</a><a href="#campaigns" data-nav="campaigns"><i class="fa-solid fa-link"></i> Campaign links</a><a href="#analytics" data-nav="analytics"><i class="fa-solid fa-chart-line"></i> Analytics</a><a href="#earnings" data-nav="earnings"><i class="fa-solid fa-indian-rupee-sign"></i> Earnings</a><a href="#withdrawals" data-nav="withdrawals"><i class="fa-solid fa-wallet"></i> Withdrawals</a><a href="#notifications" data-nav="notifications"><i class="fa-regular fa-bell"></i> Notifications</a><a href="#settings" data-nav="settings"><i class="fa-solid fa-gear"></i> Settings</a></nav><hr><a href="../../../creators.html"><i class="fa-solid fa-arrow-left"></i> Public website</a><a href="#" id="if-logout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></aside><main class="if-main"><header class="if-top"><div><button class="if-button if-mobile-menu" id="if-menu" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button><span class="if-kicker">Creator Studio</span><h1 id="if-title">Overview</h1><small id="if-identity"></small></div><span class="if-status" id="if-account-status">Loading account…</span></header><p id="if-message" class="if-message" role="status" hidden></p>
<section id="overview" class="if-section active"><div class="if-overview-hero"><div><span class="if-kicker">YOUR CREATOR STUDIO</span><h2>Share meaningful stories. Track real results.</h2><p>Manage your profile, approved products, campaign links and earnings from one place.</p></div><div class="if-overview-actions"><a class="if-button" href="#curations"><i class="fa-solid fa-bag-shopping"></i> Curate products</a><a class="if-button secondary" href="#campaigns"><i class="fa-solid fa-link"></i> Campaign links</a></div></div><div class="if-grid"><article class="if-card"><small><i class="fa-solid fa-arrow-pointer"></i> Link clicks</small><strong class="if-value" id="if-clicks">—</strong></article><article class="if-card"><small><i class="fa-solid fa-cart-shopping"></i> Conversions</small><strong class="if-value" id="if-conversions">—</strong></article><article class="if-card"><small><i class="fa-solid fa-indian-rupee-sign"></i> Attributed sales</small><strong class="if-value" id="if-revenue">—</strong></article><article class="if-card"><small><i class="fa-solid fa-wallet"></i> Commission</small><strong class="if-value" id="if-commission">—</strong></article></div><div class="if-two" style="margin-top:16px"><article class="if-card"><h2>Your next step</h2><p id="if-next-step">Loading your application…</p><a class="if-button" href="#application">Open profile</a></article><article class="if-card"><h2>Active campaigns</h2><div id="if-overview-campaigns" class="if-empty">Loading…</div></article></div></section>
<section id="application" class="if-section"><div class="if-stack"><div class="if-card" style="margin-bottom: 16px;">
    <h2>Profile Media</h2>
    <p class="if-muted">Upload a profile photo, cover image, and portfolio images. Max 5MB each (JPEG/PNG).</p>
    
    <div id="if-media-status" style="margin-bottom: 12px; font-weight: bold; color: var(--green);"></div>
    
    <form id="if-media-form" class="if-form">
        <label>Profile Photo
            <input type="file" name="profile_photo" accept="image/png, image/jpeg" required>
        </label>
        <button class="if-button" type="submit" data-media-type="profile_photo">Upload Profile Photo</button>
    </form>

    <form id="if-cover-form" class="if-form" style="margin-top: 12px;">
        <label>Cover Image
            <input type="file" name="cover_image" accept="image/png, image/jpeg" required>
        </label>
        <button class="if-button" type="submit" data-media-type="cover_image">Upload Cover Image</button>
    </form>

    <form id="if-portfolio-form" class="if-form" style="margin-top: 12px;">
        <label>Add Portfolio Image
            <input type="file" name="portfolio_image" accept="image/png, image/jpeg" required>
        </label>
        <button class="if-button" type="submit" data-media-type="portfolio_image">Add to Portfolio</button>
    </form>
</div>
<div class="if-card"><h2>Creator application</h2><p class="if-notice">Complete your public bio and social platforms, then upload identity, PAN and social ownership evidence. Documents are private and must pass a security scan before submission. Admin reviews the application before it appears publicly.</p><p id="if-application-status"></p><form id="if-profile-form" class="if-form-grid"><label class="if-field full">About your creative work<textarea name="bio" required minlength="40" maxlength="2000" placeholder="Tell buyers and brands about your niche, audience, and why you support conscious Indian makers."></textarea></label><div class="full"><div id="if-platforms" class="if-platforms"></div><button class="if-text-button" id="if-add-platform" type="button">+ Add another platform</button><p class="if-muted">Add one to five accounts that you own. Follower counts are self-reported and reviewed by the team.</p></div><label class="if-field full">Audience city or region<input name="audience_region" maxlength="100" placeholder="India-wide, Mumbai…"></label><div class="full"><button class="if-button" type="submit">Save profile</button></div></form></div><div class="if-two"><article class="if-card"><h2>Verification documents</h2><div id="if-documents"></div><form id="if-document-form" class="if-form"><label>Document type<select name="document_type" required><option value="identity">Government identity</option><option value="pan">PAN card</option><option value="social_ownership">Social account ownership proof</option><option value="address">Address proof</option></select></label><label>PDF, JPEG or PNG (max 5 MB)<input name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" required></label><button class="if-button" type="submit">Upload securely</button></form></article><article class="if-card"><h2>Payout verification</h2><p class="if-muted">Bank and PAN details are encrypted by the backend and shown here only as masked values.</p><p id="if-payout-status"></p><form id="if-payout-form" class="if-form"><label>Beneficiary name<input name="beneficiary_name" required maxlength="150"></label><label>Account number<input name="account_number" inputmode="numeric" pattern="[0-9]{9,18}" required autocomplete="off"></label><label>IFSC<input name="ifsc" pattern="[A-Za-z]{4}0[A-Za-z0-9]{6}" maxlength="11" required></label><label>PAN<input name="pan" pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]" maxlength="10" required></label><button class="if-button" type="submit">Save for verification</button></form></article></div><div class="if-card"><h2>Submit for admin review</h2><p>Submit only after the required files show a clean scan status. You can continue checking this page while review is pending.</p><button class="if-button" id="if-submit-application" type="button">Submit application</button></div></div></section>
<section id="campaigns" class="if-section"><div class="if-card"><h2>Assigned campaign links</h2><p>Only admin-assigned, active links appear here. Include the campaign disclosure in every promotional post.</p><div id="if-campaign-list" class="if-empty">Loading…</div></div>
</section>
<section id="packages" class="if-section">
    <div class="if-stack">
        <div class="if-card">
            <h2>Your Service Packages</h2>
            <p class="if-muted">Create packages for brands to purchase. Packages must be submitted for admin approval before they become active.</p>
            <div id="if-package-list" class="if-stack" style="margin-top: 16px;">
                <!-- Packages will load here -->
            </div>
        </div>
        
        <div class="if-card" id="if-create-package-card">
            <h2>Create a New Package</h2>
            <form id="if-package-form" class="if-form-grid">
                <label class="if-field full">Package Title
                    <input name="title" required minlength="3" placeholder="e.g., 1x Instagram Reel + 2 Stories">
                </label>
                <label class="if-field full">Description
                    <textarea name="description" required placeholder="Describe exactly what the brand gets..."></textarea>
                </label>
                
                <label class="if-field">Price (₹ INR)
                    <input name="price_inr" type="number" min="100" required>
                </label>
                <label class="if-field">Turnaround (Days)
                    <input name="turnaround_days" type="number" min="1" required>
                </label>
                <label class="if-field">Max Revisions
                    <input name="revisions" type="number" min="0" value="1" required>
                </label>
                <label class="if-field">Capacity (Per Month)
                    <input name="capacity" type="number" min="1" value="1" required>
                </label>

                <label class="if-field full">Deliverables (comma separated)
                    <input name="deliverables" required placeholder="1x Reel, 2x Story, 1x Raw footage">
                </label>
                <label class="if-field full">Platforms (comma separated)
                    <input name="platforms" required placeholder="Instagram, YouTube">
                </label>
                <label class="if-field full">Usage Rights
                    <input name="usage_rights" required placeholder="e.g., 30 days organic repost rights">
                </label>
                <label class="if-field full">Vendor Requirements
                    <input name="vendor_requirements" required placeholder="e.g., Product must be shipped 7 days prior">
                </label>
                <label class="if-field full">Tax Treatment
                    <input name="tax_treatment" required value="Inclusive of GST">
                </label>
                <label class="if-field full">Cancellation Terms
                    <input name="cancellation_terms" required placeholder="e.g., 50% refund if cancelled 3 days prior">
                </label>
                
                <div class="full" style="margin-top: 12px;">
                    <button class="if-button" type="submit">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>
</section>
<section id="analytics" class="if-section"><div class="if-card"><h2>Clicks and conversions</h2><label class="if-field">Period<select id="if-period"><option value="7">Last 7 days</option><option value="30" selected>Last 30 days</option><option value="90">Last 90 days</option><option value="365">Last year</option></select></label><div id="if-analytics" class="if-empty">Loading…</div></div></section>
<section id="earnings" class="if-section"><div class="if-grid"><article class="if-card"><small>Eligible balance</small><strong class="if-value" id="if-eligible">—</strong></article><article class="if-card"><small>Pending</small><strong class="if-value" id="if-pending">—</strong></article><article class="if-card"><small>Held / flagged</small><strong class="if-value" id="if-held">—</strong></article><article class="if-card"><small>Total commission</small><strong class="if-value" id="if-total-commission">—</strong></article></div><div class="if-card" style="margin-top:16px"><h2>Commission history</h2><div id="if-ledger" class="if-empty">Loading…</div></div></section>
<section id="withdrawals" class="if-section">
    <div class="if-two">
        <article class="if-card">
            <h2>Request a withdrawal</h2>
            <p class="if-muted">Minimum ₹100. Your payout profile must be verified.</p>
            <div style="margin: 16px 0; padding: 12px; background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 4px;">
                <strong>Available Balance:</strong> <span id="if-withdraw-balance" style="font-size: 1.2rem; color: #15803d;">Loading...</span>
            </div>
            <form id="if-withdraw-form" class="if-form">
                <label>Amount (₹)
                    <input name="amount" type="number" min="100" step="0.01" required>
                </label>
                <button class="if-button" type="submit">Request withdrawal</button>
            </form>
        </article>
        <article class="if-card">
            <h2>Withdrawal history</h2>
            <div id="if-withdraw-list" class="if-empty">Loading…</div>
        </article>
    </div>
</section>
<section id="notifications" class="if-section"><div class="if-card"><h2>Updates</h2><div id="if-notification-list" class="if-empty">Loading…</div></div></section>
<section id="settings" class="if-section"><div class="if-two"><article class="if-card"><h2>Account security</h2><p>Your creator account uses a secure, revocable session. Never share verification codes or your password.</p><form id="if-password-form" class="if-form"><label>Current password<input name="current_password" type="password" required autocomplete="current-password"></label><label>New password<input name="new_password" type="password" minlength="12" required autocomplete="new-password"></label><button class="if-button" type="submit">Change password</button></form></article><article class="if-card"><h2>Public visibility</h2><p>Your profile is shown publicly only after admin approval. Claims, links and disclosures must remain truthful and current.</p><p id="if-visibility"></p></article></div></section>
<section id="curations" class="if-section"><div class="if-stack"><div class="if-card"><h2>Curate approved products</h2><p class="if-muted">Feature up to 24 published products from approved makers on your public creator page. Products automatically disappear from your page if their listing is withdrawn.</p><form id="if-product-search" class="if-form"><label>Search the marketplace<input name="query" minlength="2" maxlength="80" placeholder="Try bamboo, handloom, pottery…" required></label><button class="if-button" type="submit">Find products</button></form><div id="if-product-results" class="if-stack" style="margin-top:16px"></div></div><div class="if-card"><h2>Your featured products</h2><div id="if-curated-products" class="if-stack">Loading…</div></div></div></section>
<section id="inquiries" class="if-section"><div class="if-card"><h2>Partnership inquiries</h2><p class="if-muted">Contact details shared with you are for responding to these requests only. Do not publish or reuse them for marketing without consent.</p><div id="if-inquiry-list" class="if-stack">Loading…</div></div></section>
</main></div><script src="assets/js/app.js?v=20261017" defer></script></body></html>
