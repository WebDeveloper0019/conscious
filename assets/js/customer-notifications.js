(function () {
    'use strict';

    const API_URL = '../api/notifications.php';

    let csrfToken = '';

    const pageSub = document.querySelector('.ci-dash-page-sub');
    const notificationContainer = document.querySelector('.ci-data-table-card > div:nth-child(2)');
    const markAllButton = document.querySelector('.ci-data-table-head .bc-btn');

    async function getCsrfToken() {
        try {
            const response = await fetch('../api/auth/me.php', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            csrfToken =
                data.csrf_token ||
                data.csrfToken ||
                data.user?.csrf_token ||
                data.user?.csrfToken ||
                '';

            return csrfToken;
        } catch (error) {
            console.error('Unable to get CSRF token:', error);
            return '';
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNotificationTime(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return escapeHtml(value);
        }

        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMinutes = Math.floor(diffMs / 60000);

        if (diffMinutes < 1) {
            return 'Just now';
        }

        if (diffMinutes < 60) {
            return diffMinutes + ' minute' + (diffMinutes === 1 ? '' : 's') + ' ago';
        }

        const diffHours = Math.floor(diffMinutes / 60);

        if (diffHours < 24) {
            return diffHours + ' hour' + (diffHours === 1 ? '' : 's') + ' ago';
        }

        const diffDays = Math.floor(diffHours / 24);

        if (diffDays < 7) {
            return diffDays + ' day' + (diffDays === 1 ? '' : 's') + ' ago';
        }

        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function getIcon(type) {
        const typeValue = String(type || '').toLowerCase();

        if (
            typeValue.includes('order') ||
            typeValue.includes('delivery') ||
            typeValue.includes('shipping')
        ) {
            return {
                icon: 'fa-truck',
                className: 'saffron'
            };
        }

        if (
            typeValue.includes('offer') ||
            typeValue.includes('coupon') ||
            typeValue.includes('sale')
        ) {
            return {
                icon: 'fa-tag',
                className: 'gold'
            };
        }

        if (
            typeValue.includes('success') ||
            typeValue.includes('complete') ||
            typeValue.includes('delivered')
        ) {
            return {
                icon: 'fa-circle-check',
                className: 'navy'
            };
        }

        return {
            icon: 'fa-bell',
            className: 'saffron'
        };
    }

    function getNotificationId(notification) {
        return (
            notification.id ||
            notification.notificationId ||
            notification.notification_id ||
            ''
        );
    }

    function isRead(notification) {
        return (
            notification.is_read === true ||
            notification.isRead === true ||
            notification.read === true ||
            Number(notification.is_read) === 1 ||
            Number(notification.isRead) === 1 ||
            Number(notification.read) === 1
        );
    }

    function getNotificationsFromResponse(data) {
        if (Array.isArray(data)) {
            return data;
        }

        if (Array.isArray(data.notifications)) {
            return data.notifications;
        }

        if (Array.isArray(data.data)) {
            return data.data;
        }

        return [];
    }

    function updateUnreadCount(notifications) {
        const unread = notifications.filter(function (notification) {
            return !isRead(notification);
        }).length;

        if (pageSub) {
            pageSub.textContent =
                unread === 0
                    ? 'No unread notifications.'
                    : unread + ' unread notification' + (unread === 1 ? '.' : 's.');
        }

        const notificationNavItem = document.querySelector(
            'a[href="notifications.html"]'
        );

        if (notificationNavItem) {
            const badge = notificationNavItem.querySelector(
                '.ci-sidebar-nav-badge'
            );

            if (badge) {
                if (unread > 0) {
                    badge.textContent = unread;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    }

    function renderNotifications(notifications) {
        if (!notificationContainer) {
            return;
        }

        if (!notifications.length) {
            notificationContainer.innerHTML = `
                <div style="padding:40px 20px;text-align:center;color:var(--text-muted);">
                    <div style="font-size:2rem;margin-bottom:10px;">
                        <i class="fa-regular fa-bell-slash"></i>
                    </div>
                    <div style="font-weight:600;color:var(--text-primary);">
                        No notifications
                    </div>
                    <div style="font-size:.875rem;margin-top:4px;">
                        You're all caught up.
                    </div>
                </div>
            `;
            return;
        }

        notificationContainer.innerHTML = notifications.map(function (notification) {
            const id = getNotificationId(notification);
            const read = isRead(notification);
            const icon = getIcon(notification.type);

            const title = notification.title || 'Notification';
            const message = notification.message || '';
            const createdAt =
                notification.created_at ||
                notification.createdAt ||
                notification.created ||
                '';

            const unreadStyle = read
                ? ''
                : `
                    background:var(--saffron-pale);
                    border-left:3px solid var(--saffron);
                `;

            const unreadDot = read
                ? ''
                : `
                    <span
                        style="width:8px;height:8px;background:var(--saffron);border-radius:50%;flex-shrink:0;margin-top:4px;"
                        aria-label="Unread"
                    ></span>
                `;

            return `
                <div
                    class="customer-notification-row"
                    data-notification-id="${escapeHtml(id)}"
                    style="
                        display:flex;
                        gap:14px;
                        align-items:flex-start;
                        padding:16px 20px;
                        border-bottom:1px solid var(--border-light);
                        ${unreadStyle}
                    "
                >
                    <div class="ci-activity-icon ${icon.className}">
                        <i class="fa-solid ${icon.icon}"></i>
                    </div>

                    <div style="flex:1;">
                        <div style="
                            font-size:.9375rem;
                            font-weight:600;
                            color:var(--text-primary);
                        ">
                            ${escapeHtml(title)}
                        </div>

                        <div style="
                            font-size:.875rem;
                            color:var(--text-secondary);
                            margin:3px 0;
                        ">
                            ${escapeHtml(message)}
                        </div>

                        <div style="
                            font-size:.75rem;
                            color:var(--text-muted);
                        ">
                            ${formatNotificationTime(createdAt)}
                        </div>

                        ${
                            !read && id
                                ? `
                                    <button
                                        type="button"
                                        class="notification-read-btn"
                                        data-notification-id="${escapeHtml(id)}"
                                        style="
                                            border:0;
                                            background:none;
                                            padding:4px 0;
                                            margin-top:6px;
                                            color:var(--saffron);
                                            font-size:.75rem;
                                            font-weight:600;
                                            cursor:pointer;
                                        "
                                    >
                                        Mark as read
                                    </button>
                                `
                                : ''
                        }
                    </div>

                    ${unreadDot}
                </div>
            `;
        }).join('');
    }

    async function loadNotifications() {
        try {
            const response = await fetch(API_URL, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    data.error ||
                    'Unable to load notifications.'
                );
            }

            const notifications = getNotificationsFromResponse(data);

            updateUnreadCount(notifications);
            renderNotifications(notifications);

        } catch (error) {
            console.error('Notification loading failed:', error);

            if (notificationContainer) {
                notificationContainer.innerHTML = `
                    <div style="padding:30px 20px;text-align:center;color:#b42318;">
                        Unable to load notifications.
                        Please refresh the page.
                    </div>
                `;
            }
        }
    }

    async function markAsRead(notificationId) {
        if (!notificationId) {
            return;
        }

        if (!csrfToken) {
            await getCsrfToken();
        }

        if (!csrfToken) {
            alert('Security token is missing. Please refresh the page and try again.');
            return;
        }

        try {
            const response = await fetch(API_URL, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    id: notificationId
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    data.error ||
                    'Unable to mark notification as read.'
                );
            }

            await loadNotifications();

        } catch (error) {
            console.error('Mark notification as read failed:', error);
            alert(error.message || 'Unable to mark notification as read.');
        }
    }

    async function markAllAsRead() {
        if (!csrfToken) {
            await getCsrfToken();
        }

        if (!csrfToken) {
            alert('Security token is missing. Please refresh the page and try again.');
            return;
        }

        if (markAllButton) {
            markAllButton.disabled = true;
            markAllButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
        }

        try {
            const response = await fetch(API_URL, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    id: 'all'
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    data.error ||
                    'Unable to mark all notifications as read.'
                );
            }

            await loadNotifications();

        } catch (error) {
            console.error('Mark all notifications as read failed:', error);
            alert(error.message || 'Unable to mark all notifications as read.');
        } finally {
            if (markAllButton) {
                markAllButton.disabled = false;
                markAllButton.textContent = 'Mark All Read';
            }
        }
    }

    document.addEventListener('click', function (event) {
        const readButton = event.target.closest('.notification-read-btn');

        if (readButton) {
            event.preventDefault();

            const notificationId =
                readButton.getAttribute('data-notification-id');

            markAsRead(notificationId);
        }
    });

    if (markAllButton) {
        markAllButton.addEventListener('click', function (event) {
            event.preventDefault();
            markAllAsRead();
        });
    }

    async function init() {
        await getCsrfToken();
        await loadNotifications();
    }

    init();
})();