/* ============================================================
   Conscious Indi — Premium Indian Handcrafted Marketplace
   Main JavaScript — All Interactions & Animations
   Version: 1.0.0
   ============================================================ */

'use strict';

/* ──────────────────────────────────────────────
   1. DOM READY
   ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  initPreloader();
  initAnnouncementBar();
  initHeader();
  initMobileDrawer();
  initHeroParticles();
  initScrollAnimations();
  initProductTabs();
  initProductCarousel();
  initReviewCarousel();
  initWishlistButtons();
  initNewsletterForm();
  initBackToTop();
  initCounterAnimation();
  initProductCards();
  initSearchBar();
  initMegaMenu();
});

/* ──────────────────────────────────────────────
   2. PRELOADER
   ────────────────────────────────────────────── */
function initPreloader() {
  const preloader = document.getElementById('bc-preloader');
  if (!preloader) return;

  window.addEventListener('load', function () {
    setTimeout(function () {
      preloader.classList.add('loaded');
      document.body.style.overflow = '';
    }, 600);
  });

  // Fallback: remove after 3s even if load event was missed
  setTimeout(function () {
    if (preloader && !preloader.classList.contains('loaded')) {
      preloader.classList.add('loaded');
      document.body.style.overflow = '';
    }
  }, 3000);

  document.body.style.overflow = 'hidden';
}

/* ──────────────────────────────────────────────
   3. ANNOUNCEMENT BAR ROTATOR
   ────────────────────────────────────────────── */
function initAnnouncementBar() {
  const items = document.querySelectorAll('.bc-announce-item');
  if (!items.length) return;

  let current = 0;
  items[0].classList.add('active');

  function rotate() {
    items[current].classList.remove('active');
    current = (current + 1) % items.length;
    items[current].classList.add('active');
  }

  setInterval(rotate, 3800);

  // Close button
  const closeBtn = document.querySelector('.bc-announce-close');
  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      const bar = document.querySelector('.bc-announce-bar');
      if (bar) {
        bar.style.height = '0';
        bar.style.overflow = 'hidden';
        bar.style.transition = 'height 0.4s ease';
      }
    });
  }
}

/* ──────────────────────────────────────────────
   4. HEADER SCROLL BEHAVIOR
   ────────────────────────────────────────────── */
function initHeader() {
  const header = document.getElementById('bc-header');
  const mobileHeader = document.querySelector('.bc-header-mobile');

  if (!header && !mobileHeader) return;

  let lastScroll = 0;
  let ticking = false;

  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(function () {
        const scrollY = window.scrollY;

        // Add/remove scrolled class
        if (scrollY > 60) {
          header && header.classList.add('scrolled');
          mobileHeader && mobileHeader.classList.add('scrolled');
        } else {
          header && header.classList.remove('scrolled');
          mobileHeader && mobileHeader.classList.remove('scrolled');
        }

        lastScroll = scrollY;
        ticking = false;
      });
      ticking = true;
    }
  });
}

/* ──────────────────────────────────────────────
   5. MOBILE DRAWER
   ────────────────────────────────────────────── */
function initMobileDrawer() {
  const drawer   = document.getElementById('bc-mobile-drawer');
  const overlay  = document.getElementById('bc-overlay');
  const openBtn  = document.getElementById('bc-hamburger');
  const closeBtn = document.getElementById('bc-drawer-close');

  if (!drawer) return;

  function openDrawer() {
    drawer.classList.add('open');
    overlay && overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawer.classList.remove('open');
    overlay && overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  openBtn  && openBtn.addEventListener('click', openDrawer);
  closeBtn && closeBtn.addEventListener('click', closeDrawer);
  overlay  && overlay.addEventListener('click', closeDrawer);

  // Close on ESC key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer.classList.contains('open')) {
      closeDrawer();
    }
  });
}

/* ──────────────────────────────────────────────
   6. HERO PARTICLE DOTS
   ────────────────────────────────────────────── */
function initHeroParticles() {
  const dotsContainer = document.getElementById('bc-hero-dots');
  if (!dotsContainer) return;

  const count = 22;

  for (let i = 0; i < count; i++) {
    const dot = document.createElement('div');
    dot.classList.add('bc-hero-dot');

    const size   = Math.random() * 3 + 2;
    const left   = Math.random() * 100;
    const delay  = Math.random() * 20;
    const dur    = Math.random() * 15 + 12;
    const opac   = Math.random() * 0.4 + 0.2;

    dot.style.cssText = `
      width:  ${size}px;
      height: ${size}px;
      left:   ${left}%;
      animation-delay:    ${delay}s;
      animation-duration: ${dur}s;
      opacity: ${opac};
    `;

    dotsContainer.appendChild(dot);
  }
}

/* ──────────────────────────────────────────────
   7. SCROLL-TRIGGERED ANIMATIONS
   ────────────────────────────────────────────── */
function initScrollAnimations() {
  const targets = document.querySelectorAll('.bc-reveal, .bc-reveal-left, .bc-reveal-right');
  if (!targets.length || !('IntersectionObserver' in window)) {
    // Fallback: make all visible immediately
    targets.forEach(el => el.classList.add('visible'));
    return;
  }

  const observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
  );

  targets.forEach(el => observer.observe(el));
}

/* ──────────────────────────────────────────────
   8. PRODUCT FILTER TABS
   ────────────────────────────────────────────── */
function initProductTabs() {
  const tabs  = document.querySelectorAll('.bc-product-tab');
  const cards = document.querySelectorAll('.bc-product-card[data-category]');

  if (!tabs.length) return;

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      // Update active tab
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const filter = this.getAttribute('data-filter');

      cards.forEach(function (card) {
        if (filter === 'all' || card.getAttribute('data-category') === filter) {
          card.style.display = '';
          // Trigger re-animation
          card.classList.remove('visible');
          setTimeout(() => card.classList.add('visible'), 30);
        } else {
          card.style.display = 'none';
        }
      });

      document.dispatchEvent(new CustomEvent('bc:products-filtered'));
    });
  });
}

function initProductCarousel() {
  const carousel = document.querySelector('.bc-products-carousel');
  if (!carousel) return;

  const track = carousel.querySelector('.bc-products-grid');
  const prevBtn = carousel.querySelector('.bc-product-carousel-prev');
  const nextBtn = carousel.querySelector('.bc-product-carousel-next');
  const pagination = carousel.querySelector('.bc-product-carousel-pagination');
  if (!track) return;

  let current = 0;
  let maxIndex = 0;
  let slideStep = 0;
  let autoplay;
  let resizeTimer;
  let touchStartX = 0;

  function getVisibleCards() {
    return Array.from(track.querySelectorAll('.bc-product-card[data-category]'))
      .filter(card => card.style.display !== 'none');
  }

  function getGap() {
    const styles = window.getComputedStyle(track);
    return parseFloat(styles.columnGap || styles.gap) || 0;
  }

  function buildPagination() {
    if (!pagination) return;
    pagination.innerHTML = '';

    for (let i = 0; i <= maxIndex; i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.setAttribute('aria-label', 'Show product slide ' + (i + 1));
      dot.addEventListener('click', function () {
        goTo(i);
        restartAutoplay();
      });
      pagination.appendChild(dot);
    }
  }

  function updateCarousel(animate) {
    track.style.transition = animate === false ? 'none' : 'transform 0.45s var(--ease)';
    track.style.transform = 'translateX(-' + (current * slideStep) + 'px)';

    const dots = pagination ? pagination.querySelectorAll('button') : [];
    dots.forEach(function (dot, index) {
      dot.classList.toggle('active', index === current);
      dot.setAttribute('aria-current', index === current ? 'true' : 'false');
    });

    if (animate === false) {
      requestAnimationFrame(function () {
        track.style.transition = 'transform 0.45s var(--ease)';
      });
    }
  }

  function measureCarousel() {
    const cards = getVisibleCards();
    if (!cards.length) return;

    const viewportWidth = carousel.querySelector('.bc-products-carousel-viewport').getBoundingClientRect().width;
    const cardWidth = cards[0].getBoundingClientRect().width;
    const gap = getGap();
    const visibleCount = Math.max(1, Math.floor((viewportWidth + gap) / (cardWidth + gap)));

    slideStep = cardWidth + gap;
    maxIndex = Math.max(cards.length - visibleCount, 0);
    current = Math.min(current, maxIndex);

    const showControls = maxIndex > 0;
    if (prevBtn) prevBtn.style.display = showControls ? '' : 'none';
    if (nextBtn) nextBtn.style.display = showControls ? '' : 'none';
    if (pagination) pagination.style.display = showControls ? '' : 'none';

    buildPagination();
    updateCarousel(false);
    restartAutoplay();
  }

  function goTo(index) {
    if (maxIndex <= 0) return;
    current = (index + maxIndex + 1) % (maxIndex + 1);
    updateCarousel(true);
  }

  function stopAutoplay() {
    if (autoplay) window.clearInterval(autoplay);
  }

  function startAutoplay() {
    stopAutoplay();
    if (maxIndex > 0) {
      autoplay = window.setInterval(function () {
        goTo(current + 1);
      }, 4200);
    }
  }

  function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
  }

  prevBtn && prevBtn.addEventListener('click', function () {
    goTo(current - 1);
    restartAutoplay();
  });

  nextBtn && nextBtn.addEventListener('click', function () {
    goTo(current + 1);
    restartAutoplay();
  });

  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);
  carousel.addEventListener('focusin', stopAutoplay);
  carousel.addEventListener('focusout', startAutoplay);

  carousel.addEventListener('touchstart', function (e) {
    touchStartX = e.changedTouches[0].clientX;
    stopAutoplay();
  }, { passive: true });

  carousel.addEventListener('touchend', function (e) {
    const deltaX = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(deltaX) > 40) {
      goTo(current + (deltaX < 0 ? 1 : -1));
    }
    startAutoplay();
  }, { passive: true });

  window.addEventListener('resize', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(measureCarousel, 120);
  });

  document.addEventListener('bc:products-filtered', function () {
    current = 0;
    window.setTimeout(measureCarousel, 60);
  });

  measureCarousel();
}

/* ──────────────────────────────────────────────
   9. WISHLIST TOGGLE
   ────────────────────────────────────────────── */
function initReviewCarousel() {
  const carousel = document.querySelector('.bc-review-carousel');
  if (!carousel) return;

  const track = carousel.querySelector('.bc-review-track');
  const slides = track ? Array.from(track.querySelectorAll('.bc-review-card')) : [];
  const prevBtn = carousel.querySelector('.bc-review-prev');
  const nextBtn = carousel.querySelector('.bc-review-next');
  const pagination = carousel.querySelector('.bc-review-pagination');

  if (!track || !slides.length) return;

  let current = 0;
  let visibleCount = 1;
  let maxIndex = 0;
  let slideStep = 0;
  let autoplay;
  let resizeTimer;
  let touchStartX = 0;

  slides.forEach(function (slide) {
    slide.classList.add('visible');
  });

  function getVisibleCount() {
    return window.matchMedia('(max-width: 768px)').matches ? 1 : 2;
  }

  function getGap() {
    const styles = window.getComputedStyle(track);
    return parseFloat(styles.columnGap || styles.gap) || 0;
  }

  function buildPagination() {
    if (!pagination) return;
    pagination.innerHTML = '';

    for (let i = 0; i <= maxIndex; i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.setAttribute('aria-label', 'Show review set ' + (i + 1));
      dot.addEventListener('click', function () {
        goTo(i);
        restartAutoplay();
      });
      pagination.appendChild(dot);
    }
  }

  function updateCarousel(animate) {
    track.style.transition = animate === false ? 'none' : 'transform 0.45s var(--ease)';
    track.style.transform = 'translateX(-' + (current * slideStep) + 'px)';

    slides.forEach(function (slide, index) {
      const isVisible = index >= current && index < current + visibleCount;
      slide.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
    });

    if (pagination) {
      const dots = pagination.querySelectorAll('button');
      dots.forEach(function (dot, index) {
        dot.classList.toggle('active', index === current);
        dot.setAttribute('aria-current', index === current ? 'true' : 'false');
      });
    }

    if (animate === false) {
      requestAnimationFrame(function () {
        track.style.transition = 'transform 0.45s var(--ease)';
      });
    }
  }

  function measureCarousel() {
    visibleCount = getVisibleCount();
    maxIndex = Math.max(slides.length - visibleCount, 0);
    current = Math.min(current, maxIndex);
    slideStep = slides[0].getBoundingClientRect().width + getGap();

    const shouldShowControls = maxIndex > 0;
    [prevBtn, nextBtn].forEach(function (btn) {
      if (btn) btn.style.display = shouldShowControls ? '' : 'none';
    });
    if (pagination) pagination.style.display = shouldShowControls ? '' : 'none';

    buildPagination();
    updateCarousel(false);
  }

  function goTo(index) {
    if (maxIndex <= 0) return;
    current = (index + maxIndex + 1) % (maxIndex + 1);
    updateCarousel(true);
  }

  function stopAutoplay() {
    if (autoplay) window.clearInterval(autoplay);
  }

  function startAutoplay() {
    stopAutoplay();
    if (maxIndex > 0) {
      autoplay = window.setInterval(function () {
        goTo(current + 1);
      }, 4800);
    }
  }

  function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
  }

  prevBtn && prevBtn.addEventListener('click', function () {
    goTo(current - 1);
    restartAutoplay();
  });

  nextBtn && nextBtn.addEventListener('click', function () {
    goTo(current + 1);
    restartAutoplay();
  });

  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);
  carousel.addEventListener('focusin', stopAutoplay);
  carousel.addEventListener('focusout', startAutoplay);

  carousel.addEventListener('touchstart', function (e) {
    touchStartX = e.changedTouches[0].clientX;
    stopAutoplay();
  }, { passive: true });

  carousel.addEventListener('touchend', function (e) {
    const deltaX = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(deltaX) > 40) {
      goTo(current + (deltaX < 0 ? 1 : -1));
    }
    startAutoplay();
  }, { passive: true });

  window.addEventListener('resize', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(measureCarousel, 120);
  });

  measureCarousel();
  startAutoplay();
}

function initWishlistButtons() {
  const script = document.querySelector('script[src*="assets/js/bharatcraft.js"]');
  const apiBase = new URL('../../api/', script ? script.src : location.href);
  document.addEventListener('click', async function (event) {
    const button = event.target.closest('.bc-product-action-btn.wishlist');
    if (!button || button.disabled) return;
    event.preventDefault();
    const productId = button.dataset.productId;
    if (!productId) { showToast('Open the marketplace to save a published product.', 'neutral'); return; }
    button.disabled = true;
    try {
      const session = await fetch(new URL('auth/me.php', apiBase), {credentials:'same-origin', cache:'no-store'});
      if (!session.ok) throw new Error('Sign in to save products to your wishlist.');
      const identity = await session.json();
      const remove = button.classList.contains('wishlist-active');
      const url = new URL('user/wishlist.php', apiBase);
      if (remove) url.searchParams.set('productId', productId);
      const response = await fetch(url, {
        method: remove ? 'DELETE' : 'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-Token':identity.csrf_token || ''},
        body: remove ? undefined : JSON.stringify({productId})
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Your wishlist could not be updated. Please try again.');
      button.classList.toggle('wishlist-active', !remove);
      button.setAttribute('aria-pressed', remove ? 'false' : 'true');
      const icon=button.querySelector('i');
      if(icon) icon.className=remove ? 'fa-regular fa-heart' : 'fa-solid fa-heart';
      showToast(remove ? 'Removed from your wishlist.' : 'Saved to your wishlist.', 'green');
    } catch(error) { showToast(error.message || 'Unable to update your wishlist. Please try again.', 'neutral'); }
    finally { button.disabled=false; }
  });
}

/* ──────────────────────────────────────────────
   10. TOAST NOTIFICATIONS
   ────────────────────────────────────────────── */
function showToast(message, type) {
  // Remove existing toast
  const existing = document.querySelector('.bc-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'bc-toast';

  const colors = {
    saffron: { bg: '#FF9933', color: '#fff' },
    green:   { bg: '#138808', color: '#fff' },
    navy:    { bg: '#000080', color: '#fff' },
    neutral: { bg: '#4A3728', color: '#fff' }
  };

  const c = colors[type] || colors.neutral;

  toast.style.cssText = `
    position: fixed;
    bottom: 90px;
    right: 28px;
    background: ${c.bg};
    color: ${c.color};
    padding: 12px 20px;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    z-index: 9999;
    box-shadow: 0 8px 32px rgba(0,0,0,0.20);
    animation: toastIn 0.3s ease forwards;
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 280px;
  `;

  // Inject keyframe once
  if (!document.getElementById('bc-toast-style')) {
    const style = document.createElement('style');
    style.id = 'bc-toast-style';
    style.textContent = `
      @keyframes toastIn  { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
      @keyframes toastOut { from { opacity:1; transform: translateY(0); } to { opacity:0; transform: translateY(10px); } }
    `;
    document.head.appendChild(style);
  }

  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(function () {
    toast.style.animation = 'toastOut 0.3s ease forwards';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

/* ──────────────────────────────────────────────
   11. NEWSLETTER FORM
   ────────────────────────────────────────────── */
function initNewsletterForm() {
  const form  = document.getElementById('bc-newsletter-form');
  const input = document.getElementById('bc-newsletter-email');
  const btn   = document.getElementById('bc-newsletter-submit');

  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const email = input ? input.value.trim() : '';
    if (!email || !isValidEmail(email)) {
      input && shakeElement(input);
      return;
    }

    // Simulate submission
    if (btn) {
      btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Subscribing…';
      btn.disabled = true;
    }

    setTimeout(function () {
      if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Subscribed!';
        btn.style.background = 'var(--green)';
      }
      if (input) input.value = '';
      showToast('🎉 Welcome to Conscious Indi family!', 'green');

      setTimeout(function () {
        if (btn) {
          btn.innerHTML = 'Subscribe <i class="fa-solid fa-arrow-right"></i>';
          btn.style.background = '';
          btn.disabled = false;
        }
      }, 3000);
    }, 1200);
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function shakeElement(el) {
  el.style.animation = 'none';
  el.offsetHeight; // reflow
  const style = document.createElement('style');
  style.textContent = '@keyframes bcShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)} 40%{transform:translateX(8px)} 60%{transform:translateX(-6px)} 80%{transform:translateX(6px)} }';
  document.head.appendChild(style);
  el.style.animation = 'bcShake 0.5s ease';
  setTimeout(() => el.style.animation = '', 500);
}

/* ──────────────────────────────────────────────
   12. BACK TO TOP
   ────────────────────────────────────────────── */
function initBackToTop() {
  const btn = document.getElementById('bc-back-top');
  if (!btn) return;

  window.addEventListener('scroll', function () {
    if (window.scrollY > 400) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  }, { passive: true });

  btn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ──────────────────────────────────────────────
   13. COUNTER ANIMATION (Stats)
   ────────────────────────────────────────────── */
function initCounterAnimation() {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length || !('IntersectionObserver' in window)) return;

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

function animateCounter(el) {
  const target   = parseFloat(el.getAttribute('data-count'));
  const suffix   = el.getAttribute('data-suffix') || '';
  const prefix   = el.getAttribute('data-prefix') || '';
  const duration = 1800;
  const start    = performance.now();
  const isFloat  = el.getAttribute('data-float') === 'true';

  function update(now) {
    const elapsed  = now - start;
    const progress = Math.min(elapsed / duration, 1);
    // Ease-out cubic
    const eased  = 1 - Math.pow(1 - progress, 3);
    const current = target * eased;

    el.textContent = prefix + (isFloat ? current.toFixed(1) : Math.floor(current)) + suffix;

    if (progress < 1) requestAnimationFrame(update);
    else el.textContent = prefix + (isFloat ? target.toFixed(1) : target) + suffix;
  }

  requestAnimationFrame(update);
}

/* ──────────────────────────────────────────────
   14. PRODUCT CARD INTERACTIONS
   ────────────────────────────────────────────── */
function initProductCards() {
  // Quick view (placeholder)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.bc-product-action-btn.quickview');
    if (!btn) return;
    const card  = btn.closest('.bc-product-card');
    const title = card ? card.querySelector('.bc-product-title') : null;
    showToast(`👁 Quick view: ${title ? title.textContent.trim() : 'Product'}`, 'navy');
  });
}

/* ──────────────────────────────────────────────
   15. SEARCH BAR INTERACTIONS
   ────────────────────────────────────────────── */
function initSearchBar() {
  const searchInput = document.querySelector('.bc-search-input');
  const searchBtn   = document.querySelector('.bc-search-btn');

  if (!searchInput) return;

  searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      const query = this.value.trim();
      if (query) showToast(`🔍 Searching for "${query}"…`, 'navy');
    }
  });

  searchBtn && searchBtn.addEventListener('click', function () {
    const query = searchInput.value.trim();
    if (query) showToast(`🔍 Searching for "${query}"…`, 'navy');
  });

  // Mobile search
  const mobileInput = document.querySelector('.bc-mobile-search input');
  if (mobileInput) {
    mobileInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        const q = this.value.trim();
        if (q) showToast(`🔍 Searching for "${q}"…`, 'navy');
      }
    });
  }
}

/* ──────────────────────────────────────────────
   16. MEGA MENU ACCESSIBILITY
   ────────────────────────────────────────────── */
function initMegaMenu() {
  const navItems = document.querySelectorAll('.bc-nav-item.has-mega');

  navItems.forEach(function (item) {
    const menu = item.querySelector('.bc-mega-menu');
    if (!menu) return;

    let timer;

    item.addEventListener('mouseenter', function () {
      clearTimeout(timer);
      menu.style.opacity    = '1';
      menu.style.visibility = 'visible';
      menu.style.transform  = 'translateY(0)';
    });

    item.addEventListener('mouseleave', function () {
      timer = setTimeout(function () {
        menu.style.opacity    = '0';
        menu.style.visibility = 'hidden';
        menu.style.transform  = 'translateY(8px)';
      }, 120);
    });
  });
}

/* ──────────────────────────────────────────────
   17. SMOOTH CATEGORY CARD ENTRANCE
   ────────────────────────────────────────────── */
(function initCategoryEntrance() {
  if (!('IntersectionObserver' in window)) return;

  const grid = document.querySelector('.bc-categories-grid');
  if (!grid) return;

  const cards = grid.querySelectorAll('.bc-category-card');

  const observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) {
      cards.forEach(function (card, i) {
        setTimeout(function () {
          card.style.opacity   = '1';
          card.style.transform = 'translateY(0)';
        }, i * 70);
      });
      observer.unobserve(grid);
    }
  }, { threshold: 0.1 });

  // Set initial state
  cards.forEach(function (card) {
    card.style.opacity    = '0';
    card.style.transform  = 'translateY(24px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  });

  observer.observe(grid);
})();

/* ──────────────────────────────────────────────
   18. VENDOR CARD HOVER TILT
   ────────────────────────────────────────────── */
(function initCardTilt() {
  const cards = document.querySelectorAll('.bc-vendor-card, .bc-why-card');

  cards.forEach(function (card) {
    card.addEventListener('mousemove', function (e) {
      const rect   = this.getBoundingClientRect();
      const x      = (e.clientX - rect.left) / rect.width  - 0.5;
      const y      = (e.clientY - rect.top)  / rect.height - 0.5;
      const tiltX  = y * 6;
      const tiltY  = x * -6;
      this.style.transform = `translateY(-4px) perspective(600px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = '';
    });
  });
})();

/* ──────────────────────────────────────────────
   19. LAZY-LOAD IMAGES (if real images used)
   ────────────────────────────────────────────── */
(function initLazyLoad() {
  if (!('IntersectionObserver' in window)) return;

  const imgs = document.querySelectorAll('img[data-src]');
  if (!imgs.length) return;

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.getAttribute('data-src');
        img.removeAttribute('data-src');
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '200px' });

  imgs.forEach(img => observer.observe(img));
})();

/* ──────────────────────────────────────────────
   20. REGIONAL CRAFT CARD HOVER TOOLTIP
   ────────────────────────────────────────────── */
(function initRegionCards() {
  const cards = document.querySelectorAll('.bc-region-card');

  cards.forEach(function (card) {
    card.addEventListener('click', function (e) {
      e.preventDefault();
      const state = this.querySelector('.bc-region-state');
      const craft = this.querySelector('.bc-region-craft');
      if (state && craft) {
        showToast(`🗺 Exploring ${state.textContent} — ${craft.textContent}`, 'navy');
      }
    });
  });
})();

/* ──────────────────────────────────────────────
   21. FESTIVAL CARD ENTRANCE
   ────────────────────────────────────────────── */
(function initFestivalCards() {
  if (!('IntersectionObserver' in window)) return;

  const grid = document.querySelector('.bc-festival-grid');
  if (!grid) return;

  const cards = grid.querySelectorAll('.bc-festival-card');

  const observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) {
      cards.forEach(function (card, i) {
        card.style.opacity    = '0';
        card.style.transform  = 'translateY(24px)';
        card.style.transition = `opacity 0.5s ease ${i * 100}ms, transform 0.5s ease ${i * 100}ms`;

        setTimeout(function () {
          card.style.opacity   = '1';
          card.style.transform = 'translateY(0)';
        }, 60 + i * 100);
      });
      observer.unobserve(grid);
    }
  }, { threshold: 0.1 });

  observer.observe(grid);
})();

/* ──────────────────────────────────────────────
   22. SMOOTH SCROLL FOR ANCHOR LINKS
   ────────────────────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
  anchor.addEventListener('click', function (e) {
    const href   = this.getAttribute('href');
    if (href === '#') return;
    const target = document.querySelector(href);
    if (target) {
      e.preventDefault();
      const headerH = document.querySelector('.bc-header')
                   || document.querySelector('.bc-header-mobile');
      const offset  = headerH ? headerH.offsetHeight + 16 : 80;
      const top     = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    }
  });
});

/* ──────────────────────────────────────────────
   23. ARTISAN PROFILE CARD INTERACTION
   ────────────────────────────────────────────── */
(function initArtisanCards() {
  document.querySelectorAll('.bc-artisan-profile-card').forEach(function (card) {
    card.addEventListener('click', function () {
      const name  = this.querySelector('.bc-artisan-name');
      const craft = this.querySelector('.bc-artisan-craft');
      if (name) showToast(`🧑‍🎨 Viewing ${name.textContent}'s store — ${craft ? craft.textContent : ''}`, 'saffron');
    });
  });
})();

/* ──────────────────────────────────────────────
   24. HEADER SEARCH CATEGORY LABEL UPDATE
   ────────────────────────────────────────────── */
(function initSearchCategory() {
  const select = document.querySelector('.bc-search-cat');
  if (!select) return;

  select.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex].text;
    const input = this.closest('.bc-search-box')?.querySelector('.bc-search-input');
    if (input && selected !== 'All Categories') {
      input.placeholder = `Search in ${selected}…`;
    } else if (input) {
      input.placeholder = 'Search handcrafted products, artisans…';
    }
  });
})();

/* ──────────────────────────────────────────────
   25. PERFORMANCE: PASSIVE SCROLL LISTENERS
   ────────────────────────────────────────────── */
// All scroll listeners above use { passive: true } where applicable
// This wrapper ensures consistent performance
window.addEventListener('scroll', function () {
  // Handled individually per module above
}, { passive: true });

/* ──────────────────────────────────────────────
   26. EVENTS — FILTER TABS
   ────────────────────────────────────────────── */
(function initEventTabs() {
  const tabs     = document.querySelectorAll('.bc-event-tab');
  const cards    = document.querySelectorAll('.bc-event-card[data-etype]');
  const featured = document.querySelector('.bc-event-featured[data-etype]');
  if (!tabs.length) return;

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const filter = this.getAttribute('data-efilter');

      // Regular cards
      cards.forEach(function (card) {
        const show = filter === 'all' || card.getAttribute('data-etype') === filter;
        card.style.display    = show ? ''       : 'none';
        card.style.opacity    = show ? '0'      : '';
        card.style.transform  = show ? 'translateY(18px)' : '';
        if (show) {
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              card.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
              card.style.opacity    = '1';
              card.style.transform  = 'translateY(0)';
            });
          });
        }
      });

      // Featured event
      if (featured) {
        const featType = featured.getAttribute('data-etype');
        featured.style.display = (filter === 'all' || filter === featType) ? '' : 'none';
      }
    });
  });
})();

/* ──────────────────────────────────────────────
   27. EVENTS — LIVE COUNTDOWN TIMER
   ────────────────────────────────────────────── */
(function initCountdown() {
  const daysEl = document.getElementById('cd-days');
  const hrsEl  = document.getElementById('cd-hrs');
  const minEl  = document.getElementById('cd-min');
  const secEl  = document.getElementById('cd-sec');

  if (!daysEl) return;

  // Target: July 15, 2025 10:00 AM IST
  const eventDate = new Date('2025-07-15T10:00:00+05:30').getTime();

  function pad(n) { return String(n).padStart(2, '0'); }

  function tick() {
    const now  = Date.now();
    let   diff = eventDate - now;

    if (diff <= 0) {
      // Event has started
      daysEl.textContent = '00';
      hrsEl.textContent  = '00';
      minEl.textContent  = '00';
      secEl.textContent  = '00';
      return;
    }

    const days = Math.floor(diff / 86400000);       diff -= days * 86400000;
    const hrs  = Math.floor(diff / 3600000);        diff -= hrs  * 3600000;
    const mins = Math.floor(diff / 60000);          diff -= mins * 60000;
    const secs = Math.floor(diff / 1000);

    // Animate digit change
    function update(el, val) {
      const padded = pad(val);
      if (el.textContent !== padded) {
        el.style.transform  = 'translateY(-4px)';
        el.style.opacity    = '0.4';
        el.style.transition = 'all 0.15s ease';
        setTimeout(function () {
          el.textContent      = padded;
          el.style.transform  = '';
          el.style.opacity    = '';
        }, 150);
      }
    }

    update(daysEl, days);
    update(hrsEl,  hrs);
    update(minEl,  mins);
    update(secEl,  secs);
  }

  tick();
  setInterval(tick, 1000);
})();

/* ──────────────────────────────────────────────
   28. EVENTS — EVENT CARD CLICK (Notify / Register toast)
   ────────────────────────────────────────────── */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.bc-event-cta-btn');
  if (!btn) return;

  const card  = btn.closest('.bc-event-card');
  const title = card ? card.querySelector('.bc-event-card-title') : null;
  const text  = btn.textContent.trim();

  if (text.startsWith('Notify')) {
    e.preventDefault();
    showToast('🔔 You\'ll be notified when registration opens!', 'navy');
  } else if (text.startsWith('Set Reminder')) {
    e.preventDefault();
    showToast('📅 Reminder set! We\'ll notify you before the event.', 'green');
  } else if (title) {
    // Let natural navigation happen but show a toast
    showToast('🎟 Registering for: ' + title.textContent.trim().substring(0, 35) + '…', 'saffron');
  }
});

/* ──────────────────────────────────────────────
   29. SUSTAINABLE BRANDS — CATEGORY FILTER TABS
   ────────────────────────────────────────────── */
(function initBrandTabs() {
  const tabs  = document.querySelectorAll('.bc-brand-tab');
  const cards = document.querySelectorAll('.bc-vendor-card[data-bcat]');
  if (!tabs.length) return;

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const filter = this.getAttribute('data-bfilter');

      cards.forEach(function (card) {
        const cats = (card.getAttribute('data-bcat') || '').split(',');
        const show = filter === 'all' || cats.includes(filter);
        card.style.display   = show ? '' : 'none';
        card.style.opacity   = show ? '0' : '';
        card.style.transform = show ? 'translateY(14px)' : '';
        if (show) {
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            });
          });
        }
      });
    });
  });
})();

/* ──────────────────────────────────────────────
   10. GLOBAL UI LOGIC (Modals, Accordions, Tabs)
   ────────────────────────────────────────────── */

// Modal Utility
function showModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }
}

// Toast Utility
function showToast(message, type = 'info') {
  const toastContainer = document.getElementById('bc-toast-container');
  if (!toastContainer) {
    const container = document.createElement('div');
    container.id = 'bc-toast-container';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
  }
  
  const toastId = 'toast-' + Date.now();
  const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  
  document.getElementById('bc-toast-container').insertAdjacentHTML('beforeend', toastHtml);
  const toastEl = document.getElementById(toastId);
  const bsToast = new bootstrap.Toast(toastEl);
  bsToast.show();
  
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
// Form Validation Logic
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
      showToast('Please fill in all required fields correctly.', 'error');
    }
    form.classList.add('was-validated');
  }, false);
});


// Loading Skeletons Toggle (Example)
function toggleLoading(selector, show) {
  const elements = document.querySelectorAll(selector);
  elements.forEach(el => {
    if (show) {
      el.classList.add('bc-skeleton');
    } else {
      el.classList.remove('bc-skeleton');
    }
  });
}

/* --------------------------------------------------------------------------
   Influencer Registration Route
   -------------------------------------------------------------------------- */
(function initInfluencerRegistration() {
  function setupInfluencerRegistration() {
    const triggers = document.querySelectorAll('.bc-header-actions .bc-btn-influencer');
    if (!triggers.length) return;

    triggers.forEach(function (trigger) {
      trigger.setAttribute('href', 'frontend/modules/influencer/auth/register.php');
      trigger.setAttribute('aria-haspopup', 'false');
      trigger.innerHTML = '<i class="fa-solid fa-star" aria-hidden="true"></i> Register as an Influencer';
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupInfluencerRegistration);
  else setupInfluencerRegistration();
})();
