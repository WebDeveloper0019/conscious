/* Bridges the legacy dashboard.html to the real api/influencer/dashboard.php */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', async function () {
    try {
      // 1. Fetch the real data from your API
      const response = await fetch('../api/influencer/dashboard.php', { 
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin' 
      });
      
      if (!response.ok) {
          if (response.status === 401) {
              window.location.href = '../influencer-login.html'; // Kick out if not logged in
              return;
          }
          throw new Error('API Error');
      }

      const data = await response.json();
      if (!data.success) throw new Error(data.message);

      // 2. Update the Stat Cards
      const statCards = document.querySelectorAll('.ci-stat-value');
      if (statCards.length >= 4) {
          // API sends strings/numbers, we format them
          statCards[0].textContent = new Intl.NumberFormat('en-IN').format(data.stats.clicks || 0);
          statCards[1].textContent = new Intl.NumberFormat('en-IN').format(data.stats.conversions || 0);
          statCards[2].textContent = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(data.stats.revenue || 0);
          statCards[3].textContent = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(data.stats.pending_earnings || 0);
      }

      // 3. Update the Campaigns Widget
      const campaignsBody = document.querySelector('.ci-widget-grid .ci-widget-card:nth-child(2) .ci-widget-body');
      if (campaignsBody && data.campaigns) {
          if (data.campaigns.length === 0) {
              campaignsBody.innerHTML = '<div style="padding:20px;text-align:center;color:#888;">No active campaigns yet.</div>';
          } else {
              campaignsBody.innerHTML = data.campaigns.map(c => `
                  <div class="ci-activity-item">
                    <div class="ci-activity-icon saffron"><i class="fa-solid fa-bullhorn"></i></div>
                    <div class="ci-activity-text">
                      <div class="ci-activity-desc"><strong>${c.title || 'Campaign'}</strong> — Link: ${c.code}</div>
                      <div class="ci-activity-time">Status: ${c.link_status}</div>
                    </div>
                  </div>
              `).join('');
          }
      }

      // 4. Update the Profile Name in the top right & sidebar
      const nameEls = document.querySelectorAll('.ci-profile-trigger-name, .ci-sidebar-user-name');
      // We will get the actual name from the session API later, for now just fix the greeting
      const greeting = document.querySelector('.ci-dash-page-sub');
      if (greeting) greeting.textContent = "Here is your real-time performance.";

    } catch (error) {
      console.error('Failed to load dashboard:', error);
    }
  });
})();