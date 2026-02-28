<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\HotelSearchController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks (no auth; signature verification only)
|--------------------------------------------------------------------------
*/
Route::post('webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

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

    // Auth (no token)
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // Hotel search & single hotel (no auth)
    Route::get('/hotels', [HotelSearchController::class, 'index'])->name('hotels.index');
    Route::get('/hotels/{id}', [HotelSearchController::class, 'show'])->name('hotels.show');

    // Reviews list (no auth) – approved only
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');

    // Authenticated customer routes
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/bookings/preview', [BookingController::class, 'preview'])->name('bookings.preview');
        Route::apiResource('bookings', BookingController::class)->only(['index', 'store']);
        Route::get('/bookings/{uuid}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::get('/bookings/{uuid}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{uuid}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('/wishlist/{hotelId}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    });
});
