<section class="ce-hero" data-reveal>
    <div><span class="cx-kicker">Meet the hands behind the craft</span><h2>Experiences that stay with you.</h2><p>Book workshops, exhibitions, craft walks and cultural gatherings hosted by verified makers and partners.</p><form class="ce-search" data-event-filter><div><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="event_search" placeholder="Search events or crafts" aria-label="Search events"></div><select name="city" aria-label="Filter by city"><option value="all">All India</option><option>Jaipur</option><option>Delhi</option><option>Kolkata</option><option>Mumbai</option><option>Bengaluru</option><option>Online</option></select><select name="date" aria-label="Filter by date"><option value="all">Any date</option><option>This week</option><option>This month</option><option>Next month</option></select><button class="cc-btn primary" type="submit">Find experiences</button></form></div>
    <div class="ce-hero-art" aria-hidden="true"><span><i class="fa-solid fa-palette"></i></span><b>50+</b><small>maker-led experiences</small></div>
</section>

<section class="ce-type-strip" aria-label="Event types" data-reveal><button class="active" type="button" data-event-type="all"><i class="fa-solid fa-shapes"></i><span>All events</span></button><button type="button" data-event-type="workshop"><i class="fa-solid fa-hands"></i><span>Workshops</span></button><button type="button" data-event-type="exhibition"><i class="fa-regular fa-images"></i><span>Exhibitions</span></button><button type="button" data-event-type="walk"><i class="fa-solid fa-person-walking"></i><span>Craft walks</span></button><button type="button" data-event-type="online"><i class="fa-solid fa-laptop"></i><span>Online</span></button></section>

<section class="cx-section mt-3" data-reveal>
    <div class="cx-section-head">
        <div><span class="cx-kicker">Featured this month</span><h2>Learn directly from master makers</h2></div>
        <span class="ce-result-count" data-event-count>0 experiences</span>
    </div>
    
    <!-- REAL EVENTS WILL BE INJECTED HERE -->
    <div class="ce-event-grid" data-event-grid id="real-events-grid">
        <p style="padding: 20px; text-align: center; color: #666; grid-column: 1 / -1;">Loading experiences...</p>
    </div>
    <div class="ce-empty" hidden data-event-empty><i class="fa-regular fa-calendar-xmark"></i><h3>No matching experiences</h3><p>Try a different city, date or event type.</p></div>
</section>

<section class="ce-bookings mt-3" id="bookings" data-reveal>
    <div class="cx-section-head">
        <div><span class="cx-kicker">Your bookings</span><h2>Upcoming experiences</h2></div>
        <a href="#past-events">Past events</a>
    </div>
    
    <!-- REAL BOOKINGS WILL BE INJECTED HERE -->
    <div id="real-bookings-list">
        <p style="padding: 20px; color: #666;">Loading your bookings...</p>
    </div>
</section>

<dialog class="cc-dialog ce-book-dialog" id="event-booking-dialog">
    <div class="cc-dialog-head"><div><span class="cx-kicker">Complete your booking</span><h2 data-booking-title>Event booking</h2></div><button type="button" class="cc-btn small" data-close-dialog aria-label="Close booking"><i class="fa-solid fa-xmark"></i></button></div>
    <form data-event-booking-form>
        <div class="ce-book-layout">
            <section>
                <div class="ce-book-summary">
                    <div class="ce-summary-art"><i class="fa-solid fa-palette"></i></div>
                    <div><h3 data-summary-title>Selected event</h3><p><i class="fa-regular fa-calendar"></i> <span data-summary-date></span></p><p><i class="fa-solid fa-location-dot"></i> <span data-summary-city></span></p><button type="button" data-demo-action="Open full event details">View inclusions, host and policy</button></div>
                </div>
                <div class="cc-form-grid">
                    <div class="cc-field"><label for="booking-name">Lead attendee *</label><input class="cc-input" id="booking-name" name="attendee_name" required maxlength="100" autocomplete="name"></div>
                    <div class="cc-field"><label for="booking-phone">Mobile number *</label><input class="cc-input" id="booking-phone" name="phone" required maxlength="10" inputmode="numeric" data-indian-phone></div>
                    <div class="cc-field"><label for="ticket-type">Ticket type *</label><select class="cc-select" id="ticket-type" name="ticket_type" required><option value="standard">Standard admission</option><option value="kit">Admission + material kit</option></select></div>
                    <div class="cc-field"><label for="ticket-quantity">Attendees *</label><select class="cc-select" id="ticket-quantity" name="quantity" required data-ticket-quantity><option value="1">1 attendee</option><option value="2" selected>2 attendees</option><option value="3">3 attendees</option><option value="4">4 attendees</option></select></div>
                    <div class="cc-field full"><label for="booking-accessibility">Accessibility, dietary or other request</label><textarea class="cc-textarea" id="booking-accessibility" name="accessibility_request" maxlength="500" placeholder="Optional—tell the host how they can support you"></textarea></div>
                </div>
                <label class="cc-check ce-policy"><input type="checkbox" name="booking_policy" required> I agree to the event cancellation, conduct and photography policy.</label>
            </section>
            <aside class="ce-price-box">
                <h3>Price details</h3>
                <p><span>Tickets × <b data-price-quantity>2</b></span><strong data-ticket-subtotal>₹0</strong></p>
                <p><span>Booking fee</span><strong>₹0</strong></p>
                <p><span>Taxes</span><strong>Included</strong></p>
                <div><span>Total</span><strong data-booking-total>₹0</strong></div>
                <small><i class="fa-solid fa-shield-halved"></i> Secure checkout.</small>
                <button class="cc-btn primary" type="submit" data-booking-submit><span>Confirm booking</span><i class="fa-solid fa-arrow-right"></i></button>
            </aside>
        </div>
    </form>
    <section class="ce-confirmation" hidden data-booking-confirmation tabindex="-1">
        <span><i class="fa-solid fa-check"></i></span><h2>Booking confirmed</h2><p>Your ticket is ready.</p>
        <div><small>Booking reference</small><strong data-booking-reference>EV-XXXX-2026</strong></div>
        <div class="cc-actions"><button class="cc-btn" type="button" data-demo-action="Download event ticket">Download ticket</button><button class="cc-btn primary" type="button" data-close-dialog>Done</button></div>
    </section>
</dialog>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Fetch and render REAL EVENTS
    fetch('/conscious/api/user/events.php')
        .then(res => res.json())
        .then(data => {
            const grid = document.getElementById('real-events-grid');
            const countBadge = document.querySelector('[data-event-count]');
            const events = data.events || [];

            countBadge.textContent = `${events.length} experiences`;

            if (events.length > 0) {
                let html = '';
                events.forEach(event => {
                    const date = new Date(event.starts_at);
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = date.toLocaleString('en-IN', { month: 'short' }).toUpperCase();
                    
                    const isFree = event.ticket_price == 0 || event.price == 0;
                    const priceDisplay = isFree ? 'Free' : `₹${event.ticket_price || event.price}`;
                    const btnText = isFree ? 'Register' : 'View & book';

                    html += `
                        <article class="ce-event-card" data-event-card data-type="${event.event_type || 'workshop'}" data-city="${event.venue_name || 'Online'}">
                            <div class="ce-event-image pottery">
                                ${isFree ? '<span class="ce-badge free">Free entry</span>' : '<span class="ce-badge">Bestseller</span>'}
                                <button type="button" aria-label="Save Event"><i class="fa-regular fa-heart"></i></button>
                                <div class="ce-date"><b>${day}</b><small>${month}</small></div>
                            </div>
                            <div class="ce-event-info">
                                <p><span>${event.event_type || 'Event'}</span><span><i class="fa-solid fa-location-dot"></i> ${event.venue_name || 'Online'}</span></p>
                                <h3>${event.title}</h3>
                                <small>${event.short_description || 'Join this amazing experience.'}</small>
                                <div class="ce-event-meta">
                                    <span><i class="fa-regular fa-clock"></i> 2 hours</span>
                                    <span><i class="fa-solid fa-users"></i> Limited seats</span>
                                </div>
                                <footer>
                                    <div><strong>${priceDisplay}</strong><small>per person</small></div>
                                    <button class="cc-btn primary" type="button" data-book-event="${event.title}" data-event-price="${event.ticket_price || event.price || 0}" data-event-date="${day} ${month} · 11:00 AM" data-event-city="${event.venue_name || 'Online'}">${btnText}</button>
                                </footer>
                            </div>
                        </article>
                    `;
                });
                grid.innerHTML = html;
            } else {
                grid.innerHTML = '<p style="padding: 20px; text-align: center; color: #666; grid-column: 1 / -1;">No upcoming experiences found.</p>';
            }
        })
        .catch(err => console.error('Error loading events:', err));

    // 2. Fetch and render REAL BOOKINGS
    fetch('/conscious/api/user/dashboard.php') // Using dashboard API which returns upcomingBookings
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('real-bookings-list');
            const bookings = data.upcomingBookings || [];

            if (bookings.length > 0) {
                let html = '';
                bookings.forEach(b => {
                    const date = new Date(b.starts_at);
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = date.toLocaleString('en-IN', { month: 'short' }).toUpperCase();
                    const year = date.getFullYear();
                    
                    html += `
                        <article style="margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; padding: 15px; display: flex; gap: 15px; align-items: center;">
                            <div class="ce-booking-date" style="text-align: center; background: #f8f9fa; padding: 10px 15px; border-radius: 8px;">
                                <b style="font-size: 1.5rem; display: block; color: #A63D24;">${day}</b>
                                <span style="font-size: 0.8rem; color: #666;">${month}<br>${year}</span>
                            </div>
                            <div class="ce-booking-main" style="flex: 1;">
                                <span class="cc-status success" style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">${b.status}</span>
                                <h3 style="margin: 5px 0; font-size: 1.1rem;">${b.title}</h3>
                                <p style="margin: 0; color: #555; font-size: 0.9rem;">${b.venue_name || 'Online'} · ${b.quantity} attendees</p>
                                <small style="color: #888;">Booking ID: ${b.booking_number}</small>
                            </div>
                            <div class="ce-booking-actions">
                                <button class="cc-btn" type="button" data-demo-action="Download event ticket">Ticket</button>
                            </div>
                        </article>
                    `;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<p style="padding: 20px; color: #666;">You have no upcoming bookings.</p>';
            }
        })
        .catch(err => console.error('Error loading bookings:', err));
});
</script>