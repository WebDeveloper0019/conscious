<div data-tabs>
  <!-- TABS HEADER -->
  <section class="cp-tabs" role="tablist" aria-label="Communication sections">
    <button class="active" type="button" data-tab-target="messages"><i class="fa-regular fa-message"></i> Messages <span id="msg-count-badge" style="display:none;">0</span></button>
    <button type="button" data-tab-target="notifications"><i class="fa-regular fa-bell"></i> Notifications <span id="notif-count-badge" style="display:none;">0</span></button>
    <button type="button" data-tab-target="reviews"><i class="fa-regular fa-star"></i> Reviews</button>
  </section>

  <!-- MESSAGES TAB -->
  <section class="cp-tab-panel active" data-tab-panel="messages">
    <div class="cp-inbox">
      <aside class="cp-thread-list">
        <div class="cp-inbox-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" placeholder="Search messages">
        </div>
        <!-- Real messages will be injected here -->
        <div id="customer-message-threads">
            <p style="padding: 20px; text-align: center; color: #666;">No messages yet.</p>
        </div>
      </aside>
      <div class="cp-conversation">
        <div class="cp-message-stream" id="customer-conversation-stream">
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#666;">
                <i class="fa-regular fa-message" style="font-size:3rem; margin-bottom:10px; color:#ccc;"></i>
                <p>Select a message to view the conversation.</p>
            </div>
        </div>
        <form class="cp-message-compose" data-customer-form="message">
          <button type="button" aria-label="Attach file"><i class="fa-solid fa-paperclip"></i></button>
          <input type="text" placeholder="Write a message" maxlength="500" required>
          <button class="cc-btn primary" type="submit" aria-label="Send message"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <p class="cp-safety-line"><i class="fa-solid fa-shield-halved"></i> Keep payments and personal details inside Conscious India.</p>
      </div>
    </div>
  </section>

  <!-- NOTIFICATIONS TAB -->
  <section class="cp-tab-panel" data-tab-panel="notifications" id="notifications" hidden>
    <div class="cp-panel">
      <header class="cp-panel-head">
        <div><span class="cx-kicker">Updates</span><h2>Recent notifications</h2></div>
        <button class="cc-btn" type="button" data-demo-action="Mark all notifications read">Mark all as read</button>
      </header>
      <div class="cp-notification-list" id="customer-notification-list">
        <!-- Real notifications will be injected here by dashboard-live.js -->
        <p style="padding: 20px; text-align: center; color: #666;">No new notifications.</p>
      </div>
    </div>
  </section>

  <!-- REVIEWS TAB -->
  <section class="cp-tab-panel" data-tab-panel="reviews" id="reviews" hidden>
    <div class="cp-panel">
      <header class="cp-panel-head">
        <div><span class="cx-kicker">Verified purchases</span><h2>Share your experience</h2></div>
        <span class="cc-status info" id="reviews-badge" style="display:none;">0 awaiting review</span>
      </header>
      <div id="customer-reviews-list">
        <!-- Real review tasks will be injected here -->
        <p style="padding: 20px; text-align: center; color: #666;">You have no pending reviews.</p>
      </div>
    </div>
  </section>

  <!-- REVIEW DIALOG (Keep this, it's functional) -->
  <dialog class="cc-dialog" id="review-dialog">
    <div class="cc-dialog-head">
      <h2>Review your purchase</h2>
      <button class="cc-btn small" type="button" data-close-dialog aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form class="cc-card-body" data-customer-form="review">
      <fieldset class="cp-rating">
        <legend>Overall rating</legend>
        <?php for($i=1;$i<=5;$i++): ?>
          <label><input type="radio" name="rating" value="<?=$i?>" required><i class="fa-regular fa-star"></i><span><?=$i?> star</span></label>
        <?php endfor; ?>
      </fieldset>
      <div class="cc-field mt-3">
        <label for="review-title">Review title</label>
        <input class="cc-input" id="review-title" maxlength="80" required>
      </div>
      <div class="cc-field mt-3">
        <label for="review-copy">Your review</label>
        <textarea class="cc-textarea" id="review-copy" maxlength="1000" required></textarea>
      </div>
      <div class="cc-form-actions">
        <button class="cc-btn primary" type="submit">Submit review</button>
      </div>
    </form>
  </dialog>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch the real notifications from your working API
    fetch('/conscious/api/user/notifications.php')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('customer-notification-list');
            const badge = document.getElementById('notif-count-badge');

            if (data.success && data.notifications.length > 0) {
                // Update the red notification badge count in the tabs
                if (data.unreadCount > 0) {
                    badge.style.display = 'inline-block';
                    badge.textContent = data.unreadCount;
                }

                let html = '';
                // Loop through the real database data
                data.notifications.forEach(notif => {
                    // Pick an icon based on the notification type
                    let icon = 'fa-bell';
                    if (notif.type === 'data_export_ready') icon = 'fa-file-export';
                    else if (notif.type.includes('order')) icon = 'fa-truck-fast';

                    // Format the date nicely (e.g., Oct 15, 2026)
                    const date = new Date(notif.createdAt).toLocaleDateString('en-IN', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });

                    // Build the HTML for the notification
                    html += `
                        <article class="${notif.isRead ? '' : 'unread'}">
                            <span><i class="fa-solid ${icon}"></i></span>
                            <div>
                                <b>${notif.title}</b>
                                <p>${notif.message}</p>
                                <small>${date}</small>
                            </div>
${notif.actionUrl ? `<a href="/conscious/customer/profile.html">View</a>` : ''}                    `;
                });
                
                // Inject it into the page!
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p style="padding: 20px; text-align: center; color: #666;">No new notifications.</p>';
            }
        })
        .catch(err => {
            console.error('Failed to load notifications:', err);
            document.getElementById('customer-notification-list').innerHTML = '<p style="padding: 20px; text-align: center; color: red;">Failed to load notifications.</p>';
        });
});
</script>