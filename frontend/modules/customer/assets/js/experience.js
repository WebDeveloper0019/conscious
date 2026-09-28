(function () {
    'use strict';
    var toastWrap = document.getElementById('cc-toast-wrap');
    var experienceConfig = window.ConsciousIndiExperienceConfig || { mode: 'demo', apiBaseUrl: '', endpoints: {} };
    function toast(message, type) { var item = document.createElement('div'); item.className = 'cc-toast ' + (type || ''); item.textContent = message; if (toastWrap) toastWrap.appendChild(item); window.setTimeout(function () { item.remove(); }, 3600); }

    if (window.gsap && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.gsap.from('.cc-page-head > *', { y: 9, opacity: 0, duration: .45, stagger: .08, ease: 'power2.out' });
        window.gsap.from('[data-reveal]', { y: 17, opacity: 0, duration: .55, stagger: .07, ease: 'power2.out', clearProps: 'transform' });
    }

    document.querySelectorAll('.cx-product-media button,.ce-event-image>button,[data-save-product]').forEach(function (button) {
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', function (event) {
            event.preventDefault();
            var saved = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', saved ? 'true' : 'false');
            button.querySelector('i').className = saved ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
            toast(saved ? 'Saved to your wishlist.' : 'Removed from your wishlist.', 'success');
        });
    });

    var eventGrid = document.querySelector('[data-event-grid]');
    if (!eventGrid) return;
    var cards = Array.from(eventGrid.querySelectorAll('[data-event-card]'));
    var activeType = 'all';
    var city = 'all';
    var query = '';

    function filterEvents() {
        var visible = 0;
        cards.forEach(function (card) {
            var matchesType = activeType === 'all' || card.dataset.type === activeType;
            var matchesCity = city === 'all' || card.dataset.city.toLowerCase() === city.toLowerCase();
            var matchesQuery = !query || card.textContent.toLowerCase().includes(query);
            card.hidden = !(matchesType && matchesCity && matchesQuery);
            if (!card.hidden) visible += 1;
        });
        var count = document.querySelector('[data-event-count]');
        if (count) count.textContent = visible + (visible === 1 ? ' experience' : ' experiences');
        var empty = document.querySelector('[data-event-empty]');
        if (empty) empty.hidden = visible !== 0;
    }

    document.querySelectorAll('[data-event-type]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('[data-event-type]').forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active'); activeType = button.dataset.eventType; filterEvents();
        });
    });

    document.querySelector('[data-event-filter]')?.addEventListener('submit', function (event) {
        event.preventDefault();
        var data = new FormData(event.currentTarget);
        city = String(data.get('city') || 'all');
        query = String(data.get('event_search') || '').trim().toLowerCase();
        filterEvents();
        eventGrid.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
    });

    var dialog = document.getElementById('event-booking-dialog');
    var bookingForm = dialog?.querySelector('[data-event-booking-form]');
    var basePrice = 0;
    function formatMoney(value) { return value === 0 ? 'Free' : '₹' + new Intl.NumberFormat('en-IN').format(value); }
    function updatePrice() {
        if (!bookingForm) return;
        var quantity = Number(bookingForm.elements.quantity.value || 1);
        var kitAddition = bookingForm.elements.ticket_type.value === 'kit' ? 300 : 0;
        var total = (basePrice + kitAddition) * quantity;
        dialog.querySelector('[data-price-quantity]').textContent = quantity;
        dialog.querySelector('[data-ticket-subtotal]').textContent = formatMoney(total);
        dialog.querySelector('[data-booking-total]').textContent = formatMoney(total);
        dialog.dataset.bookingTotal = String(total);
    }

    document.querySelectorAll('[data-book-event]').forEach(function (button) {
        button.addEventListener('click', function () {
            basePrice = Number(button.dataset.eventPrice || 0);
            dialog.dataset.eventTitle = button.dataset.bookEvent;
            dialog.querySelector('[data-booking-title]').textContent = button.dataset.bookEvent;
            dialog.querySelector('[data-summary-title]').textContent = button.dataset.bookEvent;
            dialog.querySelector('[data-summary-date]').textContent = button.dataset.eventDate;
            dialog.querySelector('[data-summary-city]').textContent = button.dataset.eventCity;
            bookingForm.hidden = false;
            dialog.querySelector('[data-booking-confirmation]').hidden = true;
            bookingForm.reset();
            bookingForm.elements.attendee_name.value = 'Rahul Sharma';
            bookingForm.elements.phone.value = '9876543210';
            bookingForm.elements.quantity.value = '2';
            updatePrice();
            dialog.showModal();
        });
    });

    bookingForm?.elements.quantity.addEventListener('change', updatePrice);
    bookingForm?.elements.ticket_type.addEventListener('change', updatePrice);
    bookingForm?.elements.phone.addEventListener('input', function () { bookingForm.elements.phone.value = bookingForm.elements.phone.value.replace(/\D/g, '').slice(0, 10); });
    bookingForm?.addEventListener('submit', async function (event) {
        event.preventDefault();
        var phone = bookingForm.elements.phone;
        phone.setCustomValidity(/^[6-9][0-9]{9}$/.test(phone.value) ? '' : 'Enter a valid 10-digit Indian mobile number.');
        if (!bookingForm.checkValidity()) { bookingForm.reportValidity(); return; }
        var submit = bookingForm.querySelector('[data-booking-submit]');
        submit.disabled = true;
        var payload = { event_title: dialog.dataset.eventTitle, ticket_type: bookingForm.elements.ticket_type.value, quantity: Number(bookingForm.elements.quantity.value), attendee_name: bookingForm.elements.attendee_name.value.trim(), phone: phone.value, accessibility_request: bookingForm.elements.accessibility_request.value.trim(), booking_policy: bookingForm.elements.booking_policy.checked };
        var safeBooking;
        try {
            if (experienceConfig.mode === 'api') {
                var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                var response = await fetch((experienceConfig.apiBaseUrl || '') + experienceConfig.endpoints.eventBooking, { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify(payload) });
                var data = await response.json().catch(function () { return {}; });
                if (!response.ok) throw new Error(data.message || 'The booking could not be completed.');
                safeBooking = data.booking;
            } else {
                safeBooking = { reference: 'EV-DEMO-2026', title: dialog.dataset.eventTitle, quantity: payload.quantity, total: Number(dialog.dataset.bookingTotal), status: 'confirmed' };
                window.sessionStorage.setItem('ci_demo_event_booking', JSON.stringify(safeBooking));
            }
        } catch (error) { toast(error.message || 'The booking could not be completed.', 'error'); submit.disabled = false; return; }
        dialog.querySelector('[data-booking-reference]').textContent = safeBooking.reference;
        bookingForm.hidden = true;
        var confirmation = dialog.querySelector('[data-booking-confirmation]');
        confirmation.hidden = false;
        confirmation.focus();
        submit.disabled = false;
        if (window.gsap) window.gsap.from('[data-booking-confirmation] > *', { y: 12, opacity: 0, duration: .4, stagger: .07, ease: 'power2.out' });
    });
})();
