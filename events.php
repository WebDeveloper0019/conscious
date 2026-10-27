<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#A63D24" />
  <meta name="robots" content="index, follow" />
  <meta name="description" content="Discover conscious living events across India — pop-up markets, artisan exhibitions, sustainability workshops and more." />
  <meta name="author" content="Conscious India" />
  <meta property="og:title" content="Events | Conscious India" />
  <meta property="og:description" content="Pop-ups, exhibitions, workshops and summits celebrating conscious living across India." />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Conscious India" />
  <title>Events — Conscious India | Pop-Ups, Exhibitions & Sustainability Summits</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bharatcraft.css" />
  <link rel="stylesheet" href="assets/css/event-management.css" />
<style>
.bc-breadcrumb-wrap{background:var(--bg-cream);border-bottom:1px solid var(--border-warm);padding:10px 0;}
.event-hero-shell{background:linear-gradient(135deg,#07253d,#0d3a5a 45%,#143b72);padding:40px 0;}
.event-hero-card{display:grid;grid-template-columns:1.2fr .8fr;gap:22px;align-items:center;background:linear-gradient(135deg,rgba(255,255,255,.14),rgba(255,255,255,.06));border:1px solid rgba(255,255,255,.18);border-radius:28px;padding:28px;color:#fff;backdrop-filter:blur(9px);box-shadow:0 26px 55px rgba(4,22,46,.28)}
.event-hero-label{justify-content:flex-start;color:#f8d38b}
.event-hero-title{color:#fff;margin-bottom:10px}
.event-hero-desc{color:rgba(255,255,255,.82);max-width:650px}
.event-hero-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.event-hero-pills span{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);padding:8px 12px;border-radius:999px;font-size:.9rem}
.event-hero-graphic{position:relative;min-height:260px;display:flex;align-items:center;justify-content:center}
.event-hero-orb{position:absolute;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(255,210,111,.9),rgba(255,210,111,.15) 60%,transparent 70%);filter:blur(4px)}
.event-hero-stack{position:relative;width:min(100%,290px);display:grid;gap:12px}
.event-hero-card-tile{background:#fff;color:#0d2346;border-radius:18px;padding:14px 16px;font-weight:700;display:flex;align-items:center;gap:10px;box-shadow:0 10px 24px rgba(8,18,42,.14)}
.event-gallery-carousel{position:relative;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:14px}
.event-gallery-viewport{overflow:hidden}
.event-gallery-track{display:flex;gap:16px;transition:transform .45s ease}
.event-gallery-card{min-width:calc(50% - 8px);background:#fff;border-radius:20px;border:1px solid var(--border-warm);overflow:hidden;box-shadow:var(--shadow-sm)}
.event-gallery-image{height:220px;background:linear-gradient(135deg,#214b7d,#a9c580)}
.event-gallery-image.square{background:linear-gradient(135deg,#7c4a4d,#e2b56a)}
.event-gallery-image.landscape{background:linear-gradient(135deg,#164f4a,#86b16b)}
.event-gallery-body{padding:14px 16px;display:flex;flex-direction:column;gap:6px}
.event-gallery-body span{color:var(--text-muted);font-size:.9rem}
.event-gallery-control{width:42px;height:42px;border:none;border-radius:50%;background:var(--navy);color:#fff;display:grid;place-items:center}
.event-gallery-dots{display:flex;gap:8px;grid-column:1 / -1;justify-content:center}
.event-gallery-dot{width:10px;height:10px;border-radius:50%;border:none;background:#d8dce3}
.event-gallery-dot.active{background:var(--saffron)}

/* ===== Featured event + countdown ===== */
.event-feat-grid{display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;color:#fff;}
.event-feat-meta{display:flex;gap:20px;flex-wrap:wrap;color:rgba(255,255,255,0.8);font-size:0.875rem;margin-bottom:20px;}
.event-feat-actions{display:flex;gap:12px;flex-wrap:wrap;}

@media (max-width: 768px){.event-hero-shell{padding:24px 0}.event-hero-card{grid-template-columns:1fr;padding:22px;border-radius:20px}.event-hero-graphic{min-height:195px}.event-hero-orb{width:180px;height:180px}.event-hero-stack{width:min(100%,260px)}.event-hero-card-tile{padding:11px 13px;font-size:.9rem}.event-gallery-card{min-width:100%}.event-gallery-carousel{grid-template-columns:1fr}.event-gallery-control{display:none}.bc-event-feat-countdown{width:100%;overflow:auto}.bc-countdown-grid{min-width:285px}.event-feat-grid{grid-template-columns:1fr;text-align:center;gap:24px;}.event-feat-meta,.event-feat-actions{justify-content:center;}}
@media (max-width: 420px){.event-hero-title{font-size:2rem}.event-hero-pills span{font-size:.78rem;padding:7px 9px}.event-hero-stack{gap:8px}.event-hero-card-tile{font-size:.8rem}.event-gallery-body{padding:12px}.event-gallery-image{height:170px}}
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
            <li class="breadcrumb-item active" aria-current="page">Events</li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py-sm event-hero-shell" aria-labelledby="events-heading">
  <div class="container">
    <div class="bc-reveal event-hero-card">
      <div class="event-hero-copy">
        <div class="bc-section-label event-hero-label">Happening Across India</div>
        <h1 class="bc-section-title event-hero-title" id="events-heading">Conscious India Events</h1>
        <p class="bc-section-desc event-hero-desc">Pop-ups, exhibitions, workshops, summits and festivals — where conscious community comes together.</p>
        <div class="event-hero-pills">
          <span><i class="fa-solid fa-location-dot"></i> 12+ cities</span>
          <span><i class="fa-solid fa-seedling"></i> Verified makers</span>
          <span><i class="fa-solid fa-people-group"></i> 50k+ members</span>
        </div>
      </div>
      <div class="event-hero-graphic" aria-hidden="true">
        <div class="event-hero-orb"></div>
        <div class="event-hero-stack">
          <div class="event-hero-card-tile"><i class="fa-solid fa-calendar-days"></i> Curated experiences</div>
          <div class="event-hero-card-tile"><i class="fa-solid fa-store"></i> Stall and workshop booking</div>
          <div class="event-hero-card-tile"><i class="fa-solid fa-users"></i> Community-first events</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Event with Countdown -->
<section class="section-py-sm" style="background:linear-gradient(135deg,var(--saffron-dark),var(--gold-dark));">
  <div class="container">
    <div class="event-feat-grid bc-reveal">
      <div>
        <span style="padding:4px 12px;background:rgba(255,255,255,0.2);border-radius:var(--r-full);font-size:0.75rem;font-weight:700;margin-bottom:12px;display:inline-block;">⭐ FEATURED EVENT</span>
        <h2 style="font-family:var(--font-display);font-size:clamp(1.5rem,3vw,2.25rem);font-weight:800;margin-bottom:8px;">Sustainable Living Summit 2025</h2>
        <p style="color:rgba(255,255,255,0.85);font-size:1rem;line-height:1.6;margin-bottom:16px;">India's largest sustainability conference — 3 days of talks, workshops, brand showcases and networking.</p>
        <div class="event-feat-meta">
          <span><i class="fa-regular fa-calendar"></i> 15–17 Feb 2025</span>
          <span><i class="fa-solid fa-location-dot"></i> NSIC, New Delhi</span>
          <span><i class="fa-regular fa-circle-user"></i> 5,000+ Attendees Expected</span>
        </div>
        <div class="event-feat-actions">
          <button type="button" class="bc-btn bc-btn-primary" data-open-modal="event-stall-booking"><i class="fa-solid fa-store"></i> Book a Stall</button>
          <button type="button" class="bc-btn bc-btn-outline-white" data-open-modal="event-workshop-booking"><i class="fa-solid fa-chalkboard-user"></i> Organize a Workshop</button>
        </div>
      </div>
      <div class="bc-event-feat-countdown" id="bc-event-countdown">
        <div class="bc-countdown-title">Event Starts In</div>
        <div class="bc-countdown-grid">
          <div class="bc-countdown-unit">
            <span class="bc-countdown-val" id="cd-days">28</span>
            <span class="bc-countdown-lbl">Days</span>
          </div>
          <div class="bc-countdown-sep">:</div>
          <div class="bc-countdown-unit">
            <span class="bc-countdown-val" id="cd-hrs">14</span>
            <span class="bc-countdown-lbl">Hours</span>
          </div>
          <div class="bc-countdown-sep">:</div>
          <div class="bc-countdown-unit">
            <span class="bc-countdown-val" id="cd-min">32</span>
            <span class="bc-countdown-lbl">Mins</span>
          </div>
          <div class="bc-countdown-sep">:</div>
          <div class="bc-countdown-unit">
            <span class="bc-countdown-val" id="cd-sec">45</span>
            <span class="bc-countdown-lbl">Secs</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-py-sm" style="background:var(--bg-cream);" aria-labelledby="gallery-heading">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
      <div>
        <div class="bc-section-label">Visual moments</div>
        <h2 class="bc-section-title mb-0" id="gallery-heading">Event Gallery</h2>
      </div>
      <span class="small text-muted">Auto-playing highlights from recent Conscious India events</span>
    </div>
    <div class="event-gallery-carousel" id="event-gallery-carousel" data-carousel>
      <button class="event-gallery-control event-gallery-prev" type="button" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
      <div class="event-gallery-viewport">
        <div class="event-gallery-track">
          <article class="event-gallery-card">
            <div class="event-gallery-image landscape"></div>
            <div class="event-gallery-body"><strong>Craft Bazaar</strong><span>Curated artisan stories in motion</span></div>
          </article>
          <article class="event-gallery-card">
            <div class="event-gallery-image square"></div>
            <div class="event-gallery-body"><strong>Slow Living Jam</strong><span>Community-led workshops and demos</span></div>
          </article>
          <article class="event-gallery-card">
            <div class="event-gallery-image landscape"></div>
            <div class="event-gallery-body"><strong>Design for Earth</strong><span>Material stories and circular design</span></div>
          </article>
          <article class="event-gallery-card">
            <div class="event-gallery-image square"></div>
            <div class="event-gallery-body"><strong>Conscious Summit</strong><span>Thought leadership and maker showcases</span></div>
          </article>
        </div>
      </div>
      <button class="event-gallery-control event-gallery-next" type="button" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
      <div class="event-gallery-dots" aria-label="Carousel pagination"></div>
    </div>
  </div>
</section>

<section class="section-py-sm" style="background:var(--bg-cream);" aria-labelledby="live-events-heading">
  <div class="container"><div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4"><div><div class="bc-section-label">Now welcoming participants</div><h2 class="bc-section-title mb-0" id="live-events-heading">Live experiences</h2></div><span class="small text-muted">Select an event to book a stall or host a workshop</span></div><div class="row g-4">
    <div class="col-md-6 col-lg-4"><article class="ev-public-card"><div class="ev-public-art bazaar"><h3>Conscious Craft Bazaar</h3></div><div class="ev-public-body"><p class="mb-2 text-secondary">A considered market of makers, textiles, food and everyday sustainable discoveries.</p><div class="ev-public-meta"><span><i class="fa-regular fa-calendar"></i>12–13 August 2026 · 11 AM–8 PM</span><a class="ev-map-link" href="https://maps.google.com" target="_blank"><i class="fa-solid fa-location-dot"></i> Jayamahal Palace, Bengaluru</a></div><div class="ev-option"><span>Book a Stall</span><strong>₹1,999</strong></div><div class="ev-option"><span>Organize a Workshop</span><strong>₹1,599</strong></div><a href="/conscious/events/event-detail?event=bazaar" class="bc-event-cta-btn">View Event <i class="fa-solid fa-arrow-right"></i></a></div></article></div>
    <div class="col-md-6 col-lg-4"><article class="ev-public-card"><div class="ev-public-art festival"><h3>Slow Living Festival</h3></div><div class="ev-public-body"><p class="mb-2 text-secondary">A slower weekend of conscious food, craft, wellbeing and learning in the city.</p><div class="ev-public-meta"><span><i class="fa-regular fa-calendar"></i>24 August 2026 · 10 AM–7 PM</span><a class="ev-map-link" href="https://maps.google.com" target="_blank"><i class="fa-solid fa-location-dot"></i> Jio World Garden, Mumbai</a></div><div class="ev-option"><span>Book a Stall</span><strong>₹2,499</strong></div><div class="ev-option"><span>Organize a Workshop</span><strong>₹1,599</strong></div><a href="/conscious/events/event-detail?event=festival" class="bc-event-cta-btn">View Event <i class="fa-solid fa-arrow-right"></i></a></div></article></div>
    <div class="col-md-6 col-lg-4"><article class="ev-public-card"><div class="ev-public-art earth"><h3>Design for Earth</h3></div><div class="ev-public-body"><p class="mb-2 text-secondary">A design showcase for circular ideas, material stories and emerging makers.</p><div class="ev-public-meta"><span><i class="fa-regular fa-calendar"></i>06 September 2026 · 11 AM–6 PM</span><a class="ev-map-link" href="https://maps.google.com" target="_blank"><i class="fa-solid fa-location-dot"></i> India Habitat Centre, New Delhi</a></div><div class="ev-option"><span>Book a Stall</span><strong>₹1,999</strong></div><a href="/conscious/events/event-detail?event=earth" class="bc-event-cta-btn">View Event <i class="fa-solid fa-arrow-right"></i></a></div></article></div>
  </div></div>
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

<div class="ev-modal" id="event-stall-booking" aria-hidden="true"><div class="ev-modal-box" role="dialog" aria-modal="true" aria-labelledby="event-stall-title"><div class="ev-modal-head"><h2 id="event-stall-title">Book a Stall <small class="text-muted">· ₹1,999</small></h2><button class="ev-close" data-close-modal aria-label="Close">&times;</button></div><div class="ev-modal-body"><form data-static-submit><div class="ev-fields"><label><span class="ev-label">Full Name *</span><input class="ev-input" required></label><label><span class="ev-label">Email Address *</span><input class="ev-input" type="email" required></label><label><span class="ev-label">Phone Number *</span><input class="ev-input" type="tel" required></label><label><span class="ev-label">Business / Brand Name</span><input class="ev-input"></label><label class="wide"><span class="ev-label">Full Address *</span><textarea class="ev-textarea" required></textarea></label><label><span class="ev-label">City</span><input class="ev-input"></label><label class="wide"><span class="ev-label">What will you display?</span><input class="ev-input"></label></div><p class="mt-3 small"><b>Stall Booking Price: ₹1,999</b></p><button class="ev-btn" type="submit">Submit Stall Request</button></form><div class="ev-success"><i class="fa-solid fa-check"></i><h3>Request submitted</h3><p>Your stall booking request is ready for admin review.</p><button class="ev-btn alt" data-close-modal>Done</button></div></div></div></div>
<div class="ev-modal" id="event-workshop-booking" aria-hidden="true"><div class="ev-modal-box" role="dialog" aria-modal="true" aria-labelledby="event-workshop-title"><div class="ev-modal-head"><h2 id="event-workshop-title">Organize a Workshop <small class="text-muted">· ₹1,599</small></h2><button class="ev-close" data-close-modal aria-label="Close">&times;</button></div><div class="ev-modal-body"><form data-static-submit><div class="ev-fields"><label><span class="ev-label">Full Name *</span><input class="ev-input" required></label><label><span class="ev-label">Email Address *</span><input class="ev-input" type="email" required></label><label><span class="ev-label">Phone Number *</span><input class="ev-input" type="tel" required></label><label><span class="ev-label">Organizer / Brand Name</span><input class="ev-input"></label><label class="wide"><span class="ev-label">Workshop Name *</span><input class="ev-input" required></label><label><span class="ev-label">City</span><input class="ev-input"></label><label class="wide"><span class="ev-label">Workshop Description *</span><textarea class="ev-textarea" required></textarea></label><label class="wide"><span class="ev-label">Equipment / Setup Needs</span><textarea class="ev-textarea"></textarea></label></div><p class="mt-3 small"><b>Workshop Booking Price: ₹1,599</b></p><button class="ev-btn" type="submit">Submit Workshop Request</button></form><div class="ev-success"><i class="fa-solid fa-check"></i><h3>Request submitted</h3><p>Your workshop request is ready for admin review.</p><button class="ev-btn alt" data-close-modal>Done</button></div></div></div></div>

<button class="bc-back-top" id="bc-back-top" aria-label="Scroll back to top" title="Back to Top">
  <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bharatcraft.js?v=20261017-1"></script>
<script src="assets/js/event-management.js"></script>
<script>
(function () {
  function initGalleryCarousel() {
    var root = document.getElementById('event-gallery-carousel');
    if (!root) return;
    var track = root.querySelector('.event-gallery-track');
    var cards = root.querySelectorAll('.event-gallery-card');
    var dots = root.querySelector('.event-gallery-dots');
    var prev = root.querySelector('.event-gallery-prev');
    var next = root.querySelector('.event-gallery-next');
    if (!track || !cards.length) return;
    var index = 0;
    var cardCount = cards.length;
    var step = window.innerWidth <= 576 ? 1 : 2;
    function renderDots() {
      dots.innerHTML = '';
      for (var i = 0; i < Math.ceil(cardCount / step); i += 1) {
        var dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'event-gallery-dot' + (i === index ? ' active' : '');
        dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
        dot.addEventListener('click', function () {
          index = Number(this.dataset.index);
          updateCarousel();
        });
        dot.dataset.index = i;
        dots.appendChild(dot);
      }
    }
    function updateCarousel() {
      var maxIndex = Math.max(0, cardCount - step);
      index = Math.min(index, maxIndex);
      var translate = -(index * (100 / step));
      track.style.transform = 'translateX(' + translate + '%)';
      dots.querySelectorAll('.event-gallery-dot').forEach(function (dot, dotIndex) {
        dot.classList.toggle('active', dotIndex === Math.floor(index / step));
      });
    }
    prev.addEventListener('click', function () {
      index = Math.max(0, index - step);
      updateCarousel();
    });
    next.addEventListener('click', function () {
      index = Math.min(cardCount - step, index + step);
      updateCarousel();
    });
    renderDots();
    updateCarousel();
    setInterval(function () {
      index += step;
      if (index >= cardCount - step + 1) index = 0;
      updateCarousel();
    }, 3500);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initGalleryCarousel);
  else initGalleryCarousel();
})();
</script>
</body>
</html>