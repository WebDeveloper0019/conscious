# Vendor frontend status matrix

The frontend must display only actions allowed by the current state. The future backend is authoritative and must reject invalid transitions.

## Vendor onboarding

Draft → Submitted → Under review → Approved / Changes required / Rejected → Resubmitted. Approved accounts may later become Restricted, Suspended, Deactivated, or Reactivated by authorized platform workflows.

## Product

Draft → Submitted → Under review → Approved / Changes required / Rejected. Approved products may become Active, Paused, Out of stock, Suspended, Archived, or Restored. Material edits create a new review version without silently replacing the approved version.

## Fulfilment

Awaiting acceptance → Accepted / Rejected. Accepted → Preparing → Quality checked → Packed → Ready for dispatch. Partial item acceptance and partial packing must be represented at item/package level.

## Manual delivery

Ready for dispatch → Dispatched → Picked up → In transit → Reached destination → Out for delivery → Delivered. Exception branches: Delivery attempted → Re-delivery scheduled → Out for delivery; Delivery failed / Customer refused → Returning to vendor → Returned to vendor. Every transition needs vendor user, timestamp, location, note, and evidence where required.

## Cancellation

Requested → Vendor review → Approved / Rejected / More information required → Cancelled / Closed. After dispatch, cancellation is redirected to refusal, support, or return policy.

## Return

Requested → Vendor review → Approved / Rejected / More information required → Pickup scheduled / Customer self-shipping → Collected → In transit → Received → Inspection → Accepted / Disputed → Refund recommended / Replacement created → Closed.

## Replacement

Requested → Approved → Original return pending → Original received → Preparing → Packed → Dispatched → Delivered → Completed.

## Refund

Not applicable → Recommended by vendor → Admin/payment review → Initiated → Processing → Completed / Failed / Partially completed / Rejected. Vendor frontend never claims to execute the payment.

## Settlement and payout

Pending delivery → Return window active → Eligible → Scheduled / Requested → Processing → Paid / Failed / On hold / Reversed.

## Event application

Draft → Submitted → Under review → More information required / Approved / Rejected → Payment pending → Confirmed → Cancelled / Completed.
