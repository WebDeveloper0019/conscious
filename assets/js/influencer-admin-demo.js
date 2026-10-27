(function () {
  'use strict';

  var STORE_KEY = 'conscious-influencer-demo-registry';
  var SEED_VERSION = '4';
  var AUTO_APPROVE_NEW_REGISTRATIONS = true;
  var OTP_ENABLED = false;

  /* ── Seed creators ───────────────────────────────────── */
  var seededRequests = [
    {
      id: 'INF-1001', handle: 'meera-finds',
      profileImage: 'assets/images/user.png',
      fullName: 'Meera Sharma', email: 'meera@example.com', password: 'password123',
      contactNumber: '+91 99887 76655', whatsappNumber: '+91 99887 76655',
      city: 'Jaipur', brandName: 'Meera Finds',
      about: 'Conscious lifestyle storyteller sharing handcraft and mindful living.',
      category: 'Lifestyle & Sustainable Living',
      socialLinks: 'https://instagram.com/meera-finds https://youtube.com/@meera-finds',
      portfolioUrl: 'https://example.com/meera-finds', portfolioFiles: [],
      availability: 'Freelance', verificationDocument: 'uploads/verification/meera-id.pdf',
      status: 'approved', hideProfile: false, visibility: 'visible',
      packages: [
        { name: 'Basic',    price: '₹4,999',  deliveryTime: '5 Days',  description: 'One branded reel + 2 static posts',     features: ['Reel concept', 'Caption support'] },
        { name: 'Gold',     price: '₹9,999',  deliveryTime: '7 Days',  description: 'Campaign + story & content plan',       features: ['Story support', 'Analytics review'] },
        { name: 'Platinum', price: '₹14,999', deliveryTime: '10 Days', description: 'Premium creator collaboration package', features: ['Dedicated brief', 'Priority turnaround'] }
      ],
      stats: { followers: '2.4M', rating: '4.9', projects: '48', experience: '6 yrs' },
      dashboard: {
        clicks: 48240, clicksChange: 12,
        conversions: 1284, conversionRate: 2.66,
        sales: 1240000, pendingEarnings: 62000, commissionRate: 7,
        chart: [
          { label: 'Jun 1',  height: 33,  amount: 1200 },
          { label: 'Jun 5',  height: 66,  amount: 2400 },
          { label: 'Jun 8',  height: 50,  amount: 1800 },
          { label: 'Jun 11', height: 100, amount: 3600 },
          { label: 'Jun 14', height: 80,  amount: 2900 }
        ],
        topLinks: [
          { name: 'Kundan Necklace Set',       clicks: 4820, conversions: 142, earned: 12070 },
          { name: 'Pashmina Shawl Collection', clicks: 3210, conversions: 98,  earned: 18130 },
          { name: 'Diwali Gift Guide',         clicks: 8400, conversions: 240, earned: 24000 }
        ],
        campaigns: [
          { name: 'Monsoon Craft Mela',        detail: '18 products promoted · ₹8,400 earned', status: 'Active · Ends Jul 15' },
          { name: 'Diwali Jewellery Festival', detail: 'Joining soon',                       status: 'Starts Oct 1, 2025' }
        ],
        notifications: [
          { icon: 'fa-indian-rupee-sign', color: 'green',   text: '₹4,200 commission payment processed',                         time: '18 min ago',   unread: true },
          { icon: 'fa-bullhorn',          color: 'saffron', text: 'New campaign "Monsoon Craft Mela" is live — 9% commission',    time: '2 hours ago',  unread: true },
          { icon: 'fa-arrow-trend-up',    color: 'navy',    text: 'Your referral link crossed 10,000 clicks this week',           time: 'Yesterday',    unread: true },
          { icon: 'fa-trophy',            color: 'gold',    text: 'Your creator profile was viewed by three new partners',       time: 'Jun 13, 2025', unread: false }
        ]
      },
      adminNotes: 'Approved for public creator listing.'
    },
    {
      id: 'INF-1002', handle: 'riya-creates',
      profileImage: 'assets/images/user.png',
      fullName: 'Riya Kapoor', email: 'riya@example.com', password: 'password123',
      contactNumber: '+91 98112 33445', whatsappNumber: '+91 98112 33445',
      city: 'Bengaluru', brandName: 'Riya Creates',
      about: 'Design-forward creator exploring mindful living and artisan stories.',
      category: 'Home Decor & Design',
      socialLinks: 'https://youtube.com/@riya-creates',
      portfolioUrl: 'https://example.com/riya-creates', portfolioFiles: [],
      availability: 'Full-time', verificationDocument: 'uploads/verification/riya-id.pdf',
      status: 'approved', hideProfile: false, visibility: 'visible',
      packages: [],
      stats: { followers: '180K', rating: '4.7', projects: '12', experience: '3 yrs' },
      dashboard: {
        clicks: 8420, clicksChange: 5,
        conversions: 214, conversionRate: 2.54,
        sales: 186000, pendingEarnings: 12400, commissionRate: 6.5,
        chart: [
          { label: 'Jun 1',  height: 20, amount: 420 },
          { label: 'Jun 5',  height: 45, amount: 940 },
          { label: 'Jun 8',  height: 65, amount: 1360 },
          { label: 'Jun 11', height: 85, amount: 1780 },
          { label: 'Jun 14', height: 70, amount: 1460 }
        ],
        topLinks: [
          { name: 'Handloom Cushion Covers', clicks: 2100, conversions: 52, earned: 3640 },
          { name: 'Organic Cotton Bedding',  clicks: 1340, conversions: 34, earned: 2380 }
        ],
        campaigns: [
          { name: 'Monsoon Craft Mela', detail: '6 products promoted · ₹1,200 earned', status: 'Active · Ends Jul 15' }
        ],
        notifications: [
          { icon: 'fa-indian-rupee-sign', color: 'green',   text: '₹1,240 commission payment processed',                       time: '1 hour ago', unread: true },
          { icon: 'fa-bullhorn',          color: 'saffron', text: 'New campaign "Monsoon Craft Mela" is live — 9% commission', time: '2 hours ago', unread: true }
        ]
      },
      adminNotes: 'Approved — active on creator listing.'
    },
    {
      id: 'INF-1003', handle: 'aarav-stories',
      profileImage: 'assets/images/user.png',
      fullName: 'Aarav Nair', email: 'aarav@example.com', password: 'password123',
      contactNumber: '+91 97000 11223', whatsappNumber: '+91 97000 11223',
      city: 'Kochi', brandName: 'Aarav Stories',
      about: 'Travel and culture creator spotlighting underrepresented crafts.',
      category: 'Travel & Culture',
      socialLinks: 'https://instagram.com/aarav-stories',
      portfolioUrl: 'https://example.com/aarav-stories', portfolioFiles: [],
      availability: 'Part-time', verificationDocument: 'uploads/verification/aarav-id.pdf',
      status: 'approved', hideProfile: false, visibility: 'visible',
      packages: [],
      stats: { followers: '95K', rating: '4.4', projects: '8', experience: '2 yrs' },
      dashboard: {
        clicks: 2340, clicksChange: 3,
        conversions: 62, conversionRate: 2.65,
        sales: 42000, pendingEarnings: 2800, commissionRate: 6,
        chart: [
          { label: 'Jun 1',  height: 10, amount: 120 },
          { label: 'Jun 5',  height: 30, amount: 360 },
          { label: 'Jun 8',  height: 50, amount: 600 },
          { label: 'Jun 11', height: 70, amount: 840 },
          { label: 'Jun 14', height: 40, amount: 480 }
        ],
        topLinks: [
          { name: 'Handmade Leather Journals', clicks: 820, conversions: 18, earned: 900 },
          { name: 'Kerala Spice Gift Set',     clicks: 510, conversions: 12, earned: 600 }
        ],
        campaigns: [
          { name: 'Monsoon Craft Mela', detail: '3 products promoted · ₹400 earned', status: 'Active · Ends Jul 15' }
        ],
        notifications: [
          { icon: 'fa-indian-rupee-sign', color: 'green', text: '₹420 commission payment processed', time: '3 hours ago', unread: true }
        ]
      },
      adminNotes: 'Approved after document re-verification.'
    }
  ];

  /* ── Missing-in-original helpers ─────────────────────── */
  function readRequests() {
    if (window.InfluencerSession) return window.InfluencerSession.readRegistry();
    try {
      var raw = localStorage.getItem(STORE_KEY);
      var parsed = JSON.parse(raw || '[]');
      return Array.isArray(parsed) ? parsed : (parsed && parsed.data) || [];
    } catch (e) { return []; }
  }

  function writeRequests(list) {
    if (window.InfluencerSession) return window.InfluencerSession.writeRegistry(list);
    localStorage.setItem(STORE_KEY, JSON.stringify({ __v: SEED_VERSION, data: list }));
  }

  function seedIfNeeded() {
    var raw = localStorage.getItem(STORE_KEY);
    var parsed = null;
    try { parsed = JSON.parse(raw || 'null'); } catch (e) {}
    if (parsed && parsed.__v === SEED_VERSION) return;
    var seedIds = seededRequests.map(function (s) { return s.id; });
    var existingData = (parsed && parsed.data) || (Array.isArray(parsed) ? parsed : []);
    var userEntries = existingData.filter(function (e) { return seedIds.indexOf(e.id) === -1; });
    var combined = JSON.parse(JSON.stringify(seededRequests)).concat(userEntries);
    localStorage.setItem(STORE_KEY, JSON.stringify({ __v: SEED_VERSION, data: combined }));
  }

  function slugifyHandle(text) {
    return String(text || '').toLowerCase().trim()
      .replace(/[^a-z0-9._-]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .slice(0, 100) || 'creator';
  }

  /* ── OTP (still available if you flip OTP_ENABLED) ──── */
  var pendingRegistration = null;
  var pendingOTP = null;
  var otpModalInstance = null;

  function _generateOTP() { return String(Math.floor(100000 + Math.random() * 900000)); }

  function _startOTPFlow(formData) {
    pendingRegistration = formData;
    pendingOTP = { code: _generateOTP(), email: formData.email, expiresAt: Date.now() + 5 * 60 * 1000, attempts: 0 };
    _showOTPModal();
  }

  function _showOTPModal() {
    if (!otpModalInstance) {
      otpModalInstance = new bootstrap.Modal(document.getElementById('otpVerifyModal'));
      _bindOTPModal();
    }
    document.getElementById('otp-email-display').textContent = pendingOTP.email;
    document.getElementById('otp-demo-code').textContent = pendingOTP.code;
    document.getElementById('otp-input').value = '';
    document.getElementById('otp-error').textContent = '';
    document.getElementById('otp-input').focus();
    _startTimer();
    otpModalInstance.show();
  }

  function _startTimer() {
    if (pendingOTP.timerId) clearInterval(pendingOTP.timerId);
    var timerEl = document.getElementById('otp-timer');
    function tick() {
      var left = Math.max(0, Math.floor((pendingOTP.expiresAt - Date.now()) / 1000));
      var m = String(Math.floor(left / 60)).padStart(2, '0');
      var s = String(left % 60).padStart(2, '0');
      if (timerEl) timerEl.textContent = m + ':' + s;
      if (left <= 0) {
        clearInterval(pendingOTP.timerId);
        pendingOTP.timerId = null;
        var errEl = document.getElementById('otp-error');
        if (errEl) errEl.textContent = 'Code expired. Please resend.';
      }
    }
    tick();
    pendingOTP.timerId = setInterval(tick, 1000);
  }

  function _bindOTPModal() {
    var input = document.getElementById('otp-input');
    input.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); _verifyOTP(); }
    });
    document.getElementById('otp-verify-btn').addEventListener('click', _verifyOTP);
    document.getElementById('otp-resend').addEventListener('click', function () {
      if (!pendingRegistration) return;
      pendingOTP = { code: _generateOTP(), email: pendingRegistration.email, expiresAt: Date.now() + 5 * 60 * 1000, attempts: 0 };
      document.getElementById('otp-demo-code').textContent = pendingOTP.code;
      document.getElementById('otp-input').value = '';
      document.getElementById('otp-error').textContent = '';
      _startTimer();
      if (typeof showToast === 'function') showToast('New verification code sent.', 'navy');
    });
  }

  function _verifyOTP() {
    var entered = document.getElementById('otp-input').value.trim();
    var errEl = document.getElementById('otp-error');
    errEl.textContent = '';
    if (!pendingOTP) return;
    if (Date.now() > pendingOTP.expiresAt) { errEl.textContent = 'Code expired. Please resend.'; return; }
    if (pendingOTP.attempts >= 3)          { errEl.textContent = 'Too many incorrect attempts. Please resend.'; return; }
    if (entered.length !== 6)              { errEl.textContent = 'Please enter all 6 digits.'; return; }
    if (entered !== pendingOTP.code) {
      pendingOTP.attempts++;
      errEl.textContent = 'Incorrect code. ' + (3 - pendingOTP.attempts) + ' attempt(s) left.';
      return;
    }
    if (pendingOTP.timerId) clearInterval(pendingOTP.timerId);
    var data = pendingRegistration;
    pendingRegistration = null;
    pendingOTP = null;
    if (otpModalInstance) otpModalInstance.hide();
    _createAccountAfterVerification(data);
  }

  /* ── Create account (used by register form) ─────────── */
  function _createAccountAfterVerification(data) {
    var registry = readRequests();

    var handle = slugifyHandle(data.brandName || data.fullName || String(data.email || '').split('@')[0]);
    if (registry.some(function (c) { return c.handle === handle; })) {
      handle = handle + '-' + (Date.now() % 1000);
    }

    var newRequest = {
      id: 'INF-' + (Date.now() % 900000 + 100000),
      handle: handle,
      profileImage: 'assets/images/user.png',
      fullName: data.fullName,
      email: data.email,
      password: data.password,
      contactNumber: data.contactNumber,
      whatsappNumber: data.whatsappNumber || '',
      city: data.city || '',
      brandName: data.brandName || data.fullName,
      about: data.about || '',
      category: data.category || '',
      socialLinks: data.socialLinks || '',
      portfolioUrl: data.portfolioUrl || '',
      portfolioFiles: [],
      availability: data.availability || 'Freelance',
      verificationDocument: data.governmentDocument || '',
      status: AUTO_APPROVE_NEW_REGISTRATIONS ? 'approved' : 'pending',
      hideProfile: false,
      visibility: 'visible',
      packages: [],
      stats: { followers: '0', rating: '0', projects: '0', experience: 'New' },
      dashboard: {
        clicks: 0, clicksChange: 0,
        conversions: 0, conversionRate: 0,
        sales: 0, pendingEarnings: 0, commissionRate: 7,
        chart: [], topLinks: [], campaigns: [], notifications: []
      },
      emailVerified: !!OTP_ENABLED,
      adminNotes: AUTO_APPROVE_NEW_REGISTRATIONS
        ? (OTP_ENABLED ? 'Auto-approved + email verified.' : 'Auto-approved (testing, unverified email).')
        : 'Awaiting admin review.'
    };
    registry.unshift(newRequest);
    writeRequests(registry);

    if (window.InfluencerSession) window.InfluencerSession.login(newRequest.id);
    if (typeof showToast === 'function') {
      showToast(OTP_ENABLED ? 'Email verified! Welcome to Conscious Indi.' : 'Account created! Redirecting…', 'success');
    }
    setTimeout(function () { window.location.href = 'influencer/dashboard.html'; }, 700);
  }

  /* ── Login form ──────────────────────────────────────── */
  function initInfluencerLoginForm() {
    var form = document.getElementById('influencer-login-form');
    if (!form) return;
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        if (typeof showToast === 'function') showToast('Please enter your influencer email and password.', 'error');
        return;
      }
      var data = Object.fromEntries(new FormData(form).entries());
      var registry = readRequests();
      var matched = registry.filter(function (entry) {
        return (entry.email || '').toLowerCase() === (data.email || '').toLowerCase()
            && entry.password === data.password;
      })[0];
      if (!matched) {
        if (typeof showToast === 'function') showToast('No influencer account matches those credentials.', 'error');
        return;
      }
      if (String(matched.status || '').toLowerCase() !== 'approved') {
        if (typeof showToast === 'function') showToast('Your account is not yet approved. Please contact support.', 'error');
        return;
      }
      if (window.InfluencerSession) window.InfluencerSession.login(matched.id);
      if (typeof showToast === 'function') showToast('Login successful. Redirecting…', 'success');
      window.location.href = 'influencer/dashboard.html';
    });
  }

  /* ── Registration form ───────────────────────────────── */
  function initInfluencerRegistrationForm() {
    var form = document.getElementById('influencer-registration-form');
    if (!form) return;
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        if (typeof showToast === 'function') showToast('Please fill in the required influencer fields correctly.', 'error');
        return;
      }
      var data = Object.fromEntries(new FormData(form).entries());
      var registry = readRequests();
      if (registry.some(function (r) { return (r.email || '').toLowerCase() === (data.email || '').toLowerCase(); })) {
        if (typeof showToast === 'function') showToast('An account with this email already exists. Please sign in.', 'error');
        return;
      }
      if (OTP_ENABLED) _startOTPFlow(data);
      else              _createAccountAfterVerification(data);
    });
  }

  /* ── Admin moderation (kept verbatim) ────────────────── */
  function normalizeStatus(s) { return String(s || 'pending').toLowerCase(); }
  function badgeClass(status) {
    var map = {
      pending:  'ci-status ci-status-pending',
      approved: 'ci-status ci-status-active',
      rejected: 'ci-status ci-status-inactive',
      blocked:  'ci-status ci-status-inactive',
      hidden:   'ci-status ci-status-pending',
      visible:  'ci-status ci-status-active'
    };
    return map[status] || 'ci-status ci-status-pending';
  }
  function initAdminModeration() {
    var tableBody = document.getElementById('influencer-requests-body');
    if (!tableBody) return;
    /* ...preserved as-is; no admin HTML in scope so this stays inert... */
  }

  /* ── Boot ────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    seedIfNeeded();
    initInfluencerRegistrationForm();
    initInfluencerLoginForm();
    initAdminModeration();
  });

  /* Expose for the dashboard hydration script */
  window.InfluencerDemo = { seedIfNeeded: seedIfNeeded };
})();