
(function () {
    'use strict';

    const API_URL = new URL(
        '../api/user/rewards.php',
        window.location.href
    ).href;

    let isBusy = false;

    async function apiRequest(action, data = {}) {
        const response = await fetch(API_URL, {
            method: action ? 'POST' : 'GET',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: action
                ? JSON.stringify({ action, ...data })
                : undefined
        });

        const result = await response.json();

        if (!response.ok || result.success === false) {
            throw new Error(
                result.message || result.error || 'Request failed.'
            );
        }

        return result;
    }

    function findStatValue(labelText) {
        const labels = document.querySelectorAll('.ci-stat-label');

        for (const label of labels) {
            if (
                label.textContent.trim().toLowerCase()
                    .includes(labelText.toLowerCase())
            ) {
                return label.closest('.ci-stat-card')
                    ?.querySelector('.ci-stat-value');
            }
        }

        return null;
    }

    function updateBalance(points) {
        const balanceElement = findStatValue('Craft Points');
        const redeemValueElement = findStatValue('Redeemable Value');

        if (balanceElement) {
            balanceElement.textContent =
                Number(points).toLocaleString('en-IN');
        }

        if (redeemValueElement) {
            redeemValueElement.textContent =
                '₹' + Number(points / 10).toLocaleString('en-IN');
        }

        const summary = document.querySelector('.ci-dash-page-sub');

        if (summary) {
            summary.textContent =
                Number(points).toLocaleString('en-IN') +
                ' Craft Points';
        }
    }

    function getHistoryContainer() {
        let container = document.getElementById('ci-reward-history');

        if (container) {
            return container;
        }

        container = document.createElement('section');
        container.id = 'ci-reward-history';
        container.className = 'ci-chart-card';
        container.style.marginTop = '24px';

        container.innerHTML = `
            <div class="ci-chart-header">
                <h3>Reward History</h3>
            </div>
            <div class="ci-chart-body" style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding:12px">Date</th>
                            <th style="text-align:left;padding:12px">Description</th>
                            <th style="text-align:left;padding:12px">Points</th>
                            <th style="text-align:left;padding:12px">Status</th>
                        </tr>
                    </thead>
                    <tbody id="ci-reward-history-body">
                        <tr>
                            <td colspan="4" style="padding:12px">
                                Loading history...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;

        const main = document.querySelector('main') ||
            document.querySelector('.ci-dashboard-content') ||
            document.querySelector('.ci-dash-content') ||
            document.body;

        main.appendChild(container);

        return container;
    }

    function renderHistory(transactions) {
        getHistoryContainer();

        const tbody = document.getElementById(
            'ci-reward-history-body'
        );

        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" style="padding:12px">
                        No reward history found.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = '';

        transactions.forEach(function (item) {
            const tr = document.createElement('tr');

            const date = document.createElement('td');
            const description = document.createElement('td');
            const points = document.createElement('td');
            const status = document.createElement('td');

            date.style.padding = '12px';
            description.style.padding = '12px';
            points.style.padding = '12px';
            status.style.padding = '12px';

            date.textContent = item.createdAt
                ? new Date(
                    item.createdAt.replace(' ', 'T') + 'Z'
                ).toLocaleString()
                : '—';

            description.textContent =
                item.description || item.entryType || 'Reward activity';

            const amount = Number(item.points || 0);

            points.textContent =
                (amount > 0 ? '+' : '') +
                amount.toLocaleString('en-IN');

            status.textContent = item.status || 'active';

            tr.append(date, description, points, status);
            tbody.appendChild(tr);
        });
    }

    function setupRedeemButtons(balance) {
        const buttons = document.querySelectorAll(
            '.ci-chart-card button'
        );

        buttons.forEach(function (button) {
            const card = button.closest('.ci-chart-card');

            if (!card ||
                !card.textContent.toLowerCase().includes('redeem your points')) {
                return;
            }

            const cardText = button.parentElement?.parentElement
                ?.textContent || '';

            const match = cardText.match(
                /([\d,]+)\s*Points/i
            );

            if (!match) return;

            const points = Number(match[1].replace(/,/g, ''));

            button.dataset.rewardPoints = String(points);
            button.disabled = isBusy || points > balance;

            if (!button.dataset.rewardListener) {
                button.dataset.rewardListener = 'true';

                button.addEventListener('click', async function (event) {
                    event.preventDefault();

                    const requestedPoints = Number(
                        button.dataset.rewardPoints
                    );

                    if (!requestedPoints || isBusy) return;

                    if (!confirm(
                        `Redeem ${requestedPoints.toLocaleString('en-IN')} points? ` +
                        'This will deduct points from your rewards balance.'
                    )) {
                        return;
                    }

                    isBusy = true;

                    try {
                        await apiRequest('redeem', {
                            points: requestedPoints
                        });

                        alert('Points redeemed successfully.');
                        await loadRewards();
                    } catch (error) {
                        alert('Could not redeem points: ' + error.message);
                    } finally {
                        isBusy = false;
                        loadRewards();
                    }
                });
            }
        });
    }

    async function loadRewards() {
        try {
            const result = await apiRequest();

            const balance = Number(
                result.balance ?? result.points ?? 0
            );

            updateBalance(balance);
            renderHistory(result.transactions || []);
            setupRedeemButtons(balance);
        } catch (error) {
            console.error('Rewards loading error:', error);

            const container = getHistoryContainer();
            const tbody = container.querySelector(
                '#ci-reward-history-body'
            );

            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="padding:12px">
                            Could not load rewards. Please refresh the page.
                        </td>
                    </tr>
                `;
            }
        }
    }

    async function init() {
        try {
            await apiRequest('expire');
        } catch (error) {
            console.warn('Reward expiry check:', error.message);
        }

        await loadRewards();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
