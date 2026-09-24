/* ============================================================
   Conscious Indi — Influencer Panel Interactions
   ------------------------------------------------------------
   Scoped to /influencer/*.html. Load AFTER bharatcraft.js.

   Replaces the small inline <script> block that was previously
   copy-pasted at the bottom of every influencer page (sidebar
   sub-menu toggle + FAQ toggle only — everything else below was
   simply missing) with one shared file, mirroring vendor-panel.js,
   customer-panel.js and admin-panel.js so every panel behaves
   identically: mobile sidebar drawer, desktop sidebar collapse,
   topbar notification/profile dropdowns, and chart period-button
   active state.

   Every init function guards on the elements it needs, so this
   file is safe to load on any influencer page even before every
   page has been rebuilt with the matching markup.
   ============================================================ */
'use strict';

(function () {

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    initSidebarSubmenus();
    initFaqAccordions();
    initSidebarCollapse();
    initMobileSidebarDrawer();
    initTopbarDropdowns();
    initChartPeriodToggle();
    initActionFeedback();
    initDestructiveConfirm();
    initTableActionFeedback();
  });

  /* ----------------------------------------------------------
     Sidebar sub-menu expand/collapse (nav items whose href is
     "#" and are followed by a .ci-sidebar-sub panel)
     ---------------------------------------------------------- */
  function initSidebarSubmenus() {
    document.querySelectorAll('.ci-sidebar-nav-item').forEach(function (item) {
      item.addEventListener('click', function (e) {
        var href = this.getAttribute('href');
        if (href && href !== '#') return; // real link, let it navigate
        var sub = this.nextElementSibling;
        if (sub && sub.classList.contains('ci-sidebar-sub')) {
          e.preventDefault();
          sub.classList.toggle('open');
        }
      });
    });
  }

  /* ---------------------------------------------------------- FAQ accordion ---------------------------------------------------------- */
  function initFaqAccordions() {
    document.querySelectorAll('.ci-faq-question').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = this.closest('.ci-faq-item');
        if (item) item.classList.toggle('open');
      });
    });
  }

  /* ----------------------------------------------------------
     Desktop sidebar collapse (shrinks to icon rail)
     ---------------------------------------------------------- */
  function initSidebarCollapse() {
    var sidebar = document.getElementById('ci-sidebar');
    var btn = document.getElementById('ci-sidebar-collapse-btn');
    if (!sidebar || !btn) return;
    btn.addEventListener('click', function () {
      sidebar.classList.toggle('collapsed');
    });
  }

  /* ----------------------------------------------------------
     Mobile sidebar drawer — hamburger opens, overlay/ESC/nav-tap
     close. Auto-closes if the window is resized back to desktop.
     ---------------------------------------------------------- */
  function initMobileSidebarDrawer() {
    var sidebar = document.getElementById('ci-sidebar');
    var openBtn = document.getElementById('ci-sidebar-hamburger');
    var overlay = document.getElementById('ci-sidebar-overlay');
    if (!sidebar || !openBtn) return;

    function openDrawer() {
      sidebar.classList.add('open');
      if (overlay) overlay.classList.add('show');
      document.body.style.overflow = 'hidden';
      openBtn.setAttribute('aria-expanded', 'true');
    }
    function closeDrawer() {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('show');
      document.body.style.overflow = '';
      openBtn.setAttribute('aria-expanded', 'false');
    }

    openBtn.addEventListener('click', openDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) closeDrawer();
    });

    sidebar.querySelectorAll('.ci-sidebar-nav-item').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 1024) closeDrawer();
      });
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024) closeDrawer();
    });
  }

  /* ----------------------------------------------------------
     Notification + profile dropdowns — click trigger to open,
     click outside or ESC to close, opening one closes the other.
     ---------------------------------------------------------- */
  function initTopbarDropdowns() {
    var dropdowns = document.querySelectorAll('.ci-topbar-dropdown');
    if (!dropdowns.length) return;

    dropdowns.forEach(function (dd) {
      var trigger = dd.querySelector('.ci-dropdown-trigger, .ci-profile-trigger');
      if (!trigger) return;
      trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = dd.classList.contains('open');
        dropdowns.forEach(function (other) { other.classList.remove('open'); });
        if (!isOpen) dd.classList.add('open');
      });
    });

    document.addEventListener('click', function () {
      dropdowns.forEach(function (dd) { dd.classList.remove('open'); });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') dropdowns.forEach(function (dd) { dd.classList.remove('open'); });
    });
  }

  /* ----------------------------------------------------------
     Chart period buttons (7D/30D/90D/1Y) — visual active state
     only. No data-swapping logic exists yet, so this doesn't
     invent any; it just makes the control respond to clicks.
     ---------------------------------------------------------- */
  function initChartPeriodToggle() {
    document.querySelectorAll('.ci-chart-period-btns').forEach(function (group) {
      var btns = group.querySelectorAll('.ci-period-btn');
      btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          btns.forEach(function (b) { b.classList.remove('active'); });
          this.classList.add('active');
        });
      });
    });
  }

  /* ----------------------------------------------------------
     Primary action buttons (Save / Update / Submit / Request /
     Reorder / Reply / Apply / Redeem / Upload / Discard, etc.)
     This is a frontend-only mock with no backend, so these
     buttons never had anything to call and clicking them did
     nothing at all — the single biggest "this feels broken"
     complaint in a mocked-up dashboard. Scoped to the main
     content area only, so topbar/sidebar/chart/FAQ controls
     that already have their own behaviour are left alone.
     ---------------------------------------------------------- */
  function initActionFeedback() {
    var main = document.querySelector('.ci-dash-main');
    if (!main) return;
    main.querySelectorAll('button.bc-btn').forEach(function (btn) {
      if (btn.closest('.ci-dash-topbar')) return;
      if (btn.closest('.ci-dropdown-panel')) return;
      if (btn.closest('.ci-chart-period-btns')) return;
      if (btn.closest('.ci-faq-item')) return;
      if (btn.classList.contains('danger')) return; // handled by initDestructiveConfirm

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (btn.disabled) return;
        var label = btn.textContent.replace(/\s+/g, ' ').trim() || 'Action';
        var isDiscard = /discard|cancel/i.test(label);
        btn.disabled = true;
        var prevOpacity = btn.style.opacity;
        btn.style.opacity = '0.65';
        setTimeout(function () {
          btn.disabled = false;
          btn.style.opacity = prevOpacity;
          if (typeof showToast === 'function') {
            showToast(isDiscard ? (label + ' — done') : (label + ' ✓'), isDiscard ? 'neutral' : 'success');
          }
        }, 400);
      });
    });
  }

  /* ----------------------------------------------------------
     Destructive actions (anything with a "danger" class) ask
     for confirmation first, then fade the row out and confirm
     with a toast — instead of silently doing nothing, or worse,
     silently "succeeding" with no way back if it were wired to
     a real backend later.
     ---------------------------------------------------------- */
  function initDestructiveConfirm() {
    document.querySelectorAll('.danger').forEach(function (btn) {
      if (btn.tagName !== 'BUTTON' && btn.tagName !== 'A') return;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var label = (btn.getAttribute('title') || btn.textContent || 'this item').trim();
        var thing = (label.toLowerCase() === 'delete' || label === '') ? 'this item' : label;
        var ok = window.confirm('Delete ' + thing + '? This can\'t be undone.');
        if (!ok) return;
        var row = btn.closest('tr') || btn.closest('.ci-list-item') || btn.closest('.ci-card-item');
        if (row) {
          row.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
          row.style.opacity = '0';
          row.style.transform = 'translateX(-8px)';
          setTimeout(function () { row.remove(); }, 250);
        }
        if (typeof showToast === 'function') showToast('Deleted successfully', 'success');
      });
    });
  }

  /* ----------------------------------------------------------
     Icon-only table row actions that are real <a href> links
     already navigate. The ones that are plain <button>s with no
     destination did nothing at all — give them the same
     "acknowledged" toast feedback.
     ---------------------------------------------------------- */
  function initTableActionFeedback() {
    document.querySelectorAll('button.ci-table-action-btn').forEach(function (btn) {
      if (btn.classList.contains('danger')) return; // handled by initDestructiveConfirm
      btn.addEventListener('click', function () {
        var label = (btn.getAttribute('title') || 'Action').trim();
        if (typeof showToast === 'function') showToast(label + ' ✓', 'navy');
      });
    });
  }

})();
