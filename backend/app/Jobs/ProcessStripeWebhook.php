<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $eventId,
        public string $type,
        public array $payload
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        $webhookEvent = WebhookEvent::where('provider', 'stripe')
            ->where('event_id', $this->eventId)
            ->first();

        if (! $webhookEvent) {
            Log::warning('ProcessStripeWebhook: webhook event not found', ['event_id' => $this->eventId]);
            return;
        }

        if ($webhookEvent->processed_at !== null) {
            return;
        }

        if ($this->type === 'payment_intent.succeeded') {
            $paymentIntent = $this->payload['data']['object'] ?? null;
            if ($paymentIntent && isset($paymentIntent['id'])) {
                $payment = Payment::where('provider', 'stripe')
                    ->where('external_id', $paymentIntent['id'])
                    ->first();
                if ($payment) {
                    $paymentService->confirmPayment($payment);
                }
            }
        }

        if ($this->type === 'checkout.session.completed') {
            $session = $this->payload['data']['object'] ?? null;
            if ($session && isset($session['id'])) {
                $payment = Payment::where('provider', 'stripe')
                    ->where('external_id', $session['id'])
                    ->first();
                if ($payment) {
                    $paymentIntentId = $session['payment_intent'] ?? null;
                    if ($paymentIntentId) {
                        $payment->update(['external_id' => $paymentIntentId]);
                    }
                    $paymentService->confirmPayment($payment);
                }
            }
        }

        $webhookEvent->update(['processed_at' => now()]);
    }
}
