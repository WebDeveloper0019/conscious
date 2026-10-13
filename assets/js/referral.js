/* Conscious Indi — Referral Program hydration */
'use strict';

(function () {
  function escapeHtml(v) {
    return String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'})[c]);
  }
  function money(v) {
    return new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(Number(v||0));
  }
  function dateText(v) {
    if (!v) return '—';
    const d = new Date(String(v).replace(' ','T') + (String(v).includes('Z') ? '' : 'Z'));
    return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleDateString('en-IN', { day:'numeric', month:'short', year:'numeric' });
  }

  async function getJson(url) {
    const r = await fetch(url, { credentials:'same-origin', headers:{ Accept:'application/json' } });
    const d = await r.json().catch(() => ({}));
    if (!r.ok || d.success === false) {
      const e = new Error(d.message || 'Request failed.');
      e.status = r.status;
      throw e;
    }
    return d;
  }

  function setText(sel, val) {
    document.querySelectorAll(sel).forEach(el => { el.textContent = val; });
  }

  function renderFriends(list) {
    const box = document.querySelector('[data-ref-friends]');
    if (!box) return;
    if (!list.length) {
      box.innerHTML = '<div class="ci-empty-state" style="padding:24px;text-align:center;color:var(--text-muted);">No referrals yet — share your link to get started!</div>';
      return;
    }

    const iconByStatus = {
      completed: { cls:'green',   text: 'Signed up &amp; purchased · Reward: +' },
      pending:   { cls:'saffron', text: 'Signed up, awaiting first purchase' },
      cancelled: { cls:'navy',    text: 'Referral cancelled' }
    };

    box.innerHTML = list.map(f => {
      const m = iconByStatus[f.status] || iconByStatus.pending;
      const rewardTxt = f.status === 'completed'
        ? m.text + money(f.reward).replace('₹','₹')
        : m.text;

      return '<div class="ci-activity-item">'
        + '<div class="ci-activity-icon ' + m.cls + '"><i class="fa-solid fa-user"></i></div>'
        + '<div class="ci-activity-text">'
        +   '<div class="ci-activity-desc"><strong>' + escapeHtml(f.name) + '</strong> — ' + rewardTxt + '</div>'
        +   '<div class="ci-activity-time">' + escapeHtml(dateText(f.completedAt || f.joinedAt || f.createdAt)) + '</div>'
        + '</div></div>';
    }).join('');
  }

  function bindCopy(link) {
    document.querySelectorAll('[data-ref-copy]').forEach(btn => {
      btn.addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(link);
          if (typeof showToast === 'function') showToast('Referral link copied!', 'success');
        } catch {
          if (typeof showToast === 'function') showToast('Could not copy — long-press to copy', 'error');
        }
      });
    });
  }

  function bindShares(link, code) {
    const msg   = 'Join Conscious Indi with my link and we both earn ₹500 on your first purchase!';
    const enc   = encodeURIComponent;
    const waUrl = 'https://wa.me/?text=' + enc(msg + ' ' + link);
    const emUrl = 'mailto:?subject=' + enc('You’re invited to Conscious Indi') + '&body=' + enc(msg + '\n\n' + link);
    const igUrl = 'https://www.instagram.com/'; // IG has no direct share link

    document.querySelectorAll('[data-ref-share="whatsapp"]').forEach(b => b.addEventListener('click', () => window.open(waUrl, '_blank')));
    document.querySelectorAll('[data-ref-share="email"]').forEach(b => b.addEventListener('click', () => window.location.href = emUrl));
    document.querySelectorAll('[data-ref-share="instagram"]').forEach(b => b.addEventListener('click', () => {
      navigator.clipboard?.writeText(link).catch(() => {});
      if (typeof showToast === 'function') showToast('Link copied — paste it into your Instagram post!', 'saffron');
      window.open(igUrl, '_blank');
    }));
  }

  async function hydrateReferral() {
    const root = document.querySelector('[data-referral-page]');
    if (!root) return;

    try {
      const data = await getJson('../api/user/referral.php');
      const s = data.stats || {};

      // Link + code
      document.querySelectorAll('[data-ref-link]').forEach(el => {
        if (el.tagName === 'INPUT') el.value = data.link; else el.textContent = data.link;
      });
      setText('[data-ref-code]', data.code || '—');

      // Stats
      setText('[data-ref-stat="totalReferred"]', Number(s.totalReferred || 0).toLocaleString('en-IN'));
      setText('[data-ref-stat="totalEarned"]',   money(s.totalEarned));
      setText('[data-ref-stat="pending"]',       Number(s.pending || 0).toLocaleString('en-IN'));
      setText('[data-ref-stat="rewardPer"]',     money(s.rewardPerReferral || 0));

      renderFriends(data.friends || []);
      bindCopy(data.link);
      bindShares(data.link, data.code);
    } catch (error) {
      if (error.status === 401) {
        window.location.href = '../../login.html?redirect=customer/referral.html';
      } else {
        document.querySelectorAll('[data-ref-error]').forEach(el => el.textContent = error.message || 'Unable to load referral data.');
      }
    }
  }

  if (document.readyState !== 'loading') hydrateReferral();
  else document.addEventListener('DOMContentLoaded', hydrateReferral);
})();