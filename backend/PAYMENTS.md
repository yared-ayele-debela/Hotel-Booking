# Phase 8 — Payments (Stripe)

## Environment

Add to your `.env`:

```env
# Stripe (get keys from https://dashboard.stripe.com/apikeys)
STRIPE_KEY=pk_test_...          # Publishable key (optional; for frontend)
STRIPE_SECRET=sk_test_...       # Secret key (required for backend)
STRIPE_WEBHOOK_SECRET=whsec_... # Webhook signing secret (required for webhooks)
```

For local webhook testing use [Stripe CLI](https://stripe.com/docs/stripe-cli):  
`stripe listen --forward-to http://localhost:8000/api/webhooks/stripe`

## Flow

1. **Create booking** → `POST /api/v1/bookings` creates booking (status `pending_payment`) and returns `payment_intent` with `client_secret` and optional `stripe_publishable_key`.
2. **Frontend** uses Stripe.js / Elements to confirm the PaymentIntent with `client_secret`.
3. **Stripe** sends `payment_intent.succeeded` to **webhook** `POST /api/webhooks/stripe`.
4. **Webhook** verifies signature, enforces idempotency via `webhook_events` table, returns 200, dispatches `ProcessStripeWebhook` job.
5. **Job** finds payment by `external_id`, calls `PaymentService::confirmPayment` (sets payment + booking to confirmed), dispatches `PaymentConfirmed` event (for Phase 9 emails/audit).
6. **Cancel booking** → `POST /api/v1/bookings/{uuid}/cancel` releases inventory and refunds any completed payment via `PaymentService::refund`.

## Refunds

- Full or partial: `PaymentService::refund($payment, $amount, $reason, $idempotencyKey)`.
- Cancel flow refunds the full remaining amount for each completed payment.
- Commission/reporting uses `payments.refunded_amount` (see `CommissionService::vendorNetForBooking`).

## Queue

Webhook processing runs in a queued job. Ensure a worker is running, e.g.:

```bash
php artisan queue:work
```

With `QUEUE_CONNECTION=sync`, the job runs in the same request (not recommended for production).
