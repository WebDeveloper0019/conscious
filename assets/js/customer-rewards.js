(() => {
    'use strict';

    const API_URL = '../api/user/rewards.php';

    const $ = (selector, parent = document) => parent.querySelector(selector);
    const $$ = (selector, parent = document) => Array.from(parent.querySelectorAll(selector));

    let rewardBalance = 0;
    let rewardTransactions = [];

    function formatPoints(points) {
        return Number(points || 0).toLocaleString('en-IN');
    }

    function redeemableValue(points) {
        // Current UI/business rule: 10 points = ₹1
        return Math.floor(Number(points || 0) / 10);
    }

    function generateIdempotencyKey() {
        if (window.crypto && crypto.randomUUID) {
            return `rewards-${crypto.randomUUID()}`;
        }

        return `rewards-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    }

    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                'Accept': 'application/json',
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...(options.headers || {})
            }
        });

        let data = {};

        try {
            data = await response.json();
        } catch (error) {
            data = {
                success: false,
                message: 'Invalid response from server.'
            };
        }

        if (!response.ok) {
            throw new Error(
                data.message ||
                `Request failed with status ${response.status}.`
            );
        }

        return data;
    }

    function updateBalanceUI() {
        const formatted = formatPoints(rewardBalance);
        const rupees = redeemableValue(rewardBalance);

        /*
         * Topbar
         */
        const pageSub = $('.ci-dash-page-sub');

        if (pageSub) {
            pageSub.textContent =
                `${formatted} Craft Points — Level: ${getMembershipLevel(rewardBalance)} Member`;
        }

        /*
         * First stats card = Craft Points
         */
        const stats = $$('.ci-stat-card');

        if (stats[0]) {
            const value = $('.ci-stat-value', stats[0]);

            if (value) {
                value.textContent = formatted;
            }
        }

        /*
         * Membership level card
         */
        if (stats[1]) {
            const value = $('.ci-stat-value', stats[1]);

            if (value) {
                value.textContent = getMembershipLevel(rewardBalance);
            }
        }

        /*
         * Redeemable value card
         */
        if (stats[2]) {
            const value = $('.ci-stat-value', stats[2]);

            if (value) {
                value.textContent = `₹${rupees.toLocaleString('en-IN')}`;
            }
        }

        /*
         * Membership progress
         */
        updateMembershipProgress();

        /*
         * Redeem buttons
         */
        updateRedeemButtons();
    }

    function getMembershipLevel(points) {
        /*
         * These thresholds match the current page design:
         * Bronze -> Silver -> Gold -> Platinum
         *
         * Gold is currently shown at 2,840 and Platinum begins at 5,000.
         */
        if (points >= 5000) {
            return 'Platinum';
        }

        if (points >= 2000) {
            return 'Gold';
        }

        if (points >= 1000) {
            return 'Silver';
        }

        return 'Bronze';
    }

    function updateMembershipProgress() {
        const progressText = $('.ci-form-card-body .d-flex.align-items-center.justify-content-between span:nth-child(2)');
        const progressBar = $('.ci-form-card-body [style*="width:56.8%"]');

        const level = getMembershipLevel(rewardBalance);

        let nextLevelPoints;
        let currentLevelMinimum;

        if (level === 'Bronze') {
            currentLevelMinimum = 0;
            nextLevelPoints = 1000;
        } else if (level === 'Silver') {
            currentLevelMinimum = 1000;
            nextLevelPoints = 2000;
        } else if (level === 'Gold') {
            currentLevelMinimum = 2000;
            nextLevelPoints = 5000;
        } else {
            currentLevelMinimum = 5000;
            nextLevelPoints = 10000;
        }

        const range = nextLevelPoints - currentLevelMinimum;
        const current = Math.max(0, rewardBalance - currentLevelMinimum);

        let percentage = range > 0
            ? Math.min(100, (current / range) * 100)
            : 100;

        if (progressText) {
            if (level === 'Platinum') {
                progressText.textContent = `${formatPoints(rewardBalance)} points — Platinum`;
            } else {
                progressText.textContent =
                    `${formatPoints(rewardBalance)} / ${formatPoints(nextLevelPoints)} points to ${level === 'Gold' ? 'Platinum' : level === 'Silver' ? 'Gold' : level === 'Bronze' ? 'Silver' : 'Platinum'}`;
            }
        }

        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
        }

        const labels = $$('.ci-form-card-body .d-flex.justify-content-between span');

        labels.forEach(label => {
            label.style.color = '';
            label.style.fontWeight = '';

            const text = label.textContent.trim().replace('✓', '');

            if (text === level) {
                label.style.color = 'var(--gold)';
                label.style.fontWeight = '600';
                label.textContent = `${level} ✓`;
            }
        });

        const levelName = $('.ci-form-card-body .d-flex.align-items-center.justify-content-between span:first-child');

        if (levelName) {
            levelName.textContent = `${level} Member`;
        }
    }

    function updateRedeemButtons() {
        const redeemSection = $('.ci-chart-card');

        if (!redeemSection) {
            return;
        }

        const buttons = $$('button', redeemSection);

        const redemptionOptions = [
            500,
            1000,
            2000,
            rewardBalance
        ];

        buttons.forEach((button, index) => {
            const points = redemptionOptions[index];

            if (index === 3) {
                button.textContent = rewardBalance > 0
                    ? 'Redeem All'
                    : 'No Points';

                button.disabled = rewardBalance <= 0;

                return;
            }

            button.disabled = rewardBalance < points;

            button.textContent = rewardBalance >= points
                ? 'Redeem'
                : 'Not Enough Points';
        });
    }

    async function loadRewards() {
        try {
            const data = await apiRequest(API_URL);

            rewardBalance = Number(
                data.balance ??
                data.points ??
                0
            );

            rewardTransactions = Array.isArray(data.transactions)
                ? data.transactions
                : [];

            updateBalanceUI();

            renderTransactions();

        } catch (error) {
            console.error('Rewards load failed:', error);

            showMessage(
                error.message || 'Unable to load your rewards.',
                'error'
            );
        }
    }

    async function redeemPoints(points) {
        points = Number(points);

        if (!Number.isFinite(points) || points < 1) {
            return;
        }

        if (points > rewardBalance) {
            showMessage('You do not have enough reward points.', 'error');
            return;
        }

        const confirmed = window.confirm(
            `Redeem ${formatPoints(points)} points?`
        );

        if (!confirmed) {
            return;
        }

        const buttons = $$('.ci-chart-card button');
        buttons.forEach(button => {
            button.disabled = true;
        });

        try {
            const data = await apiRequest(API_URL, {
                method: 'POST',
                headers: {
                    'Idempotency-Key': generateIdempotencyKey()
                },
                body: JSON.stringify({
                    action: 'redeem',
                    points: points
                })
            });

            showMessage(
                data.message ||
                `${formatPoints(points)} points redeemed successfully.`,
                'success'
            );

            /*
             * Reload from the server instead of manually subtracting.
             * This keeps frontend state exactly equal to the database.
             */
            await loadRewards();

        } catch (error) {
            console.error('Reward redemption failed:', error);

            showMessage(
                error.message || 'Unable to redeem reward points.',
                'error'
            );

            updateRedeemButtons();
        }
    }

    function renderTransactions() {
        /*
         * Your current HTML doesn't contain a transaction-history section.
         * Create one automatically below the redeem section.
         */
        let section = $('#reward-transaction-history');

        if (!section) {
            section = document.createElement('div');
            section.id = 'reward-transaction-history';
            section.className = 'ci-chart-card mb-4';

            const redeemSection = $('.ci-chart-card');

            if (redeemSection && redeemSection.parentNode) {
                redeemSection.parentNode.insertBefore(
                    section,
                    redeemSection.nextSibling
                );
            } else {
                return;
            }
        }

        let rows = '';

        if (!rewardTransactions.length) {
            rows = `
                <div style="
                    padding:24px;
                    text-align:center;
                    color:var(--text-muted);
                ">
                    No reward transactions yet.
                </div>
            `;
        } else {
            rows = rewardTransactions.map(transaction => {
                const points = Number(transaction.points || 0);
                const isPositive = points > 0;

                const status = transaction.status || 'active';

                return `
                    <div style="
                        display:flex;
                        align-items:center;
                        justify-content:space-between;
                        gap:16px;
                        padding:14px 0;
                        border-bottom:1px solid var(--border-light);
                    ">
                        <div>
                            <div style="
                                font-weight:700;
                                color:var(--text-primary);
                            ">
                                ${escapeHtml(
                                    transaction.description ||
                                    transaction.entryType ||
                                    'Reward transaction'
                                )}
                            </div>

                            <div style="
                                margin-top:4px;
                                font-size:.8rem;
                                color:var(--text-muted);
                            ">
                                ${formatTransactionDate(transaction.createdAt)}
                            </div>
                        </div>

                        <div style="
                            text-align:right;
                            white-space:nowrap;
                        ">
                            <div style="
                                font-weight:800;
                                color:${isPositive ? 'var(--success)' : 'var(--saffron-dark)'};
                            ">
                                ${isPositive ? '+' : ''}${formatPoints(points)} pts
                            </div>

                            <div style="
                                margin-top:3px;
                                font-size:.75rem;
                                color:var(--text-muted);
                            ">
                                ${escapeHtml(status)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        section.innerHTML = `
            <div class="ci-chart-card-head">
                <div class="ci-chart-title">Reward Transaction History</div>
            </div>

            <div class="ci-chart-body">
                ${rows}
            </div>
        `;
    }

    function formatTransactionDate(value) {
        if (!value) {
            return '';
        }

        const date = new Date(
            String(value).replace(' ', 'T') + 'Z'
        );

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function showMessage(message, type = 'success') {
        let box = $('#reward-message');

        if (!box) {
            box = document.createElement('div');
            box.id = 'reward-message';

            const main = $('.ci-dash-main');

            if (main) {
                main.insertBefore(
                    box,
                    main.firstElementChild
                );
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

        window.clearTimeout(box._rewardTimer);

        box._rewardTimer = window.setTimeout(() => {
            box.remove();
        }, 4000);
    }

    function attachRedeemHandlers() {
        const redeemSection = $('.ci-chart-card');

        if (!redeemSection) {
            return;
        }

        const buttons = $$('button', redeemSection);

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => {
                if (index === 3) {
                    redeemPoints(rewardBalance);
                    return;
                }

                const points = [500, 1000, 2000][index];

                redeemPoints(points);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        attachRedeemHandlers();
        loadRewards();
    });

})();