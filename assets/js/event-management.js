'use strict';
document.addEventListener('DOMContentLoaded', function () {
  var toast = document.createElement('div');
  toast.className = 'ev-toast'; toast.setAttribute('role', 'status'); toast.setAttribute('aria-live', 'polite'); document.body.appendChild(toast);
  var pendingAction = null, pendingRow = null;
  function say(message) { toast.textContent = message; toast.classList.add('show'); clearTimeout(say.timer); say.timer = setTimeout(function () { toast.classList.remove('show'); }, 3600); }
  function close(modal) { if (!modal) return; modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true'); }
  function status(row, name, kind) { var badge = row && row.querySelector('.ev-status'); if (!badge) return; badge.className = 'ev-status ' + kind; badge.textContent = name; }
  function performAction(action, row) { if (action === 'approve') { status(row, 'Approved', 'approved'); say('Request approved. Future confirmation messaging is represented in this UI.'); } if (action === 'reject') { status(row, 'Rejected', 'rejected'); say('Request rejected and the admin note is ready for a future workflow.'); } if (action === 'publish') { status(row, 'Live', 'live'); say('Event is now Live in this frontend preview.'); } if (action === 'unpublish') { status(row, 'Draft', 'draft'); say('Event returned to Draft in this frontend preview.'); } if (action === 'cancel') { status(row, 'Cancelled', 'cancelled'); say('Event marked Cancelled in this frontend preview.'); } if (action === 'delete') { if (row) row.remove(); say('Event removed from this frontend list.'); } }
  function labelCells() { document.querySelectorAll('.ev-table').forEach(function (table) { table.querySelectorAll('tbody tr').forEach(function (row) { row.querySelectorAll('td').forEach(function (cell, index) { var heading = table.querySelectorAll('th')[index]; if (heading) cell.dataset.label = heading.textContent; }); }); }); }
  labelCells();
  document.querySelectorAll('[data-toggle-price]').forEach(function (control) { control.addEventListener('change', function () { var price = document.getElementById(control.dataset.togglePrice); if (price) price.classList.toggle('show', control.checked); }); });
  var attendeeEnabled = document.getElementById('attendee-registration-enabled'), attendeeType = document.getElementById('attendee-registration-type'), attendeeTypeWrap = document.getElementById('attendee-registration-type-wrap'), attendeeFeeWrap = document.getElementById('attendee-registration-fee-wrap'), attendeeFee = document.getElementById('attendee-registration-fee');
  function syncAttendeeRegistration() { if (!attendeeEnabled) return; var enabled = attendeeEnabled.value === 'yes', paid = attendeeType && attendeeType.value === 'paid'; if (attendeeTypeWrap) attendeeTypeWrap.hidden = !enabled; if (attendeeFeeWrap) attendeeFeeWrap.classList.toggle('show', enabled && paid); if (attendeeFee) attendeeFee.required = enabled && paid; }
  if (attendeeEnabled) { attendeeEnabled.addEventListener('change', syncAttendeeRegistration); attendeeType.addEventListener('change', syncAttendeeRegistration); syncAttendeeRegistration(); }
  document.querySelectorAll('[data-open-modal]').forEach(function (button) { button.addEventListener('click', function () { var modal = document.getElementById(button.dataset.openModal); if (button.dataset.confirmAction) { pendingAction = button.dataset.confirmAction; pendingRow = button.closest('tr'); } if (modal) { var confirm = modal.querySelector('[data-confirm-action]'), title = modal.querySelector('[data-confirm-title]'), copy = modal.querySelector('[data-confirm-copy]'); if (confirm && pendingAction) { confirm.dataset.action = pendingAction; } if (title && pendingAction) title.textContent = pendingAction === 'approve' ? 'Approve this request?' : pendingAction === 'reject' ? 'Reject this request?' : pendingAction === 'delete' ? 'Delete this event?' : pendingAction === 'cancel' ? 'Cancel this event?' : 'Change event status?'; if (copy && pendingAction) copy.textContent = pendingAction === 'approve' ? 'This static UI will mark the request approved.' : pendingAction === 'reject' ? 'This static UI will mark the request rejected.' : pendingAction === 'delete' ? 'This will remove the event from this frontend management view.' : pendingAction === 'cancel' ? 'This static UI will mark the event cancelled.' : 'Confirm the publication status change.'; modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false'); var focusable = modal.querySelector('input, textarea, button, select'); if (focusable) focusable.focus(); } }); });
  document.querySelectorAll('[data-close-modal]').forEach(function (button) { button.addEventListener('click', function () { close(button.closest('.ev-modal')); }); });
  document.querySelectorAll('.ev-modal').forEach(function (modal) { modal.addEventListener('click', function (event) { if (event.target === modal) close(modal); }); });
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape') document.querySelectorAll('.ev-modal.open').forEach(close); });
  document.querySelectorAll('[data-static-submit]').forEach(function (form) { form.addEventListener('submit', function (event) { event.preventDefault(); if (!form.checkValidity()) { form.classList.add('was-validated'); return; } var success = form.closest('.ev-modal') && form.closest('.ev-modal').querySelector('.ev-success'); if (success) { form.style.display = 'none'; success.classList.add('show'); } }); });
  document.querySelectorAll('.ev-tab').forEach(function (tab) { tab.addEventListener('click', function () { document.querySelectorAll('.ev-tab').forEach(function (item) { item.classList.remove('active'); item.setAttribute('aria-selected', 'false'); }); tab.classList.add('active'); tab.setAttribute('aria-selected', 'true'); say('Showing ' + tab.textContent.trim() + ' (static demo results).'); }); });
  document.querySelectorAll('[data-action]').forEach(function (button) { button.addEventListener('click', function () { performAction(button.dataset.action, pendingRow || button.closest('tr')); pendingAction = null; pendingRow = null; document.querySelectorAll('.ev-modal.open').forEach(close); }); });
  document.querySelectorAll('.ev-action-link:not([data-action])').forEach(function (button) {
    if (button.tagName === 'A') return;
    var text = button.textContent.trim().toLowerCase();
    var action = text.indexOf('approve') === 0 ? 'approve' : text.indexOf('reject') === 0 ? 'reject' : '';
    if (!action) return;
    button.dataset.action = action;
    button.addEventListener('click', function () { status(button.closest('tr'), action === 'approve' ? 'Approved' : 'Rejected', action === 'approve' ? 'approved' : 'rejected'); say(action === 'approve' ? 'Request approved in this frontend preview.' : 'Request rejected in this frontend preview.'); });
  });

  /* Static action hooks for the minified admin templates. These keep the UI
     ready for future backend wiring without changing the page markup. */
  function actionFromLabel(control) {
    var label = (control.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    if (label === 'approve' || label === 'approve request') return 'approve';
    if (label === 'reject' || label === 'reject request') return 'reject';
    if (label === 'publish') return 'publish';
    if (label === 'unpublish') return 'unpublish';
    if (label === 'delete' || label === 'yes, delete event') return 'delete';
    if (label === 'cancel event' || label === 'yes, cancel event') return 'cancel';
    return '';
  }
  function openStaticConfirmation(control, action) {
    var modal = document.getElementById(control.dataset.openModal || (action === 'delete' ? 'delete-modal' : action === 'publish' || action === 'unpublish' ? 'publish-modal' : ''));
    pendingAction = action; pendingRow = control.closest('tr');
    if (!modal) {
      if (window.confirm('Confirm ' + action + ' in this frontend preview?')) performAction(action, pendingRow);
      pendingAction = null; pendingRow = null;
      return;
    }
    var title = modal.querySelector('[data-confirm-title], h2'), copy = modal.querySelector('[data-confirm-copy], p'), buttons = modal.querySelectorAll('.ev-form-actions button'), confirm = buttons[buttons.length - 1];
    if (title) title.textContent = action === 'approve' ? 'Approve this request?' : action === 'reject' ? 'Reject this request?' : action === 'cancel' ? 'Cancel this event?' : action === 'delete' ? 'Delete this event?' : 'Change event status';
    if (copy) copy.textContent = action === 'cancel' ? 'This static UI will mark the event cancelled.' : action === 'delete' ? 'This will remove the event from this frontend management view.' : 'Confirm this frontend-only status change.';
    if (confirm) { confirm.dataset.staticConfirm = 'true'; confirm.addEventListener('click', function () { performAction(action, pendingRow); pendingAction = null; pendingRow = null; delete confirm.dataset.staticConfirm; close(modal); }, { once: true }); }
    modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false');
  }
  document.querySelectorAll('.ev-tab, [role="tab"]').forEach(function (tab) {
    tab.dataset.filter = tab.dataset.filter || (tab.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
    tab.setAttribute('role', 'tab');
    if (!tab.hasAttribute('aria-selected')) tab.setAttribute('aria-selected', tab.classList.contains('active') ? 'true' : 'false');
  });
  document.addEventListener('click', function (event) {
    var control = event.target.closest('button, a');
    if (!control || control.dataset.action || control.dataset.staticConfirm) return;
    var action = actionFromLabel(control);
    if (!action) return;
    event.preventDefault(); event.stopImmediatePropagation();
    openStaticConfirmation(control, action);
  }, true);

  /* Keep all event-management pages inside the complete Admin navigation.
     The event pages were intentionally lightweight static templates; this
     renders the same navigation vocabulary as the main dashboard so opening
     the event sub-menu never hides the rest of the admin workspace. */
  var sidebar = document.querySelector('.ci-sidebar-nav');
  if (sidebar && !sidebar.textContent.includes('Revenue Analytics')) {
    var currentPage = (window.location.pathname.split('/').pop() || 'event-management.html');
    var eventLinks = [
      ['event-management.html', 'Overview'],
      ['add-event.html', 'Add Event'],
      ['all-events.html', 'All Events'],
      ['booking-requests.html', 'Booking Requests']
    ].map(function (item) { return '<a href="' + item[0] + '"' + (currentPage === item[0] ? ' class="active"' : '') + '>' + item[1] + '</a>'; }).join('');
    sidebar.innerHTML = [
      '<div class="ci-sidebar-section-label">Overview</div>',
      '<a href="dashboard.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span></a>',
      '<a href="revenue.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-chart-line"></i><span>Revenue Analytics</span></a>',
      '<div class="ci-sidebar-section-label">Users</div>',
      '<a href="customers.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-users"></i><span>Customers</span></a>',
      '<a href="vendors.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-store"></i><span>Vendors</span></a>',
      '<a href="vendor-verification.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-user-check"></i><span>Vendor Verification</span><span class="ci-sidebar-nav-badge">8</span></a>',
      '<a href="influencers.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-star"></i><span>Influencers</span></a>',
      '<div class="ci-sidebar-section-label">Catalog</div>',
      '<a href="product-approval.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-box-open"></i><span>Product Approval</span><span class="ci-sidebar-nav-badge">14</span></a>',
      '<a href="categories.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-layer-group"></i><span>Categories</span></a>',
      '<a href="sub-categories.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-sitemap"></i><span>Sub Categories</span></a>',
      '<div class="ci-sidebar-section-label">Orders</div>',
      '<a href="orders.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-bag-shopping"></i><span>All Orders</span></a>',
      '<a href="returns.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-rotate-left"></i><span>Returns</span></a>',
      '<a href="refunds.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-indian-rupee-sign"></i><span>Refunds</span></a>',
      '<div class="ci-sidebar-section-label">Finance</div>',
      '<a href="commissions.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-percent"></i><span>Commissions</span></a>',
      '<a href="payouts.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-money-bill-transfer"></i><span>Payout Management</span></a>',
      '<div class="ci-sidebar-section-label">Marketing</div>',
      '<a href="event-management.html" class="ci-sidebar-nav-item active"><i class="fa-regular fa-calendar-days"></i><span>Event Management</span></a>',
      '<div class="ci-sidebar-sub open">' + eventLinks + '</div>',
      '<a href="coupons.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-ticket"></i><span>Coupons</span></a>',
      '<a href="banners.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-image"></i><span>Banners</span></a>',
      '<div class="ci-sidebar-section-label">Content</div>',
      '<a href="cms-pages.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-file-lines"></i><span>CMS Pages</span></a>',
      '<a href="blog-management.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-newspaper"></i><span>Blog Management</span></a>',
      '<a href="reviews.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-star"></i><span>Reviews Moderation</span></a>',
      '<div class="ci-sidebar-section-label">Admin</div>',
      '<a href="support.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-headset"></i><span>Support Center</span></a>',
      '<a href="reports.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-file-chart-column"></i><span>Reports</span></a>',
      '<a href="settings.html" class="ci-sidebar-nav-item"><i class="fa-solid fa-gear"></i><span>System Settings</span></a>'
    ].join('');
  }

  document.querySelectorAll('.ev-upload').forEach(function (upload) {
    var fileInput = upload.querySelector('input[type="file"]');
    if (!fileInput) return;
    upload.setAttribute('role', 'button'); upload.setAttribute('tabindex', '0');
    upload.addEventListener('click', function () { fileInput.click(); });
    upload.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); fileInput.click(); } });
    fileInput.addEventListener('click', function (event) { event.stopPropagation(); });
    fileInput.addEventListener('change', function () {
      var fileName = upload.querySelector('.ev-upload-file');
      if (!fileName) { fileName = document.createElement('span'); fileName.className = 'ev-upload-file'; upload.appendChild(fileName); }
      fileName.textContent = fileInput.files && fileInput.files[0] ? fileInput.files[0].name + ' selected' : '';
    });
  });

  var events = {
    bazaar: { title: 'Conscious Craft Bazaar', date: '12–13 August 2026', time: '11:00 AM – 8:00 PM', venue: 'Jayamahal Palace', address: 'Jayamahal Palace, Bengaluru, Karnataka', about: 'A considered market of makers, textiles, food and everyday sustainable discoveries. Meet independent labels and community craftspeople over two mindful days.', instructions: 'Please arrive 90 minutes before opening for vendor check-in. Bring your display materials and an extension lead if required.', image: 'bazaar', stall: '₹1,999', workshop: '₹1,599', map: 'https://maps.google.com/?q=Jayamahal+Palace+Bengaluru' },
    festival: { title: 'Slow Living Festival', date: '24 August 2026', time: '10:00 AM – 7:00 PM', venue: 'Jio World Garden', address: 'Jio World Garden, Bandra Kurla Complex, Mumbai, Maharashtra', about: 'A slower weekend of conscious food, craft, wellbeing and learning in the city, with makers and facilitators from across India.', instructions: 'Set-up access opens at 8:00 AM. Workshop hosts should share final equipment needs with the event team before the event.', image: 'festival', stall: '₹2,499', workshop: '₹1,599', map: 'https://maps.google.com/?q=Jio+World+Garden+Mumbai' },
    earth: { title: 'Design for Earth', date: '06 September 2026', time: '11:00 AM – 6:00 PM', venue: 'India Habitat Centre', address: 'India Habitat Centre, Lodhi Road, New Delhi', about: 'A design showcase for circular ideas, material stories and emerging makers shaping a lighter future.', instructions: 'Stalls are curated for low-waste display. Please bring reusable display fixtures and packaging where possible.', image: 'earth', stall: '₹1,999', workshop: null, map: 'https://maps.google.com/?q=India+Habitat+Centre+New+Delhi' }
  };
  var key = new URLSearchParams(window.location.search).get('event'), data = events[key];
  var attendeeRegistrations = { bazaar: { enabled: true, type: 'paid', fee: '₹299' }, festival: { enabled: true, type: 'free', fee: 'FREE' }, earth: { enabled: false, type: 'free', fee: 'FREE' } };
  if (!data || !document.getElementById('event-title')) return;
  document.title = data.title + ' — Conscious Indi Events';
  document.getElementById('event-title').textContent = data.title;
  document.querySelectorAll('[data-event-date]').forEach(function (item) { item.textContent = data.date; });
  document.querySelectorAll('[data-event-time]').forEach(function (item) { item.textContent = data.time; });
  document.querySelectorAll('[data-event-venue]').forEach(function (item) { item.textContent = data.venue; });
  document.querySelectorAll('[data-event-address]').forEach(function (item) { item.textContent = data.address; });
  document.querySelectorAll('[data-event-about], [data-event-summary]').forEach(function (item) { item.textContent = data.about; });
  document.querySelectorAll('[data-event-instructions]').forEach(function (item) { item.textContent = data.instructions; });
  document.querySelectorAll('[data-event-map]').forEach(function (item) { item.href = data.map; item.setAttribute('aria-label', 'Open ' + data.venue + ' in Google Maps'); });
  document.querySelectorAll('.ev-detail-art').forEach(function (item) { item.classList.remove('bazaar', 'festival', 'earth', 'summit'); item.classList.add(data.image); });
  document.querySelectorAll('[data-event-stall]').forEach(function (item) { item.textContent = 'Book a Stall — ' + data.stall; });
  document.querySelectorAll('[data-event-stall-price]').forEach(function (item) { item.textContent = data.stall; });
  document.querySelectorAll('[data-event-workshop]').forEach(function (item) { item.textContent = 'Organize a Workshop — ' + data.workshop; });
  document.querySelectorAll('[data-event-workshop-price]').forEach(function (item) { item.textContent = data.workshop || ''; });
  if (!data.workshop) document.querySelectorAll('[data-workshop-choice]').forEach(function (item) { item.hidden = true; });
  var registration = attendeeRegistrations[key] || { enabled: true, type: 'free', fee: 'FREE' };
  document.querySelectorAll('[data-attendee-registration]').forEach(function (item) { item.hidden = !registration.enabled; });
  document.querySelectorAll('[data-attendee-price]').forEach(function (item) { item.textContent = registration.type === 'paid' ? registration.fee : 'FREE'; });
  document.querySelectorAll('[data-attendee-submit]').forEach(function (item) { item.textContent = registration.type === 'paid' ? 'Continue to payment — ' + registration.fee : 'Register Now — Free'; });
  var attendeeButton = document.querySelector('[data-attendee-registration] .bc-btn');
  if (attendeeButton) { attendeeButton.innerHTML = '<i class="fa-solid fa-ticket"></i> ' + (registration.type === 'paid' ? 'Continue to payment — ' + registration.fee : 'Register Now — Free'); attendeeButton.addEventListener('click', function (event) { event.preventDefault(); say(registration.type === 'paid' ? 'Registration details captured. Payment gateway redirect is ready for backend integration.' : 'Registration details captured in this frontend preview.'); }); }
});
