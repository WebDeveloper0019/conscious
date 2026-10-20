(function () {
'use strict';

var API_URL = new URL('../api/user/rewards.php', window.location.href).href;
var PROFILE_URL = new URL('../api/user/profile.php', window.location.href).href;
var DEFAULT_REDEEM_RATE = 0.10;

var TIER_ORDER = ['bronze', 'silver', 'gold', 'platinum'];

var TIER_META = {
bronze: { label: 'Bronze', threshold: 0, benefits: 'Base earn rate · Standard support' },
silver: { label: 'Silver', threshold: 1000, benefits: '5% bonus points · Faster support' },
gold: { label: 'Gold', threshold: 2500, benefits: '10% bonus points · Priority support · Early sale access' },
platinum: { label: 'Platinum', threshold: 5000, benefits: '20% bonus · Free express delivery · Dedicated manager' }
};
function esc(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function inr(n) {
var v = Number(n || 0);
return '\u20B9' + v.toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

function num(n) {
return Number(n || 0).toLocaleString('en-IN');
}

function getCsrfToken() {
var m = document.cookie.match(/(?:^|;\s*)ci_csrf=([^;]+)/);
return m ? decodeURIComponent(m[1]) : '';
}

function apiGet(url) {
return fetch(url, {
method: 'GET',
credentials: 'include',
headers: { 'Accept': 'application/json' }
}).then(function (r) { return r.json(); });
}

function setText(id, value) {
var el = document.getElementById(id);
if (el) el.textContent = value;
}

function showSub(id, text) {
var el = document.getElementById(id);
if (!el) return;
var span = el.querySelector('span');
if (span) span.textContent = text;
el.style.visibility = 'visible';
}

function loadSidebarUser() {
apiGet(PROFILE_URL).then(function (res) {
if (!res || res.success === false || !res.user) return;
var u = res.user;
var first = u.first_name || '';
var last = u.last_name || '';
var full = (first + ' ' + last).trim() || u.email || 'Customer';

setText('ci-sidebar-user-name', full);
setText('ci-sidebar-user-email', u.email || '\u2014');

var av = document.getElementById('ci-sidebar-avatar');
if (av) av.textContent = (first.charAt(0) || u.email || '?').toUpperCase();
}).catch(function () {});
}

function renderStats(data) {
var balance = Number(data.balance || 0);
var rate = Number(data.redeemRate || data.redeem_rate || DEFAULT_REDEEM_RATE);
var redeemValue = balance * rate;

var tierKey = String(data.tier || 'bronze').toLowerCase();
var tier = TIER_META[tierKey] || TIER_META.bronze;

setText('ci-reward-points', num(balance));
setText('ci-reward-tier', tier.label);
setText('ci-reward-value', inr(redeemValue));

if (data.rank != null) {
setText('ci-reward-rank', '#' + Number(data.rank).toLocaleString('en-IN'));
if (data.rankPercentile != null) {
showSub('ci-reward-rank-sub', 'Top ' + data.rankPercentile + '% of users');
}
} else {
setText('ci-reward-rank', '\u2014');
}

if (data.lifetime && data.lifetime.earned != null) {
showSub('ci-reward-points-sub', num(data.lifetime.earned) + ' lifetime earned');
}

setText('ci-rewards-sub', num(balance) + ' Craft Points \u00B7 Level: ' + tier.label);

renderProgress(balance, tierKey);
renderBenefits(tierKey);
renderRedeemGrid(balance, rate);
}

function renderProgress(balance, tierKey) {
var current = TIER_META[tierKey] || TIER_META.bronze;
var idx = TIER_ORDER.indexOf(tierKey);
var nextKey = idx >= 0 && idx < TIER_ORDER.length - 1 ? TIER_ORDER[idx + 1] : null;
var next = nextKey ? TIER_META[nextKey] : null;

setText('ci-progress-current-tier', current.label + ' Member');

var pct = 100;
if (next) {
var span = next.threshold - current.threshold;
var earned = balance - current.threshold;
pct = span > 0 ? Math.max(0, Math.min(100, (earned / span) * 100)) : 100;
setText('ci-progress-text', num(balance) + ' / ' + num(next.threshold) + ' points to ' + next.label);
} else {
setText('ci-progress-text', 'Highest tier reached');
}

var bar = document.getElementById('ci-progress-bar');
if (bar) bar.style.width = pct.toFixed(1) + '%';

document.querySelectorAll('[data-tier]').forEach(function (el) {
var t = el.getAttribute('data-tier');
var isCurrent = t === tierKey;
var isPast = TIER_ORDER.indexOf(t) < TIER_ORDER.indexOf(tierKey);
el.style.color = isCurrent ? 'var(--gold)' : '';
el.style.fontWeight = isCurrent ? '600' : '';
el.textContent = TIER_META[t].label + (isCurrent || isPast ? ' \u2713' : '');
});
}

function renderBenefits(tierKey) {
var wrap = document.getElementById('ci-tier-benefits');
if (!wrap) return;

var idx = TIER_ORDER.indexOf(tierKey);
var current = TIER_META[tierKey] || TIER_META.bronze;
var nextKey = idx >= 0 && idx < TIER_ORDER.length - 1 ? TIER_ORDER[idx + 1] : null;
var next = nextKey ? TIER_META[nextKey] : null;

var html = '<div class="col-md-3"><div style="padding:12px;background:var(--gold-pale);border-radius:var(--r-md);text-align:center;">' +
'<div style="font-size:.75rem;color:var(--gold-dark);font-weight:700;">' + esc(current.label) + ' Benefits</div>' +
'<div style="font-size:.8125rem;color:var(--text-secondary);margin-top:4px;">' + esc(current.benefits) + '</div>' +
'</div></div>';

if (next) {
html += '<div class="col-md-3"><div style="padding:12px;background:var(--bg-cream);border-radius:var(--r-md);text-align:center;opacity:.6;">' +
'<div style="font-size:.75rem;color:var(--text-muted);font-weight:700;">' + esc(next.label) + ' (Next)</div>' +
'<div style="font-size:.8125rem;color:var(--text-muted);margin-top:4px;">' + esc(next.benefits) + '</div>' +
'</div></div>';
}

wrap.innerHTML = html;
}

function renderRedeemGrid(balance, rate) {
var grid = document.getElementById('ci-redeem-grid');
if (!grid) return;

var tiers = [500, 1000, 2000];
var cards = [];

tiers.forEach(function (pts) {
var affordable = balance >= pts;
cards.push({
points: pts,
value: pts * rate,
affordable: affordable,
label: num(pts) + ' Points',
cta: affordable ? 'Redeem' : 'Need ' + num(pts - balance) + ' more'
});
});

if (balance > 0) {
cards.push({
points: balance,
value: balance * rate,
affordable: true,
label: num(balance) + ' Points',
cta: 'Redeem All'
});
}

var html = '';
cards.forEach(function (c) {
var emphasized = c.affordable;
html += '<div class="col-md-3">' +
'<div style="padding:16px;border:1.5px solid ' + (emphasized ? 'var(--saffron)' : 'var(--border-warm)') + ';' + 'border-radius:var(--r-lg);text-align:center;' + (emphasized ? 'background:var(--saffron-pale);' : '') + '">' +
'<div style="font-weight:700;color:var(--text-primary);">' + esc(c.label) + '</div>' +
'<div style="font-size:.8125rem;color:var(--saffron-dark);font-weight:600;margin:4px 0;">= ' + esc(inr(c.value)) + ' Off</div>' +
'<button class="bc-btn ' + (emphasized ? 'bc-btn-primary' : 'bc-btn-outline') + ' bc-btn-sm" ' +
'data-redeem-points="' + c.points + '"' + (c.affordable ? '' : ' disabled') + '>' +
esc(c.cta) + '</button>' +
'</div></div>';
});

grid.innerHTML = html;

grid.querySelectorAll('button[data-redeem-points]').forEach(function (btn) {
btn.addEventListener('click', function (e) {
e.preventDefault();
alert('Reward points are redeemed during checkout.\n\nAdd items to your cart and use the "Apply Reward Points" option on the checkout page.');
});
});
}

function renderHistory(transactions) {
var tbody = document.getElementById('ci-reward-history-body');
if (!tbody) return;

if (!transactions || !transactions.length) {
tbody.innerHTML = '<tr><td colspan="4" style="padding:12px">No reward history yet.</td></tr>';
return;
}

var html = '';
transactions.forEach(function (item) {
var when = item.createdAt
? new Date(String(item.createdAt).replace(' ', 'T') + 'Z').toLocaleString('en-IN')
: '\u2014';
var pts = Number(item.points || 0);
var sign = pts > 0 ? '+' : '';
var color = pts > 0 ? 'var(--green-dark)' : 'var(--text-secondary)';
html += '<tr>' +
'<td style="padding:12px">' + esc(when) + '</td>' +
'<td style="padding:12px">' + esc(item.description || item.entryType || 'Reward activity') + '</td>' +
'<td style="padding:12px;text-align:right;color:' + color + ';font-weight:600;">' + sign + num(pts) + '</td>' +
'<td style="padding:12px;text-transform:capitalize;">' + esc(item.status || 'active') + '</td>' +
'</tr>';
});
tbody.innerHTML = html;
}

function loadRewards() {
return apiGet(API_URL).then(function (res) {
if (!res || res.success === false) {
throw new Error((res && res.message) || 'Failed to load rewards.');
}
renderStats(res);
renderHistory(res.transactions || []);
}).catch(function (err) {
console.error('Rewards load error:', err);
var tbody = document.getElementById('ci-reward-history-body');
if (tbody) {
tbody.innerHTML = '<tr><td colspan="4" style="padding:12px">Could not load rewards. Please refresh the page.</td></tr>';
}
setText('ci-rewards-sub', 'Could not load rewards.');
});
}

function init() {
loadSidebarUser();
loadRewards();
}

if (document.readyState === 'loading') {
document.addEventListener('DOMContentLoaded', init);
} else {
init();
}
})();