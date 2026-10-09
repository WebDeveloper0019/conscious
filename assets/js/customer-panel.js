/* Conscious Indi — Customer panel shared behaviour + authenticated data hydration. */
'use strict';

(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, function (ch) {
      return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;' })[ch];
    });
  }

  function money(value) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Number(value || 0));
  }

  function dateText(value) {
    if (!value) return '—';
    var d = new Date(String(value).replace(' ', 'T') + (String(value).includes('Z') ? '' : 'Z'));
    return Number.isNaN(d.getTime()) ? String(value) : d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function relativeText(value) {
    if (!value) return '—';
    var d = new Date(String(value).replace(' ', 'T') + (String(value).includes('Z') ? '' : 'Z'));
    if (Number.isNaN(d.getTime())) return String(value);
    var seconds = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
    return dateText(value);
  }

  function initials(user) {
    return ((user.firstName || user.first_name || '').charAt(0) + (user.lastName || user.last_name || '').charAt(0)).toUpperCase() || 'U';
  }

  async function getJson(url) {
    var response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    var data = await response.json().catch(function () { return {}; });
    if (!response.ok || data.success === false) {
      var error = new Error(data.message || 'Request failed.');
      error.status = response.status;
      throw error;
    }
    return data;
  }

  function csrfToken() {
    var stored = window.sessionStorage.getItem('ci_csrf_token');
    if (stored) return stored;
    var match = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
  }

  async function hydrateIdentity() {
    try {
      var data = await getJson('../api/auth/me.php');
      var user = data.user || {};
      var fullName = [user.firstName || user.first_name, user.lastName || user.last_name].filter(Boolean).join(' ') || 'Customer';
      var avatar = initials(user);
      document.querySelectorAll('.ci-sidebar-user-name, .ci-profile-trigger-name').forEach(function (el) { el.textContent = fullName; });
      document.querySelectorAll('.ci-sidebar-user-email').forEach(function (el) { el.textContent = user.email || ''; });
      document.querySelectorAll('.ci-sidebar-avatar, .ci-profile-trigger-avatar').forEach(function (el) { el.textContent = avatar; });
      var title = document.querySelector('.ci-dash-page-title[data-auth-greeting]');
      if (title) title.textContent = 'Welcome back, ' + (user.firstName || fullName) + '! 👋';
      hydrateProfile(user);
      return user;
    } catch (error) {
      if (error.status === 401) {
        window.location.href = '../login.html?redirect=customer/dashboard.html';
      } else {
        document.querySelectorAll('[data-auth-error]').forEach(function (el) { el.textContent = 'Unable to load your account details.'; });
      }
      return null;
    }
  }

  function hydrateProfile(user) {
    if (!document.body.classList.contains('ci-profile-page') && !document.querySelector('[data-profile-field]')) return;
    var fields = {
      firstName: user.firstName || user.first_name || '',
      lastName: user.lastName || user.last_name || '',
      email: user.email || '',
      phone: user.phone || user.mobile || '',
      dateOfBirth: user.dateOfBirth || '',
      gender: user.gender || '',
      anniversaryDate: user.anniversaryDate || ''
    };
    Object.keys(fields).forEach(function (key) {
      document.querySelectorAll('[data-profile-field="' + key + '"]').forEach(function (el) { el.value = fields[key]; });
    });
    document.querySelectorAll('[data-profile-name]').forEach(function (el) { el.textContent = [fields.firstName, fields.lastName].filter(Boolean).join(' ') || 'Customer'; });
    document.querySelectorAll('[data-profile-email]').forEach(function (el) { el.textContent = fields.email; });
    document.querySelectorAll('[data-profile-verified]').forEach(function (el) { el.textContent = user.emailVerified ? 'Email Verified' : 'Email not verified'; });
  }

  function setEmpty(container, message) {
    if (container) container.innerHTML = '<div class="ci-empty-state" style="padding:24px;text-align:center;color:var(--text-muted);">' + escapeHtml(message) + '</div>';
  }

  function renderNotifications(list) {
    var dropdown = document.querySelector('.ci-dropdown-body[data-dashboard-notifications]');
    if (!dropdown) return;
    if (!list.length) { setEmpty(dropdown, 'No unread notifications.'); return; }
    dropdown.innerHTML = list.map(function (n) {
      return '<div class="ci-notif-item unread"><div class="ci-notif-icon" style="background:var(--navy-pale);color:var(--navy);"><i class="fa-solid fa-bell"></i></div><div><div class="ci-notif-text">' + escapeHtml(n.title || n.message || 'Notification') + '</div><div class="ci-notif-time">' + escapeHtml(relativeText(n.createdAt)) + '</div></div></div>';
    }).join('');
  }

  function renderOrders(list) {
    var box = document.querySelector('[data-dashboard-recent-orders]');
    if (!box) return;
    if (!list.length) { setEmpty(box, 'No orders yet.'); return; }
    box.innerHTML = list.map(function (o) {
      var tracking = o.tracking && (o.tracking.packageStatus || o.tracking.groupStatus || o.tracking.latestEventStatus);
      var status = tracking || o.status || 'processing';
      return '<div class="ci-activity-item"><div class="ci-activity-icon saffron"><i class="fa-solid fa-bag-shopping"></i></div><div class="ci-activity-text"><div class="ci-activity-desc"><strong>' + escapeHtml(o.orderNumber) + '</strong> — ' + escapeHtml(status.replace(/_/g, ' ')) + ' · ' + money(o.totalAmount) + '</div><div class="ci-activity-time">' + escapeHtml(relativeText(o.createdAt)) + '</div></div></div>';
    }).join('');
  }

  function renderActions(list) {
    var box = document.querySelector('[data-dashboard-pending-actions]');
    if (!box) return;
    if (!list.length) { setEmpty(box, 'You have no pending actions.'); return; }
    box.innerHTML = list.map(function (a) {
      return '<div class="ci-activity-item"><div class="ci-activity-icon navy"><i class="fa-solid fa-circle-exclamation"></i></div><div class="ci-activity-text"><div class="ci-activity-desc"><strong>' + escapeHtml(a.title) + '</strong></div><div class="ci-activity-time">' + (a.amount ? escapeHtml(money(a.amount)) : 'Action required') + '</div></div></div>';
    }).join('');
  }

  function renderDashboard(data) {
    var s = data.stats || {};
    var statMap = {
      activeOrders: s.activeOrders || 0,
      wishlistItems: s.wishlistItems || 0,
      rewardPoints: s.rewardPoints || 0
    };
    document.querySelectorAll('[data-stat]').forEach(function (el) {
      var key = el.getAttribute('data-stat');
      if (Object.prototype.hasOwnProperty.call(statMap, key)) el.textContent = Number(statMap[key]).toLocaleString('en-IN');
    });
    document.querySelectorAll('[data-count="orders"]').forEach(function (el) { el.textContent = Number(s.orders || 0).toLocaleString('en-IN'); });
    document.querySelectorAll('[data-count="wishlist"]').forEach(function (el) { el.textContent = Number(s.wishlistItems || 0).toLocaleString('en-IN'); });
    document.querySelectorAll('[data-count="coupons"]').forEach(function (el) { el.textContent = Number((data.coupons || []).length).toLocaleString('en-IN'); });
    document.querySelectorAll('[data-count="notifications"]').forEach(function (el) { el.textContent = Number(s.unreadNotifications || 0).toLocaleString('en-IN'); });
    document.querySelectorAll('[data-dashboard-total-spent]').forEach(function (el) { el.textContent = money(s.totalSpent); });
    renderNotifications(data.notifications || []);
    renderOrders(data.recentOrders || []);
    renderActions(data.pendingActions || []);
  }

  async function hydrateDashboard() {
    var root = document.querySelector('[data-customer-dashboard]');
    if (!root) return;
    try {
      var data = await getJson('../api/user/dashboard.php');
      renderDashboard(data);
    } catch (error) {
      document.querySelectorAll('[data-dashboard-error]').forEach(function (el) { el.textContent = error.message || 'Unable to load dashboard.'; });
    }
  }

  function initLogout() {
    document.querySelectorAll('a[data-customer-logout]').forEach(function (link) {
      link.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
          await fetch('../api/auth/logout.php', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken() } });
        } finally {
          window.location.href = '../login.html';
        }
      });
    });
  }


  function initProfileSave() {
    var form = document.querySelector('[data-profile-form]');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      var button = form.querySelector('[type="submit"]');
      if (button) button.disabled = true;

      var body = {};

      form.querySelectorAll('[data-profile-field]').forEach(function (el) {
        body[el.getAttribute('data-profile-field')] = el.value;
      });

      try {
        var authResponse = await fetch('../api/auth/me.php', {
          method: 'GET',
          credentials: 'same-origin',
          headers: { Accept: 'application/json' }
        });

        var authData = await authResponse.json();

        if (!authResponse.ok || authData.success === false) {
          throw new Error(
            authData.message || 'Please sign in again.'
          );
        }

        var token =
          authData.csrfToken ||
          authData.csrf_token ||
          '';

        if (!token) {
          throw new Error('Security token missing. Refresh and try again.');
        }

        var response = await fetch('../api/user/profile.php', {
          method: 'PUT',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-Token': token
          },
          body: JSON.stringify(body)
        });

        var data = await response.json();

        if (!response.ok || data.success === false) {
          throw new Error(data.message || 'Unable to save profile.');
        }

        hydrateProfile(data.user || {});

        if (typeof showToast === 'function') {
          showToast('Profile updated successfully.', 'success');
        }
      } catch (error) {
        if (typeof showToast === 'function') {
          showToast(error.message || 'Unable to save profile.', 'error');
        } else {
          alert(error.message || 'Unable to save profile.');
        }
      } finally {
        if (button) button.disabled = false;
      }
    });
  }

  function initSidebarSubmenus() {
    document.querySelectorAll('.ci-sidebar-nav-item').forEach(function (item) {
      item.addEventListener('click', function (e) {
        var href = this.getAttribute('href');
        if (href && href !== '#') return;
        var sub = this.nextElementSibling;
        if (sub && sub.classList.contains('ci-sidebar-sub')) { e.preventDefault(); sub.classList.toggle('open'); }
      });
    });
  }
  function initFaqAccordions() {
    document.querySelectorAll('.ci-faq-question').forEach(function (btn) { btn.addEventListener('click', function () { var item = this.closest('.ci-faq-item'); if (item) item.classList.toggle('open'); }); });
  }
  function initSidebarCollapse() {
    var sidebar = document.getElementById('ci-sidebar'), btn = document.getElementById('ci-sidebar-collapse-btn');
    if (!sidebar || !btn) return; btn.addEventListener('click', function () { sidebar.classList.toggle('collapsed'); });
  }
  function initMobileSidebarDrawer() {
    var sidebar = document.getElementById('ci-sidebar'), openBtn = document.getElementById('ci-sidebar-hamburger'), overlay = document.getElementById('ci-sidebar-overlay');
    if (!sidebar || !openBtn) return;
    function closeDrawer() { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); document.body.style.overflow = ''; openBtn.setAttribute('aria-expanded', 'false'); }
    openBtn.addEventListener('click', function () { sidebar.classList.add('open'); if (overlay) overlay.classList.add('show'); document.body.style.overflow = 'hidden'; openBtn.setAttribute('aria-expanded', 'true'); });
    if (overlay) overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
    sidebar.querySelectorAll('.ci-sidebar-nav-item').forEach(function (link) { link.addEventListener('click', function () { if (window.innerWidth <= 1024) closeDrawer(); }); });
    window.addEventListener('resize', function () { if (window.innerWidth > 1024) closeDrawer(); });
  }
  function initTopbarDropdowns() {
    var dropdowns = document.querySelectorAll('.ci-topbar-dropdown'); if (!dropdowns.length) return;
    dropdowns.forEach(function (dd) { var trigger = dd.querySelector('.ci-dropdown-trigger, .ci-profile-trigger'); if (!trigger) return; trigger.addEventListener('click', function (e) { e.stopPropagation(); var open = dd.classList.contains('open'); dropdowns.forEach(function (other) { other.classList.remove('open'); }); if (!open) dd.classList.add('open'); }); });
    document.addEventListener('click', function () { dropdowns.forEach(function (dd) { dd.classList.remove('open'); }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') dropdowns.forEach(function (dd) { dd.classList.remove('open'); }); });
  }
  function initChartPeriodToggle() {
    document.querySelectorAll('.ci-chart-period-btns').forEach(function (group) { var btns = group.querySelectorAll('.ci-period-btn'); btns.forEach(function (btn) { btn.addEventListener('click', function () { btns.forEach(function (b) { b.classList.remove('active'); }); this.classList.add('active'); }); }); });
  }
  function initActionFeedback() {
    document.querySelectorAll('.ci-dash-main button.bc-btn').forEach(function (btn) {
      if (btn.closest('.ci-dash-topbar') || btn.closest('.ci-dropdown-panel') || btn.closest('[data-profile-form]')) return;
      btn.addEventListener('click', function (e) { if (btn.getAttribute('type') === 'submit') return; e.preventDefault(); if (typeof showToast === 'function') showToast((btn.textContent || 'Action').replace(/\s+/g, ' ').trim() + ' ✓', 'success'); });
    });
  }

  ready(function () {
    initSidebarSubmenus(); initFaqAccordions(); initSidebarCollapse(); initMobileSidebarDrawer(); initTopbarDropdowns(); initChartPeriodToggle(); initActionFeedback(); initLogout();
    hydrateIdentity(); hydrateDashboard(); initProfileSave();
  });
})();
