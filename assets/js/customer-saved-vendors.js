(() => {
    'use strict';

    const API_URL = '../api/user/saved-vendors.php';

    let savedVendors = [];

    const $ = (selector, parent = document) =>
        parent.querySelector(selector);

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function idempotencyKey(prefix = 'saved-vendor') {
        if (window.crypto && crypto.randomUUID) {
            return `${prefix}-${crypto.randomUUID()}`;
        }

        return `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    }

    /*
     * Get the current CSRF token from the authenticated session.
     */
    async function getCsrfToken() {
        const response = await fetch('../api/auth/me.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || 'Unable to verify the current customer session.'
            );
        }

        const token =
            data.csrf_token ||
            data.csrfToken ||
            data.csrf ||
            null;

        if (!token) {
            throw new Error(
                'CSRF token was not returned by the authenticated session.'
            );
        }

        return token;
    }

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(options.body ? {
                    'Content-Type': 'application/json'
                } : {}),
                ...(options.headers || {})
            }
        });

        let data = {};

        try {
            data = await response.json();
        } catch (error) {
            data = {
                success: false,
                message: 'Invalid server response.'
            };
        }

        if (!response.ok) {
            throw new Error(
                data.message ||
                `Request failed (${response.status}).`
            );
        }

        return data;
    }

    function getVendorName(vendor) {
        return (
            vendor.vendorName ||
            vendor.name ||
            vendor.vendor_name ||
            'Saved Vendor'
        );
    }

    function getVendorLocation(vendor) {
        const city =
            vendor.city ||
            vendor.vendorCity ||
            '';

        const state =
            vendor.state ||
            vendor.vendorState ||
            '';

        if (city && state) {
            return `${city}, ${state}`;
        }

        return city || state || 'India';
    }

    function getVendorId(vendor) {
        return (
            vendor.vendorId ||
            vendor.vendor_id ||
            vendor.id ||
            ''
        );
    }

    function getProductCount(vendor) {
        const count =
            vendor.productCount ??
            vendor.product_count ??
            vendor.products ??
            0;

        return Number(count).toLocaleString('en-IN');
    }

    function getYearsActive(vendor) {
        const years =
            vendor.yearsActive ??
            vendor.years_active ??
            vendor.activeYears ??
            null;

        if (years === null || years === '') {
            return null;
        }

        return Number(years);
    }

    function getRating(vendor) {
        const rating =
            vendor.rating ??
            vendor.averageRating ??
            vendor.average_rating ??
            5;

        return Number(rating) || 0;
    }

    function getInitial(vendor) {
        const name = getVendorName(vendor);

        return name
            .trim()
            .charAt(0)
            .toUpperCase() || 'V';
    }

    function showMessage(message, type = 'success') {
        let box = $('#saved-vendor-message');

        if (!box) {
            box = document.createElement('div');
            box.id = 'saved-vendor-message';

            const main = $('.ci-dash-main');

            if (main) {
                main.insertBefore(box, main.firstElementChild);
            }
        }

        box.textContent = message;

        box.style.cssText = `
            margin:16px 24px 0;
            padding:12px 16px;
            border-radius:10px;
            font-weight:600;
            ${
                type === 'error'
                    ? 'background:#fdecec;color:#a33a3a;border:1px solid #efb5b5;'
                    : 'background:#edf8ef;color:#28743b;border:1px solid #b8dfc0;'
            }
        `;

        clearTimeout(box._timer);

        box._timer = setTimeout(() => {
            box.remove();
        }, 4000);
    }

    function updateCount() {
        const subtitle = $('.ci-dash-page-sub');

        if (subtitle) {
            const count = savedVendors.length;

            subtitle.textContent =
                `${count} artisan store${count === 1 ? '' : 's'} you follow.`;
        }
    }

    function renderVendors() {
        const main = $('.ci-dash-main');

        if (!main) {
            return;
        }

        /*
         * Find the existing vendor grid.
         * It is the first .row.g-4 after the dashboard topbar.
         */
        const grid = main.querySelector('.row.g-4');

        if (!grid) {
            return;
        }

        if (!savedVendors.length) {
            grid.innerHTML = `
                <div class="col-12">
                    <div style="
                        background:var(--bg-primary);
                        border:1.5px solid var(--border-light);
                        border-radius:var(--r-xl);
                        padding:40px 20px;
                        text-align:center;
                    ">
                        <div style="
                            font-size:2rem;
                            margin-bottom:10px;
                        ">🏪</div>

                        <div style="
                            font-weight:700;
                            color:var(--text-primary);
                            font-size:1.1rem;
                        ">
                            No saved vendors
                        </div>

                        <div style="
                            margin-top:6px;
                            color:var(--text-muted);
                        ">
                            You are not following any artisan stores yet.
                        </div>
                    </div>
                </div>
            `;

            updateCount();
            return;
        }

        grid.innerHTML = savedVendors.map((vendor, index) => {
            const vendorId = getVendorId(vendor);
            const name = getVendorName(vendor);
            const location = getVendorLocation(vendor);
            const products = getProductCount(vendor);
            const years = getYearsActive(vendor);
            const rating = getRating(vendor);
            const initial = getInitial(vendor);

            const yearsText = years !== null
                ? `${years} year${years === 1 ? '' : 's'} active`
                : 'Active vendor';

            const stars = '★★★★★';

            /*
             * Keep the existing vendor-store destination.
             * If your vendor-store page later supports vendor IDs/slugs,
             * this can be changed to the exact vendor URL.
             */
            const storeUrl = '../vendor-store.html';

            return `
                <div class="col-md-4 bc-reveal" data-vendor-id="${escapeHtml(vendorId)}">
                    <div style="
                        background:var(--bg-primary);
                        border:1.5px solid var(--border-light);
                        border-radius:var(--r-xl);
                        overflow:hidden;
                        text-align:center;
                    ">

                        <div style="
                            height:80px;
                            background:linear-gradient(
                                135deg,
                                var(--saffron),
                                var(--gold-dark)
                            );
                        "></div>

                        <div style="
                            margin-top:-30px;
                            padding:0 20px 20px;
                        ">

                            <div
                                class="ci-sidebar-avatar mx-auto mb-2"
                                style="
                                    width:60px;
                                    height:60px;
                                    border:3px solid white;
                                    font-size:1.25rem;
                                "
                            >
                                ${escapeHtml(initial)}
                            </div>

                            <div style="
                                font-weight:700;
                                color:var(--text-primary);
                            ">
                                ${escapeHtml(name)}
                            </div>

                            <div style="
                                font-size:.8125rem;
                                color:var(--text-muted);
                            ">
                                ${escapeHtml(location)}
                            </div>

                            <div
                                class="bc-stars"
                                style="
                                    justify-content:center;
                                    margin:6px 0;
                                    font-size:.875rem;
                                "
                            >
                                ${stars}
                            </div>

                            <div style="
                                font-size:.8125rem;
                                color:var(--text-muted);
                                margin-bottom:12px;
                            ">
                                ${products} products · ${escapeHtml(yearsText)}
                            </div>

                            <div class="d-flex gap-2">

                                <a
                                    href="${storeUrl}"
                                    class="bc-btn bc-btn-outline bc-btn-sm flex-fill"
                                >
                                    Visit Store
                                </a>

                                <button
                                    type="button"
                                    class="bc-btn bc-btn-outline bc-btn-sm js-remove-saved-vendor"
                                    data-vendor-id="${escapeHtml(vendorId)}"
                                    style="
                                        color:#E53E3E;
                                        border-color:#E53E3E;
                                    "
                                    aria-label="Remove ${escapeHtml(name)} from saved vendors"
                                    title="Remove saved vendor"
                                >
                                    <i class="fa-solid fa-heart-broken"></i>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        updateCount();

        attachRemoveHandlers();
    }

    async function loadSavedVendors() {
        try {
            const data = await apiRequest(API_URL);

            savedVendors = Array.isArray(data.savedVendors)
                ? data.savedVendors
                : [];

            renderVendors();

        } catch (error) {
            console.error('Saved vendors load failed:', error);

            showMessage(
                error.message || 'Unable to load saved vendors.',
                'error'
            );
        }
    }

    async function removeSavedVendor(vendorId, button) {
        if (!vendorId) {
            showMessage('Vendor ID is missing.', 'error');
            return;
        }

        const vendor = savedVendors.find(
            item => String(getVendorId(item)) === String(vendorId)
        );

        const vendorName = vendor
            ? getVendorName(vendor)
            : 'this vendor';

        const confirmed = window.confirm(
            `Remove ${vendorName} from your saved vendors?`
        );

        if (!confirmed) {
            return;
        }

        button.disabled = true;

        try {
            const csrfToken = await getCsrfToken();

            const url =
                `${API_URL}?vendor_id=${encodeURIComponent(vendorId)}`;

            const data = await apiRequest(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Idempotency-Key': idempotencyKey('saved-vendor-remove')
                }
            });

            showMessage(
                data.message || 'Vendor removed from saved vendors.'
            );

            /*
             * Reload from the server.
             * This proves the database is the source of truth.
             */
            await loadSavedVendors();

        } catch (error) {
            console.error('Saved vendor removal failed:', error);

            button.disabled = false;

            showMessage(
                error.message || 'Unable to remove saved vendor.',
                'error'
            );
        }
    }

    function attachRemoveHandlers() {
        document
            .querySelectorAll('.js-remove-saved-vendor')
            .forEach(button => {
                button.addEventListener('click', () => {
                    removeSavedVendor(
                        button.dataset.vendorId,
                        button
                    );
                });
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSavedVendors();
    });

})();