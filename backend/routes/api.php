<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AmenityController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\HotelSearchController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks: handled by yared/laravel-smart-stripe at api/webhooks/stripe
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| API v1 Routes (Customer-facing)
|--------------------------------------------------------------------------
|
| All customer-facing API under version prefix. Use Sanctum for token-based
| auth from React/SPA.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/ping', fn () => response()->json(['ok' => true, 'version' => 'v1']))->name('ping');
    Route::get('/website-settings', [\App\Http\Controllers\Api\V1\WebsiteSettingsController::class, 'index'])->name('website-settings.index');

    // Auth (no token)
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register/vendor', [AuthController::class, 'registerVendor'])->name('register.vendor');

    // Hotel search & single hotel (no auth)
    Route::get('/hotels', [HotelSearchController::class, 'index'])->name('hotels.index');
    Route::get('/hotels/{id}/review-sentiment', [AiController::class, 'reviewSentiment'])
        ->whereNumber('id')
        ->middleware('throttle:40,1')
        ->name('hotels.review-sentiment');
    Route::get('/hotels/{id}', [HotelSearchController::class, 'show'])->name('hotels.show');

    // AI features (optional auth on recommendations via Bearer token)
    Route::get('/ai/recommendations', [AiController::class, 'recommendations'])
        ->middleware(['auth.optional', 'throttle:30,1'])
        ->name('ai.recommendations');
    Route::post('/ai/chat', [AiController::class, 'chat'])
        ->middleware('throttle:20,1')
        ->name('ai.chat');

    // Geocode autocomplete for map search (throttled)
    Route::get('/geocode/autocomplete', [\App\Http\Controllers\Api\V1\GeocodeController::class, 'autocomplete'])
        ->middleware('throttle:60,1')
        ->name('geocode.autocomplete');

    // Locations for home / browse (no auth)
    Route::get('/countries', [LocationController::class, 'countries'])->name('locations.countries');
    Route::get('/cities', [LocationController::class, 'cities'])->name('locations.cities');

    // Amenities for filters (no auth)
    Route::get('/amenities', [AmenityController::class, 'index'])->name('amenities.index');

    // Reviews list (no auth) – approved only
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

    // Guest checkout (no auth)
    Route::post('/bookings/guest/preview', [BookingController::class, 'previewGuest'])->name('bookings.guest.preview');
    Route::post('/bookings/guest', [BookingController::class, 'storeGuest'])->name('bookings.guest.store');
    Route::get('/bookings/guest-view', [BookingController::class, 'guestView'])->name('bookings.guest-view');
    Route::get('/bookings/guest-invoice', [BookingController::class, 'guestInvoice'])->name('bookings.guest-invoice');
    Route::post('/bookings/guest-checkout-session', [BookingController::class, 'guestCheckoutSession'])->name('bookings.guest-checkout-session')->middleware('signed');
    Route::post('/bookings/guest-dispute', [BookingController::class, 'guestStoreDispute'])->name('bookings.guest-dispute')->middleware('signed');

    // Authenticated customer routes
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::put('/me', [AuthController::class, 'update'])->name('me.update');
        Route::post('/bookings/preview', [BookingController::class, 'preview'])->name('bookings.preview');
        Route::apiResource('bookings', BookingController::class)->only(['index', 'store']);
        Route::get('/bookings/{uuid}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::get('/bookings/{uuid}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{uuid}/checkout-session', [BookingController::class, 'createCheckoutSession'])->name('bookings.checkout-session');
        Route::post('/bookings/{uuid}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{uuid}/dispute', [BookingController::class, 'storeDispute'])->name('bookings.dispute.store');
        Route::post('/bookings/{uuid}/claim', [BookingController::class, 'claim'])->name('bookings.claim');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('/wishlist/{hotelId}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
        Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
        Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::post('/support-tickets/{supportTicket}/replies', [SupportTicketController::class, 'storeReply'])->name('support-tickets.replies.store');
    });
});
