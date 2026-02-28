<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook. Verify signature, enforce idempotency, respond 200, process in job.
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            Log::error('Stripe webhook: STRIPE_WEBHOOK_SECRET not set');
            return response('', 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: signature verification failed', ['error' => $e->getMessage()]);
            return response('', 400);
        }

        $eventId = $event->id;

        try {
            WebhookEvent::create([
                'provider' => 'stripe',
                'event_id' => $eventId,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response('', 200);
        }

        ProcessStripeWebhook::dispatch(
            $eventId,
            $event->type,
            $event->toArray()
        );

        return response('', 200);
    }
}
