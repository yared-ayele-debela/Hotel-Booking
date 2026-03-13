<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentService
{
    protected ?StripeClient $stripe = null;

    public function __construct() {}

    protected function stripe(): StripeClient
    {
        if ($this->stripe === null) {
            $secret = config('services.stripe.secret');
            if (empty($secret)) {
                throw new \RuntimeException('Stripe secret key is not configured. Set STRIPE_SECRET in .env.');
            }
            $this->stripe = new StripeClient($secret);
        }
        return $this->stripe;
    }

    /**
     * Create a payment record and Stripe PaymentIntent; return client payload for frontend.
     * Amount is in booking currency; Stripe expects amount in smallest unit (cents).
     *
     * @return array{client_secret: string, payment_id: int, stripe_publishable_key?: string}
     */
    public function initiatePayment(Booking $booking): array
    {
        $amount = (float) $booking->total_price;
        $currency = strtolower($booking->currency ?? 'usd');

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'currency' => $currency,
            'provider' => 'stripe',
            'external_id' => null,
            'status' => PaymentStatus::PENDING->value,
            'payload' => null,
        ]);

        $amountInCents = (int) round($amount * 100);

        try {
            $intent = $this->stripe()->paymentIntents->create([
                'amount' => max(50, $amountInCents), // Stripe minimum
                'currency' => $currency,
                'metadata' => [
                    'booking_uuid' => $booking->uuid,
                    'payment_id' => (string) $payment->id,
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        } catch (ApiErrorException $e) {
            $payment->update(['status' => PaymentStatus::FAILED->value, 'payload' => ['error' => $e->getMessage()]]);
            throw new \RuntimeException('Failed to create payment intent: '.$e->getMessage(), 0, $e);
        }

        $payment->update(['external_id' => $intent->id]);

        $result = [
            'client_secret' => $intent->client_secret,
            'payment_id' => $payment->id,
        ];
        if (config('services.stripe.key')) {
            $result['stripe_publishable_key'] = config('services.stripe.key');
        }
        return $result;
    }

    /**
     * Create Stripe Checkout Session for a booking using laravel-smart-stripe.
     * Returns checkout URL for frontend redirect.
     *
     * @return array{checkout_url: string}
     */
    public function createCheckoutSession(Booking $booking): array
    {
        $amount = (float) $booking->total_price;
        $currency = strtolower($booking->currency ?? 'usd');
        $amountInCents = max(50, (int) round($amount * 100));

        $frontendUrl = config('services.stripe.frontend_url', 'http://localhost:5173');
        $successUrl = $frontendUrl.'/checkout/'.$booking->uuid.'?success=1';
        $cancelUrl = $frontendUrl.'/checkout/'.$booking->uuid;

        $productName = $booking->hotel
            ? "Booking at {$booking->hotel->name} ({$booking->check_in->format('M j')} – {$booking->check_out->format('M j')})"
            : "Hotel booking #{$booking->uuid}";

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'currency' => $currency,
            'provider' => 'stripe',
            'external_id' => null,
            'status' => PaymentStatus::PENDING->value,
            'payload' => ['type' => 'checkout_session'],
        ]);

        try {
            $session = \Yared\SmartStripe\Facades\StripePay::checkout()
                ->product($productName)
                ->price($amountInCents)
                ->currency($currency)
                ->metadata([
                    'booking_uuid' => $booking->uuid,
                    'booking_id' => (string) $booking->id,
                    'payment_id' => (string) $payment->id,
                ])
                ->success($successUrl)
                ->cancel($cancelUrl)
                ->createSession();
        } catch (\Throwable $e) {
            $payment->update(['status' => PaymentStatus::FAILED->value, 'payload' => ['error' => $e->getMessage()]]);
            throw new \RuntimeException('Failed to create checkout session: '.$e->getMessage(), 0, $e);
        }

        $payment->update(['external_id' => $session->id]);

        return ['checkout_url' => $session->url];
    }

    /**
     * Confirm payment and booking after successful webhook. Idempotent; call from job.
     */
    public function confirmPayment(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::COMPLETED->value) {
            return;
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => PaymentStatus::COMPLETED->value]);
            $booking = $payment->booking;
            if ($booking && $booking->status !== BookingStatus::CONFIRMED->value) {
                $booking->update(['status' => BookingStatus::CONFIRMED->value]);
            }
        });

        event(new PaymentConfirmed($payment));
    }

    /**
     * Refund (full or partial). Updates payment refunded_amount and status.
     *
     * @param  float  $amount  Amount to refund in same unit as payment (e.g. dollars)
     * @param  string|null  $reason  Optional reason for refund
     * @param  string|null  $idempotencyKey  Optional idempotency key for Stripe
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null, ?string $idempotencyKey = null): void
    {
        $maxRefundable = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);
        if ($amount <= 0 || $amount > $maxRefundable) {
            throw new \InvalidArgumentException("Refund amount must be between 0 and {$maxRefundable}.");
        }

        if (empty($payment->external_id)) {
            throw new \RuntimeException('Payment has no external provider id; cannot refund.');
        }

        $amountInCents = (int) round($amount * 100);
        $params = [
            'payment_intent' => $payment->external_id,
            'amount' => $amountInCents,
            'reason' => $reason ? 'requested_by_customer' : null,
        ];
        if ($idempotencyKey !== null) {
            $params['idempotency_key'] = $idempotencyKey;
        }

        try {
            $this->stripe()->refunds->create($params);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Refund failed: '.$e->getMessage(), 0, $e);
        }

        $newRefunded = (float) ($payment->refunded_amount ?? 0) + $amount;
        $payment->refunded_amount = $newRefunded;
        $payment->status = $newRefunded >= (float) $payment->amount
            ? PaymentStatus::REFUNDED->value
            : PaymentStatus::COMPLETED->value;
        $payment->save();
    }

    /**
     * Full refund for a payment (convenience).
     */
    public function refundFull(Payment $payment, ?string $reason = null, ?string $idempotencyKey = null): void
    {
        $amount = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);
        if ($amount > 0) {
            $this->refund($payment, $amount, $reason, $idempotencyKey);
        }
    }
}
