<div class="av-shell">
  <section class="av-summary" aria-label="Application summary">
    <article><span class="av-summary-icon pending"><i class="fa-regular fa-clock"></i></span><div><small>Waiting for review</small><strong id="av-count-pending">—</strong></div></article>
    <article><span class="av-summary-icon review"><i class="fa-solid fa-magnifying-glass"></i></span><div><small>Under review</small><strong id="av-count-review">—</strong></div></article>
    <article><span class="av-summary-icon changes"><i class="fa-solid fa-rotate"></i></span><div><small>Changes requested</small><strong id="av-count-changes">—</strong></div></article>
    <article><span class="av-summary-icon approved"><i class="fa-solid fa-circle-check"></i></span><div><small>Approved</small><strong id="av-count-approved">—</strong></div></article>
  </section>
  <section class="ac-card av-filter-card" aria-labelledby="av-filter-title">
    <div class="av-filter-heading"><div><span class="ac-kicker">REVIEW QUEUE</span><h2 id="av-filter-title">Vendor applications</h2><p>Search and filter real seller submissions.</p></div><button class="ac-btn" id="av-refresh" type="button"><i class="fa-solid fa-rotate"></i> Refresh</button></div>
    <div class="av-filters">
      <label class="av-search"><span>Search</span><div><i class="fa-solid fa-magnifying-glass"></i><input class="ac-input" id="av-search" type="search" placeholder="Reference, shop, owner, or email"></div></label>
      <label><span>Status</span><select class="ac-select" id="av-status"><option value="">All statuses</option><option value="submitted">Submitted</option><option value="under_review">Under review</option><option value="changes_requested">Changes requested</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select></label>
      <label><span>Vendor type</span><select class="ac-select" id="av-type"><option value="">All vendor types</option><option value="artisan">Artisan</option><option value="brand">Brand</option></select></label>
      <label><span>Per page</span><select class="ac-select" id="av-limit"><option value="10">10</option><option value="20">20</option><option value="50">50</option></select></label>
      <button class="ac-btn av-clear" id="av-clear" type="button">Clear filters</button>
    </div>
  </section>
  <div class="av-result-bar"><p id="av-message" role="status">Loading vendor applications…</p><span id="av-result-count"></span></div>
  <section class="ac-card av-table-card">
    <div class="ac-table-wrap">
      <table class="ac-table av-table">
        <thead><tr><th>Vendor</th><th>Application</th><th>Submitted</th><th>Completion</th><th>Status</th><th class="av-actions-heading">Actions</th></tr></thead>
        <tbody id="av-rows"></tbody>
      </table>
    </div>
  </section>
  <nav class="av-pagination" aria-label="Vendor application pages"><button class="ac-btn" id="av-prev" type="button"><i class="fa-solid fa-chevron-left"></i> Previous</button><span id="av-page-label">Page 1 of 1</span><button class="ac-btn" id="av-next" type="button">Next <i class="fa-solid fa-chevron-right"></i></button></nav>
</div>
