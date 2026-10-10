
document.addEventListener('DOMContentLoaded', async () => {
    const API_URL = '../api/user/coupons.php';

    const subtitle = document.querySelector('.ci-page-subtitle');
    const statValues = document.querySelectorAll('.ci-stat-value');
    const contentRow = document.querySelector('.row.g-4');

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2
        }).format(Number(amount) || 0);
    }

    function formatDate(dateString) {
        if (!dateString) return 'No expiry date';

        // API dates are stored in UTC.
        const normalized = dateString.includes('T')
            ? dateString
            : dateString.replace(' ', 'T') + 'Z';

        const date = new Date(normalized);

        if (Number.isNaN(date.getTime())) {
            return 'Date unavailable';
        }

        return date.toLocaleDateString('en-IN', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            timeZone: 'Asia/Kolkata'
        });
    }

    function showMessage(title, message) {
        if (!contentRow) return;

        contentRow.replaceChildren();

        const column = document.createElement('div');
        column.className = 'col-12';

        const card = document.createElement('div');
        card.className = 'ci-chart-card';
        card.style.cssText = 'padding:32px;text-align:center;';

        const heading = document.createElement('h3');
        heading.textContent = title;

        const paragraph = document.createElement('p');
        paragraph.textContent = message;

        card.append(heading, paragraph);
        column.appendChild(card);
        contentRow.appendChild(column);
    }

    function createCouponCard(coupon) {
        const column = document.createElement('div');
        column.className = 'col-12 col-md-6 col-xl-4';

        const card = document.createElement('div');
        card.className = 'ci-chart-card';
        card.style.cssText = 'padding:24px;height:100%;';

        const heading = document.createElement('h3');
        heading.textContent = coupon.code || 'Coupon';

        const discount = document.createElement('p');
        discount.style.cssText = 'font-size:22px;font-weight:700;';

        if (coupon.discountType === 'percentage') {
            discount.textContent = `${coupon.discountValue}% OFF`;
        } else {
            discount.textContent =
                `${formatCurrency(coupon.discountValue)} OFF`;
        }

        const minimum = document.createElement('p');
        minimum.textContent =
            `Minimum order: ${formatCurrency(coupon.minOrderAmount)}`;

        const expiry = document.createElement('p');
        expiry.textContent = `Expires: ${formatDate(coupon.endsAt)}`;

        const uses = document.createElement('p');
        uses.textContent = coupon.remainingCustomerUses === 1
            ? '1 use remaining for you'
            : `${coupon.remainingCustomerUses} uses remaining for you`;

        const copyButton = document.createElement('button');
        copyButton.type = 'button';
        copyButton.className = 'btn btn-outline-primary';
        copyButton.textContent = 'Copy Code';

        copyButton.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(coupon.code);

                copyButton.textContent = 'Copied!';
                setTimeout(() => {
                    copyButton.textContent = 'Copy Code';
                }, 1500);
            } catch (error) {
                showMessage(
                    'Unable to copy coupon',
                    'Please copy this coupon code manually: ' + coupon.code
                );
            }
        });

        card.append(
            heading,
            discount,
            minimum,
            expiry,
            uses,
            copyButton
        );

        column.appendChild(card);
        return column;
    }

    try {
        const response = await fetch(API_URL, {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok || data.success !== true) {
            throw new Error(data.message || 'Unable to load coupons.');
        }

        const coupons = Array.isArray(data.coupons)
            ? data.coupons
            : [];

        if (subtitle) {
            subtitle.textContent = `${coupons.length} available coupon(s).`;
        }

        // Update the three existing dashboard statistics.
        const stats = data.stats || {};
        const statValuesArray = Array.from(statValues);

        if (statValuesArray[0]) {
            statValuesArray[0].textContent =
                String(stats.activeCoupons ?? coupons.length);
        }

        if (statValuesArray[1]) {
            statValuesArray[1].textContent =
                formatCurrency(stats.totalSavingsAvailable ?? 0);
        }

        if (statValuesArray[2]) {
            statValuesArray[2].textContent =
                String(stats.expiringIn7Days ?? 0);
        }

        if (!contentRow) return;

        if (coupons.length === 0) {
            showMessage(
                'No Coupons Available',
                'Your eligible coupons will appear here when available.'
            );
            return;
        }

        contentRow.replaceChildren();

        coupons.forEach(coupon => {
            contentRow.appendChild(createCouponCard(coupon));
        });

    } catch (error) {
        console.error('Coupons loading error:', error);

        showMessage(
            'Unable to Load Coupons',
            'Please refresh the page and try again.'
        );
    }
});
