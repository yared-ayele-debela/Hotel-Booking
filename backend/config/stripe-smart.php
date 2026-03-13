<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    */
    'secret_key' => env('STRIPE_SECRET_KEY', env('STRIPE_SECRET')),
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', env('STRIPE_KEY')),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Fraud Detection
    |--------------------------------------------------------------------------
    */
    'fraud_detection' => [
        'enabled' => env('STRIPE_FRAUD_DETECTION', true),
        'max_payments_per_ip_per_hour' => 10,
        'max_payments_per_user_per_hour' => 5,
        'suspicious_countries' => [], // Add country codes to block
        'rapid_payment_window_seconds' => 60,
        'max_rapid_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Smart Metadata
    |--------------------------------------------------------------------------
    */
    'metadata' => [
        'enabled' => true,
        'include' => ['user_id', 'ip', 'browser', 'device', 'country', 'app_name', 'laravel_version'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'table' => 'stripe_payment_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Mode Simulator
    |--------------------------------------------------------------------------
    */
    'simulator' => [
        'enabled' => env('STRIPE_SIMULATOR_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout Default URLs
    |--------------------------------------------------------------------------
    | Used when success/cancel URLs are not explicitly set
    */
    'checkout' => [
        'success_url' => env('STRIPE_SUCCESS_URL'),
        'cancel_url' => env('STRIPE_CANCEL_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payable Handlers (for checkout.session.completed webhook)
    |--------------------------------------------------------------------------
    */
    'payable_handlers' => [
        'booking_id' => [
            'model' => \App\Models\Booking::class,
            'method' => 'markAsPaid',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Route
    |--------------------------------------------------------------------------
    */
    'webhook_path' => 'api/webhooks/stripe',

    /*
    |--------------------------------------------------------------------------
    | Billable Table (for stripe_id column)
    |--------------------------------------------------------------------------
    */
    'billable_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Webhook Listeners (config-based)
    |--------------------------------------------------------------------------
    | Register listeners here or use StripeWebhook::listen() in AppServiceProvider
    */
    'webhook_listeners' => [],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

];
