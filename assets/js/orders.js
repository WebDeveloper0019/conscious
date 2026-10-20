
(function () {
    'use strict';

    var API_URL = '../api/user/orders.php';
    var PAGE_SIZE = 10;

    var tbody = document.getElementById('ordersTableBody');
    var searchInput = document.getElementById('orderSearch');
    var pagination = document.getElementById('ordersPagination');

    if (!tbody || !searchInput) return;

    var state = {
        status: 'all',
        search: '',
        offset: 0,
        total: 0,
        loading: false
    };

    var searchTimer = null;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(
            /[&<>"']/g,
            function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[char];
            }
        );
    }

    function money(value) {
        var amount = Number(value);

        if (!Number.isFinite(amount)) amount = 0;

        return '₹' + amount.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(value) {
        if (!value) return '—';

        var date = new Date(String(value).replace(' ', 'T') + 'Z');

        if (isNaN(date.getTime())) {
            date = new Date(value);
        }

        if (isNaN(date.getTime())) return escapeHtml(value);

        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function prettyStatus(status) {
        return String(status || 'processing')
            .replace(/[_-]/g, ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function showMessage(message, isError) {
        tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 ' +
            (isError ? 'text-danger' : '') +
            '">' + escapeHtml(message) + '</td></tr>';
    }

    function renderOrders(orders) {
        if (!orders.length) {
            showMessage(
                state.search || state.status !== 'all'
                    ? 'No orders match your search or filter.'
                    : 'You have no orders yet.',
                false
            );
            return;
        }

        // Match each cell to the existing table headings.
        var table = tbody.closest('table');
        var headers = table
            ? Array.from(table.querySelectorAll('thead th')).map(function (th) {
                return th.textContent.trim().toLowerCase();
            })
            : [];

        tbody.innerHTML = orders.map(function (order) {
            var cells = headers.length ? headers : [
                'order', 'date', 'items', 'total', 'status', 'action'
            ];

            var row = cells.map(function (heading) {
                if (/order|order id|order number|#/.test(heading)) {
                    return '<td>' + escapeHtml(order.orderNumber || '—') + '</td>';
                }

                if (/date|placed/.test(heading)) {
                    return '<td>' + escapeHtml(formatDate(order.createdAt)) + '</td>';
                }

          if (/item|product|quantity/.test(heading)) {
    var count = Number(order.itemCount) || 0;
    return '<td>' + count + (count === 1 ? ' item' : ' items') + '</td>';
}

                if (/total|amount/.test(heading)) {
                    return '<td>' + escapeHtml(money(order.totalAmount)) + '</td>';
                }

                if (/status/.test(heading)) {
                    return '<td><span class="badge bg-secondary">' +
                        escapeHtml(prettyStatus(order.status)) +
                        '</span></td>';
                }

              if (/action|view|detail/.test(heading)) {
    var detailsUrl =
        'order-details.html?orderNumber=' +
        encodeURIComponent(order.orderNumber || '');

    return '<td><a class="bc-btn bc-btn-outline bc-btn-sm" href="' +
        detailsUrl +
        '">View Details</a></td>';
}

                return '<td>—</td>';
            }).join('');

            return '<tr>' + row + '</tr>';
        }).join('');
    }

    function renderPagination() {
        if (!pagination) return;

        var start = state.total === 0 ? 0 : state.offset + 1;
        var end = Math.min(state.offset + PAGE_SIZE, state.total);
        var currentPage = Math.floor(state.offset / PAGE_SIZE) + 1;
        var totalPages = Math.max(1, Math.ceil(state.total / PAGE_SIZE));

        pagination.innerHTML =
            '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">' +
                '<span>Showing ' + start + '–' + end +
                ' of ' + state.total + ' orders</span>' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm" ' +
                    'data-page="prev" ' + (currentPage <= 1 ? 'disabled' : '') +
                    '>Previous</button>' +
                    '<span>Page ' + currentPage + ' of ' + totalPages + '</span>' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm" ' +
                    'data-page="next" ' +
                    (currentPage >= totalPages ? 'disabled' : '') +
                    '>Next</button>' +
                '</div>' +
            '</div>';
    }

    async function loadOrders() {
        if (state.loading) return;

        state.loading = true;
        showMessage('Loading orders…', false);

        var params = new URLSearchParams({
            limit: String(PAGE_SIZE),
            offset: String(state.offset)
        });

        if (state.status !== 'all') {
            params.set('status', state.status);
        }

        if (state.search) {
            params.set('search', state.search);
        }

        try {
            var response = await fetch(API_URL + '?' + params.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            var data;

            try {
                data = await response.json();
            } catch (_) {
                throw new Error('The server returned an invalid response.');
            }

            if (response.status === 401 || response.status === 403) {
                throw new Error(
                    data.message || 'Your session may have expired. Please sign in again.'
                );
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Could not load orders.');
            }

            if (!Array.isArray(data.orders)) {
                throw new Error('Unexpected orders response from the server.');
            }

            state.total = Number(data.total) || 0;

            // If the last page became empty, move back to the previous page.
            if (state.offset > 0 && state.offset >= state.total) {
                state.offset = Math.max(
                    0,
                    Math.floor((state.total - 1) / PAGE_SIZE) * PAGE_SIZE
                );
                state.loading = false;
                return loadOrders();
            }

            renderOrders(data.orders);
            renderPagination();

        } catch (error) {
            showMessage(error.message || 'Could not load orders. Please try again.', true);

            if (pagination) {
                pagination.innerHTML =
                    '<button type="button" class="btn btn-outline-primary btn-sm" ' +
                    'data-retry="true">Retry</button>';
            }
        } finally {
            state.loading = false;
        }
    }

    document.querySelectorAll('[data-status]').forEach(function (button) {
        button.addEventListener('click', function () {
            state.status = String(button.getAttribute('data-status') || 'all')
                .toLowerCase();

            state.offset = 0;

            document.querySelectorAll('[data-status]').forEach(function (item) {
                item.classList.toggle('active', item === button);
                item.setAttribute(
                    'aria-pressed',
                    item === button ? 'true' : 'false'
                );
            });

            loadOrders();
        });
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            state.search = searchInput.value.trim();
            state.offset = 0;
            loadOrders();
        }, 300);
    });

    if (pagination) {
        pagination.addEventListener('click', function (event) {
            var button = event.target.closest('button');

            if (!button) return;

            if (button.hasAttribute('data-retry')) {
                loadOrders();
                return;
            }

            var page = button.getAttribute('data-page');

            if (page === 'prev' && state.offset >= PAGE_SIZE) {
                state.offset -= PAGE_SIZE;
                loadOrders();
            } else if (
                page === 'next' &&
                state.offset + PAGE_SIZE < state.total
            ) {
                state.offset += PAGE_SIZE;
                loadOrders();
            }
        });
    }

    loadOrders();
})();