<section class="cp-list-head" data-live-orders>
  <div><span class="cx-kicker">Purchase history</span><h2>Your orders</h2><p>Each vendor arranges delivery and enters package updates manually.</p></div>
  <form class="cp-search-control" data-order-search><label class="cc-sr-only" for="customer-order-query">Order number</label><input id="customer-order-query" type="search" placeholder="Search order number" maxlength="100"><button class="cc-btn" type="submit">Search</button></form>
</section>
<div class="cp-order-list" data-order-list aria-live="polite"><p>Loading your orders…</p></div>
<div class="cp-order-more"><button class="cc-btn" data-order-more type="button" hidden>Load more orders</button></div>
<section class="cp-order-detail" data-order-detail hidden aria-live="polite"></section>
<dialog class="cc-dialog" data-order-cancel-dialog>
  <div class="cc-dialog-head"><h2>Cancel eligible order</h2><button class="cc-btn small" type="button" data-cancel-close aria-label="Close cancellation form"><i class="fa-solid fa-xmark"></i></button></div>
  <form class="cc-card-body" data-cancel-form>
    <p data-cancel-info></p>
    <label class="cc-field">Reason <select class="cc-select" name="reason" required><option value="">Choose a reason</option><option value="Ordered by mistake">Ordered by mistake</option><option value="Address issue">Address issue</option><option value="Delivery estimate unsuitable">Delivery estimate unsuitable</option><option value="Duplicate order">Duplicate order</option><option value="Other">Other</option></select></label>
    <label class="cc-field">Additional details (optional) <textarea class="cc-textarea" name="note" maxlength="400"></textarea></label>
    <label class="cc-check"><input type="checkbox" name="confirm" required> I understand cancellation may create a refund for an already-paid order.</label>
    <button class="cc-btn danger" type="submit">Confirm cancellation</button>
    <p role="status" data-cancel-status></p>
  </form>
</dialog>
