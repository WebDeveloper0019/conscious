(function () {
  'use strict';
  const grid = document.getElementById('vendor-package-grid');
  const message = document.getElementById('vendor-package-message');
  const search = document.getElementById('vendor-package-search');
  const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' });
  let packages = [];

  function element(tag, className, content) {
    const item = document.createElement(tag);
    if (className) item.className = className;
    if (content !== undefined) item.textContent = String(content);
    return item;
  }
  function render() {
    const term = search.value.trim().toLocaleLowerCase();
    const matches = packages.filter(pkg => [
      pkg.full_name, pkg.handle, pkg.title, pkg.description,
      ...(pkg.platforms_json || []), ...(pkg.deliverables_json || [])
    ].join(' ').toLocaleLowerCase().includes(term));
    grid.replaceChildren();
    message.textContent = matches.length
      ? matches.length + (matches.length === 1 ? ' reviewed package available.' : ' reviewed packages available.')
      : 'No approved packages match your search.';
    matches.forEach(pkg => {
      const column = element('div', 'col-md-6 col-xl-4');
      const card = element('article', 'card h-100');
      const body = element('div', 'card-body d-flex flex-column');
      body.append(
        element('small', 'text-muted', '@' + pkg.handle + ' · ' + pkg.full_name),
        element('h3', 'h6 mt-2', pkg.title),
        element('p', 'text-muted', pkg.description),
        element('p', 'small', 'Includes: ' + pkg.deliverables_json.join(', ')),
        element('p', 'small', 'Platforms: ' + pkg.platforms_json.join(', ')),
        element('p', 'small', 'Turnaround: ' + pkg.turnaround_days + ' days · Revisions: ' + pkg.revisions),
        element('p', 'small', 'Usage rights: ' + pkg.usage_rights),
        element('p', 'small', 'Vendor requirements: ' + pkg.vendor_requirements),
        element('p', 'small', 'Cancellation: ' + pkg.cancellation_terms),
        element('strong', 'mt-auto', money.format(Number(pkg.price_inr)))
      );
      const link = element('a', 'btn btn-outline-primary mt-3', 'View creator and enquire');
      link.href = '../../../influencer-profile.php?handle=' + encodeURIComponent(pkg.handle);
      body.append(link);
      card.append(body);
      column.append(card);
      grid.append(column);
    });
  }
  search.addEventListener('input', render);
  fetch('../../../api/public/packages.php', { headers: { Accept: 'application/json' }, cache: 'no-store' })
    .then(async response => {
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Packages could not be loaded.');
      packages = Array.isArray(data.packages) ? data.packages : [];
      render();
    })
    .catch(error => { message.textContent = error.message; });
})();
