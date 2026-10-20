/* ============================================================
   Conscious Indi — Eco Homepage Behaviour
   Scoped to the homepage. Reuses the site-wide toast and cart
   badge so existing shop functionality keeps working.
   ============================================================ */
(function () {
  'use strict';

  var root = document.querySelector('.eco-home');
  if (!root) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── Toast: use the site-wide one when available ────────── */
  function notify(message, tone) {
    if (typeof window.showToast === 'function') {
      window.showToast(message, tone || 'green');
      return;
    }
    var el = document.querySelector('.eco-fallback-toast');
    if (!el) {
      el = document.createElement('div');
      el.className = 'eco-fallback-toast';
      el.setAttribute('role', 'status');
      el.style.cssText =
        'position:fixed;left:50%;bottom:28px;transform:translateX(-50%);z-index:4000;' +
        'background:#1E3527;color:#fff;padding:13px 22px;border-radius:999px;' +
        'font:600 .8125rem/1 "DM Sans",sans-serif;box-shadow:0 12px 34px rgba(0,0,0,.24);' +
        'opacity:0;transition:opacity .25s ease';
      document.body.appendChild(el);
    }
    el.textContent = message;
    requestAnimationFrame(function () { el.style.opacity = '1'; });
    window.clearTimeout(el._t);
    el._t = window.setTimeout(function () { el.style.opacity = '0'; }, 2600);
  }

  /* ── 1. Scroll reveal ───────────────────────────────────── */
  (function initReveal() {
    var items = root.querySelectorAll('.eco-rise');
    if (!items.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-in'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        io.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    items.forEach(function (el) { io.observe(el); });
  })();

  /* ── 2. Collection tabs (Featured / Bestsellers / New) ──── */
  (function initTabs() {
    var tablist = root.querySelector('[data-eco-tablist]');
    if (!tablist) return;

    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('.eco-tab'));
    if (!tabs.length) return;

    function select(tab, focus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.setAttribute('tabindex', on ? '0' : '-1');
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
      if (focus) tab.focus();
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () { select(tab, false); });
      tab.addEventListener('keydown', function (e) {
        var i = tabs.indexOf(tab);
        var next = null;
        if (e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
        else if (e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
        else if (e.key === 'Home') next = tabs[0];
        else if (e.key === 'End') next = tabs[tabs.length - 1];
        if (!next) return;
        e.preventDefault();
        select(next, true);
      });
    });
  })();

  /* ── 3. Wishlist ────────────────────────────────────────── */
  root.addEventListener('click', function (e) {
    var btn = e.target.closest('.eco-tool[data-act="wish"]');
    if (!btn) return;

    var card = btn.closest('[data-product]');
    var name = card ? card.getAttribute('data-name') : 'Product';
    var icon = btn.querySelector('i');
    var on = btn.classList.toggle('wishlist-active');

    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    btn.setAttribute('aria-label', (on ? 'Remove ' : 'Save ') + name + (on ? ' from' : ' to') + ' wishlist');
    if (icon) icon.classList.replace(on ? 'fa-regular' : 'fa-solid', on ? 'fa-solid' : 'fa-regular');

    notify(on ? 'Saved to wishlist — ' + name : 'Removed from wishlist', on ? 'saffron' : 'neutral');
  });

  /* ── 4. Add to cart (keeps the header cart badge in sync) ─ */
  function bumpCartBadge() {
    document.querySelectorAll('.bc-action-badge').forEach(function (badge) {
      var count = parseInt(badge.textContent, 10);
      if (isNaN(count)) count = 0;
      badge.textContent = count + 1;
      badge.style.transform = 'scale(1.45)';
      window.setTimeout(function () { badge.style.transform = ''; }, 220);
    });

    var link = document.querySelector('.bc-header-actions a[href="cart.html"]');
    var badge = link && link.querySelector('.bc-action-badge');
    if (link && badge) link.setAttribute('aria-label', 'Cart (' + badge.textContent + ' items)');
  }

  function addToCart(btn) {
    var card = btn.closest('[data-product]');
    var name = card ? card.getAttribute('data-name') : 'Product';

    if (btn.classList.contains('is-added')) return;

    var original = btn.innerHTML;
    btn.classList.add('is-added');
    btn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Added';
    bumpCartBadge();
    notify('Added to cart — ' + name, 'green');

    window.setTimeout(function () {
      btn.classList.remove('is-added');
      btn.innerHTML = original;
    }, 1800);
  }

  root.addEventListener('click', function (e) {
    var btn = e.target.closest('.eco-add');
    if (!btn) return;
    e.preventDefault();
    addToCart(btn);
  });

  /* ── 5. Quick view ──────────────────────────────────────── */
  (function initQuickView() {
    var backdrop = document.getElementById('eco-quickview');
    if (!backdrop) return;

    var dialog = backdrop.querySelector('.eco-qv');
    var img = backdrop.querySelector('[data-qv="image"]');
    var brand = backdrop.querySelector('[data-qv="brand"]');
    var title = backdrop.querySelector('[data-qv="title"]');
    var stars = backdrop.querySelector('[data-qv="stars"]');
    var price = backdrop.querySelector('[data-qv="price"]');
    var attrs = backdrop.querySelector('[data-qv="attrs"]');
    var desc = backdrop.querySelector('[data-qv="desc"]');
    var link = backdrop.querySelector('[data-qv="link"]');
    var addBtn = backdrop.querySelector('[data-qv="add"]');
    var lastFocus = null;

    function open(card) {
      var name = card.getAttribute('data-name') || 'Product';
      var media = card.querySelector('.eco-prod-media img');

      if (img && media) {
        img.src = media.currentSrc || media.src;
        img.alt = media.alt || name;
      }
      if (brand) brand.textContent = card.getAttribute('data-brand') || '';
      if (title) title.textContent = name;
      if (desc) desc.textContent = card.getAttribute('data-desc') || '';
      if (link) link.href = card.getAttribute('data-href') || 'product-detail.html';
      if (addBtn) addBtn.setAttribute('data-name', name);

      var rating = card.querySelector('.eco-stars');
      if (stars) stars.innerHTML = rating ? rating.innerHTML : '';

      var priceRow = card.querySelector('.eco-price');
      if (price) price.innerHTML = priceRow ? priceRow.innerHTML : '';

      var chips = card.querySelector('.eco-attrs');
      if (attrs) attrs.innerHTML = chips ? chips.innerHTML : '';

      lastFocus = document.activeElement;
      backdrop.classList.add('is-open');
      backdrop.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      window.setTimeout(function () {
        var close = backdrop.querySelector('.eco-qv-close');
        if (close) close.focus();
      }, 60);
    }

    function close() {
      backdrop.classList.remove('is-open');
      backdrop.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    root.addEventListener('click', function (e) {
      var btn = e.target.closest('.eco-tool[data-act="quickview"]');
      if (!btn) return;
      var card = btn.closest('[data-product]');
      if (card) open(card);
    });

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop || e.target.closest('.eco-qv-close')) close();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && backdrop.classList.contains('is-open')) close();
    });

    // Keep focus inside the dialog while it is open.
    dialog && dialog.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var focusables = dialog.querySelectorAll('a[href], button:not([disabled])');
      if (!focusables.length) return;
      var first = focusables[0];
      var last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });

    addBtn && addBtn.addEventListener('click', function () {
      bumpCartBadge();
      notify('Added to cart — ' + (addBtn.getAttribute('data-name') || 'Product'), 'green');
      close();
    });
  })();

  /* ── 6. Counting figures ────────────────────────────────── */
  (function initFigures() {
    var figures = root.querySelectorAll('[data-count]');
    if (!figures.length || !('IntersectionObserver' in window)) return;

    function run(el) {
      var target = parseFloat(el.getAttribute('data-count'));
      if (isNaN(target)) return;
      var suffix = el.getAttribute('data-suffix') || '';
      var decimals = (el.getAttribute('data-count').split('.')[1] || '').length;

      if (reduceMotion) {
        el.textContent = target.toFixed(decimals) + suffix;
        return;
      }

      var start = null;
      var duration = 1500;
      function step(now) {
        if (start === null) start = now;
        var p = Math.min((now - start) / duration, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = (target * eased).toFixed(decimals) + suffix;
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        run(entry.target);
        io.unobserve(entry.target);
      });
    }, { threshold: 0.5 });

    figures.forEach(function (el) { io.observe(el); });
  })();

  /* ── 7. Newsletter ──────────────────────────────────────── */
  (function initNewsletter() {
    var form = document.getElementById('eco-newsletter-form');
    if (!form) return;

    var input = form.querySelector('.eco-news-input');
    var submit = form.querySelector('.eco-news-submit');
    var status = document.getElementById('eco-newsletter-status');

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var value = (input.value || '').trim();

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)) {
        if (status) status.textContent = 'Please enter a valid email address.';
        input.setAttribute('aria-invalid', 'true');
        input.focus();
        notify('Please enter a valid email address', 'neutral');
        return;
      }

      input.removeAttribute('aria-invalid');
      var label = submit.innerHTML;
      submit.disabled = true;
      submit.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Subscribed';
      if (status) status.textContent = 'Thanks — check your inbox to confirm your subscription.';
      notify('You are on the list. Welcome aboard.', 'green');

      window.setTimeout(function () {
        submit.disabled = false;
        submit.innerHTML = label;
        input.value = '';
      }, 2600);
    });
  })();
})();
