<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\BookingDispute;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Room;
use App\Models\SupportTicket;
use App\Policies\BookingDisputePolicy;
use App\Policies\BookingPolicy;
use App\Policies\HotelPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\RoomPolicy;
use App\Policies\SupportTicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Hotel::class => HotelPolicy::class,
        Room::class => RoomPolicy::class,
        Booking::class => BookingPolicy::class,
        BookingDispute::class => BookingDisputePolicy::class,
        Payment::class => PaymentPolicy::class,
        Review::class => ReviewPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-commission-reports', function ($user): bool {
            return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
        });
    }
}

