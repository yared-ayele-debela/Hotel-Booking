<?php

namespace App\Providers;

use App\Events\PaymentConfirmed;
use App\Events\SupportTicketReplyCreated;
use App\Listeners\SendBookingConfirmationNotification;
use App\Listeners\SendSupportTicketReplyNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        Event::listen(SupportTicketReplyCreated::class, SendSupportTicketReplyNotification::class);
        Event::listen(PaymentConfirmed::class, SendBookingConfirmationNotification::class);
    }
}
