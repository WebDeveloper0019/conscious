'use strict';

(function () {

  var currentPage = 1;
  var currentFilter = 'all';
  var loading = false;
  var hasMore = true;

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, function (ch) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;'
      }[ch];
    });
  }

  function formatDate(value) {
    if (!value) return '';

    var d = new Date(
      String(value).replace(' ', 'T') + 'Z'
    );

    if (Number.isNaN(d.getTime())) {
      return String(value);
    }

    return d.toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    }) + ' · ' + d.toLocaleTimeString('en-IN', {
      hour: 'numeric',
      minute: '2-digit'
    });
  }

  function formatDay(value) {
    if (!value) return 'Activity';

    var d = new Date(
      String(value).replace(' ', 'T') + 'Z'
    );

    if (Number.isNaN(d.getTime())) {
      return 'Activity';
    }

    var today = new Date();

    var yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (d.toDateString() === today.toDateString()) {
      return 'Today';
    }

    if (d.toDateString() === yesterday.toDateString()) {
      return 'Yesterday';
    }

    return d.toLocaleDateString('en-IN', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }

  function getIcon(activity) {
    return activity.icon || 'fa-clock-rotate-left';
  }

  function getIconClass(activity) {
    return activity.iconClass || 'navy';
  }

  function renderActivities(list, append) {

    var container = document.querySelector('[data-activity-list]');

    if (!container) return;

    if (!append) {
      container.innerHTML = '';
    }

    if (!list.length && !append) {
      container.innerHTML =
        '<div class="ci-empty-state" style="padding:40px;text-align:center;color:var(--text-muted);">' +
        '<i class="fa-solid fa-clock-rotate-left" style="font-size:28px;margin-bottom:12px;"></i>' +
        '<div style="font-weight:700;">No activity found</div>' +
        '<div style="font-size:.9rem;margin-top:5px;">Your activity will appear here when you interact with Conscious Indi.</div>' +
        '</div>';

      return;
    }

    var groups = {};

    list.forEach(function (activity) {
      var day = formatDay(activity.createdAt);

      if (!groups[day]) {
        groups[day] = [];
      }

      groups[day].push(activity);
    });

    Object.keys(groups).forEach(function (day) {

      var group = document.createElement('div');

      group.setAttribute(
        'data-activity-group',
        day
      );

      group.innerHTML =
        '<div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);padding:12px 24px 4px;border-top:1px solid var(--dash-border);">' +
        escapeHtml(day) +
        '</div>' +
        '<div class="ci-widget-body" style="padding-top:0;"></div>';

      var body = group.querySelector('.ci-widget-body');

      groups[day].forEach(function (activity) {

        var item = document.createElement('div');

        item.className = 'ci-activity-item';

        item.innerHTML =
          '<div class="ci-activity-icon ' +
          escapeHtml(getIconClass(activity)) +
          '">' +
          '<i class="fa-solid ' +
          escapeHtml(getIcon(activity)) +
          '"></i>' +
          '</div>' +

          '<div class="ci-activity-info">' +

          '<div class="ci-activity-title">' +
          escapeHtml(activity.title || 'Activity') +
          '</div>' +

          '<p class="mb-1 small text-muted">' +
          escapeHtml(activity.description || '') +
          '</p>' +

          '<div class="ci-activity-time">' +
          escapeHtml(formatDate(activity.createdAt)) +
          '</div>' +

          '</div>';

        body.appendChild(item);
      });

      container.appendChild(group);
    });
  }

  function setLoading(loadingState) {
    var button = document.querySelector(
      '[data-load-more-activities]'
    );

    if (!button) return;

    if (loadingState) {
      button.disabled = true;
      button.textContent = 'Loading...';
    } else {
      button.disabled = false;
      button.textContent = hasMore
        ? 'Load More Activities'
        : 'No More Activities';
    }
  }

  async function loadActivities(reset) {

    if (loading) return;

    if (!reset && !hasMore) return;

    loading = true;
    setLoading(true);

    if (reset) {
      currentPage = 1;
      hasMore = true;
    }

    try {

      var url =
        '../api/user/activity-history.php' +
        '?page=' + encodeURIComponent(currentPage) +
        '&limit=10' +
        '&filter=' + encodeURIComponent(currentFilter);

      var response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json'
        }
      });

      var data = await response.json().catch(function () {
        return {};
      });

      if (!response.ok || data.success === false) {
        throw new Error(
          data.message || 'Unable to load activity history.'
        );
      }

      var activities = Array.isArray(data.activities)
        ? data.activities
        : [];

      hasMore = Boolean(
        data.pagination &&
        data.pagination.hasMore
      );

      renderActivities(
        activities,
        !reset
      );

      if (activities.length) {
        currentPage++;
      }

    } catch (error) {

      var container = document.querySelector(
        '[data-activity-list]'
      );

      if (container && currentPage === 1) {
        container.innerHTML =
          '<div class="ci-empty-state" style="padding:40px;text-align:center;color:var(--text-muted);">' +
          escapeHtml(
            error.message || 'Unable to load activity history.'
          ) +
          '</div>';
      }

    } finally {

      loading = false;
      setLoading(false);
    }
  }

  function init() {

    var select = document.querySelector(
      '[data-activity-filter]'
    );

    var loadMore = document.querySelector(
      '[data-load-more-activities]'
    );

    if (select) {
      select.addEventListener('change', function () {

        currentFilter = String(
          this.value || 'all'
        ).toLowerCase();

        loadActivities(true);
      });
    }

    if (loadMore) {
      loadMore.addEventListener('click', function () {
        loadActivities(false);
      });
    }

    loadActivities(true);
  }

  if (document.readyState !== 'loading') {
    init();
  } else {
    document.addEventListener(
      'DOMContentLoaded',
      init
    );
  }

})();