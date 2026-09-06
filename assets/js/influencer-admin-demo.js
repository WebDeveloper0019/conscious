(function () {
  'use strict';

  const STORE_KEY = 'conscious-influencer-demo-registry';

  const seededRequests = [
    {
      id: 'INF-1001',
      profileImage: 'assets/images/user.png',
      fullName: 'Meera Sharma',
      email: 'meera@example.com',
      password: 'password123',
      contactNumber: '+91 99887 76655',
      whatsappNumber: '+91 99887 76655',
      city: 'Jaipur',
      brandName: 'Meera Finds',
      about: 'Conscious lifestyle storyteller sharing handcraft and mindful living.',
      category: 'Lifestyle & Sustainable Living',
      socialLinks: 'https://instagram.com/meera-finds',
      portfolioUrl: 'https://example.com/meera-finds',
      portfolioFiles: [],
      availability: 'Freelance',
      verificationDocument: 'uploads/verification/meera-id.pdf',
      status: 'approved',
      hideProfile: false,
      visibility: 'visible',
      packages: [
        { name: 'Basic', price: '₹4,999', deliveryTime: '5 Days', description: 'One branded reel + 2 static posts', features: ['Reel concept', 'Caption support'] },
        { name: 'Gold', price: '₹9,999', deliveryTime: '7 Days', description: 'Campaign + story & content plan', features: ['Story support', 'Analytics review'] },
        { name: 'Platinum', price: '₹14,999', deliveryTime: '10 Days', description: 'Premium creator collaboration package', features: ['Dedicated brief', 'Priority turnaround'] }
      ],
      stats: { followers: '2.4M', rating: '4.9', projects: '48', experience: '6 yrs' },
      adminNotes: 'Approved for public creator listing.'
    },
    {
      id: 'INF-1002',
      profileImage: 'assets/images/user.png',
      fullName: 'Riya Kapoor',
      email: 'riya@example.com',
      password: 'password123',
      contactNumber: '+91 98112 33445',
      whatsappNumber: '+91 98112 33445',
      city: 'Bengaluru',
      brandName: 'Riya Creates',
      about: 'Design-forward creator exploring mindful living and artisan stories.',
      category: 'Home Decor & Design',
      socialLinks: 'https://youtube.com/@riya-creates',
      portfolioUrl: 'https://example.com/riya-creates',
      portfolioFiles: [],
      availability: 'Full-time',
      verificationDocument: 'uploads/verification/riya-id.pdf',
      status: 'pending',
      hideProfile: false,
      visibility: 'hidden',
      packages: [],
      stats: { followers: '180K', rating: '4.7', projects: '12', experience: '3 yrs' },
      adminNotes: 'Awaiting admin review.'
    },
    {
      id: 'INF-1003',
      profileImage: 'assets/images/user.png',
      fullName: 'Aarav Nair',
      email: 'aarav@example.com',
      password: 'password123',
      contactNumber: '+91 97000 11223',
      whatsappNumber: '+91 97000 11223',
      city: 'Kochi',
      brandName: 'Aarav Stories',
      about: 'Travel and culture creator spotlighting underrepresented crafts.',
      category: 'Travel & Culture',
      socialLinks: 'https://instagram.com/aarav-stories',
      portfolioUrl: 'https://example.com/aarav-stories',
      portfolioFiles: [],
      availability: 'Part-time',
      verificationDocument: 'uploads/verification/aarav-id.pdf',
      status: 'rejected',
      hideProfile: true,
      visibility: 'hidden',
      packages: [],
      stats: { followers: '95K', rating: '4.4', projects: '8', experience: '2 yrs' },
      adminNotes: 'Rejected due to incomplete document.'
    }
  ];

  function readRequests() {
    try {
      const raw = localStorage.getItem(STORE_KEY);
      if (raw) return JSON.parse(raw);
    } catch (err) {
      return seededRequests;
    }

    localStorage.setItem(STORE_KEY, JSON.stringify(seededRequests));
    return seededRequests;
  }

  function writeRequests(requests) {
    localStorage.setItem(STORE_KEY, JSON.stringify(requests));
  }

  function normalizeStatus(status) {
    return String(status || 'pending').toLowerCase();
  }

  function badgeClass(status) {
    const classes = {
      pending: 'ci-status ci-status-pending',
      approved: 'ci-status ci-status-active',
      rejected: 'ci-status ci-status-archived',
      blocked: 'ci-status ci-status-inactive',
      hidden: 'ci-status ci-status-pending',
      visible: 'ci-status ci-status-active'
    };
    return classes[status] || 'ci-status ci-status-pending';
  }

  function initInfluencerRegistrationForm() {
    const form = document.getElementById('influencer-registration-form');
    if (!form) return;

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        if (typeof showToast === 'function') showToast('Please fill in the required influencer fields correctly.', 'error');
        return;
      }

      const data = Object.fromEntries(new FormData(form).entries());
      const registry = readRequests();
      const newRequest = {
        id: 'INF-' + (Date.now() % 900000 + 100000),
        profileImage: 'assets/images/user.png',
        fullName: data.fullName,
        email: data.email,
        password: data.password,
        contactNumber: data.contactNumber,
        whatsappNumber: data.whatsappNumber,
        city: data.city,
        brandName: data.brandName,
        about: data.about,
        category: data.category,
        socialLinks: data.socialLinks,
        portfolioUrl: data.portfolioUrl,
        portfolioFiles: [],
        availability: data.availability,
        verificationDocument: data.governmentDocument || 'uploads/verification/' + data.email + '.pdf',
        status: 'pending',
        hideProfile: false,
        visibility: 'hidden',
        packages: [],
        stats: { followers: '0', rating: '0', projects: '0', experience: 'New' },
        adminNotes: 'Awaiting admin review.'
      };
      registry.unshift(newRequest);
      writeRequests(registry);
      if (typeof showToast === 'function') {
        showToast('Registration submitted. Your influencer profile is pending approval.', 'success');
      } else {
        window.alert('Registration submitted. Your influencer profile is pending approval.');
      }
      form.reset();
      form.classList.remove('was-validated');
    });
  }

  function initInfluencerLoginForm() {
    const form = document.getElementById('influencer-login-form');
    if (!form) return;

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        if (typeof showToast === 'function') showToast('Please enter your influencer email and password.', 'error');
        return;
      }

      const data = Object.fromEntries(new FormData(form).entries());
      const registry = readRequests();
      const matched = registry.find(function (entry) {
        return entry.email === data.email && entry.password === data.password;
      });

      if (!matched) {
        if (typeof showToast === 'function') showToast('No approved influencer account matches those credentials.', 'error');
        return;
      }

      if (normalizeStatus(matched.status) !== 'approved' || matched.hideProfile) {
        if (typeof showToast === 'function') showToast('This influencer account is still pending approval or is hidden by admin.', 'error');
        return;
      }

      if (typeof showToast === 'function') showToast('Login successful. Redirecting to the influencer dashboard.', 'success');
      window.location.href = 'dashboard.html';
    });
  }

  function initAdminModeration() {
    const tableBody = document.getElementById('influencer-requests-body');
    const tabs = document.querySelectorAll('[data-influencer-status-tab]');
    const stats = document.querySelectorAll('[data-influencer-summary]');
    if (!tableBody) return;

    function render() {
      const registry = readRequests();
      const counts = {
        pending: 0,
        approved: 0,
        rejected: 0,
        blocked: 0,
        hidden: 0
      };

      registry.forEach(function (request) {
        const status = normalizeStatus(request.status);
        if (counts[status] !== undefined) counts[status] += 1;
        if (request.hideProfile && normalizeStatus(status) === 'approved') counts.hidden += 1;
      });

      stats.forEach(function (stat) {
        const key = stat.getAttribute('data-influencer-summary');
        stat.textContent = counts[key] || 0;
      });

      const activeTab = document.querySelector('.ci-admin-tab.active')?.getAttribute('data-influencer-status-tab') || 'pending';
      const filtered = registry.filter(function (request) {
        const status = normalizeStatus(request.status);
        if (activeTab === 'hidden') return request.hideProfile && status === 'approved';
        return status === activeTab;
      });

      tableBody.innerHTML = filtered.map(function (request) {
        const status = normalizeStatus(request.status);
        const visibilityLabel = request.hideProfile ? 'Hidden' : 'Visible';
        return `
          <tr>
            <td>
              <div class="ci-entity-cell">
                <div class="ci-entity-avatar">${(request.fullName || 'I').charAt(0).toUpperCase()}</div>
                <div>
                  <div class="ci-entity-name">${request.fullName || 'Influencer'} </div>
                  <div class="ci-entity-meta">${request.brandName || request.email}</div>
                </div>
              </div>
            </td>
            <td>${request.city || '—'}</td>
            <td>${request.category || '—'}</td>
            <td>${request.contactNumber || request.email || '—'}</td>
            <td><span class="${badgeClass(status)}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
            <td><span class="${badgeClass(visibilityLabel.toLowerCase())}">${visibilityLabel}</span></td>
            <td class="text-end">
              <div class="ci-admin-row-actions">
                <button class="ci-table-action-btn" data-action="view" data-id="${request.id}" title="View"><i class="fa-solid fa-eye"></i></button>
                <button class="ci-table-action-btn" data-action="approve" data-id="${request.id}" title="Approve"><i class="fa-solid fa-circle-check"></i></button>
                <button class="ci-table-action-btn" data-action="reject" data-id="${request.id}" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                <button class="ci-table-action-btn" data-action="block" data-id="${request.id}" title="Block"><i class="fa-solid fa-ban"></i></button>
                <button class="ci-table-action-btn" data-action="hide" data-id="${request.id}" title="Hide"><i class="fa-solid fa-eye-slash"></i></button>
                <button class="ci-table-action-btn danger" data-action="delete" data-id="${request.id}" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
        `;
      }).join('') || '<tr><td colspan="7" class="text-center text-muted py-4">No requests in this status.</td></tr>';
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (item) { item.classList.remove('active'); });
        tab.classList.add('active');
        render();
      });
    });

    tableBody.addEventListener('click', function (event) {
      const button = event.target.closest('[data-action]');
      if (!button) return;
      const registry = readRequests();
      const id = button.getAttribute('data-id');
      const action = button.getAttribute('data-action');
      const request = registry.find(function (item) { return item.id === id; });
      if (!request) return;

      if (action === 'approve') request.status = 'approved';
      if (action === 'reject') request.status = 'rejected';
      if (action === 'block') request.status = 'blocked';
      if (action === 'hide') { request.hideProfile = true; request.visibility = 'hidden'; }
      if (action === 'delete') {
        const index = registry.findIndex(function (item) { return item.id === id; });
        registry.splice(index, 1);
        writeRequests(registry);
        render();
        return;
      }

      if (request.status === 'blocked' && action !== 'approve') {
        request.hideProfile = true;
        request.visibility = 'hidden';
      }

      if (action === 'view') {
        const details = JSON.stringify(request, null, 2);
        window.alert('Influencer details:\n\n' + details);
        return;
      }

      writeRequests(registry);
      render();
    });

    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initInfluencerRegistrationForm();
    initInfluencerLoginForm();
    initAdminModeration();
  });
})();
