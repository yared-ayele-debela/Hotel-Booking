
<?php

use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Admin\DashboardController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
     */

    Route::middleware(['auth','admin','web'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/geoapify/autocomplete', [\App\Http\Controllers\Admin\GeoapifyController::class, 'autocomplete'])->name('geoapify.autocomplete');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Admin only (no vendor): disputes, review moderation, support tickets
    Route::middleware('admin_only')->group(function () {
        Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
        Route::patch('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'update'])->name('disputes.update');
        Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'show'])->name('reviews.show');
        Route::patch('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewModerationController::class, 'update'])->name('reviews.update');
        Route::get('/support-tickets', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('/support-tickets/{supportTicket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::patch('/support-tickets/{supportTicket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'update'])->name('support-tickets.update');
        Route::post('/support-tickets/{supportTicket}/replies', [\App\Http\Controllers\Admin\SupportTicketController::class, 'storeReply'])->name('support-tickets.replies.store');
    });

    // Super Admin only: vendors, commission & website settings
    Route::middleware('super_admin')->group(function () {
        Route::get('/vendors', [\App\Http\Controllers\Admin\VendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/{vendor}', [\App\Http\Controllers\Admin\VendorController::class, 'show'])->name('vendors.show');
        Route::post('/vendors/{vendor}/approve', [\App\Http\Controllers\Admin\VendorController::class, 'approve'])->name('vendors.approve');
        Route::post('/vendors/{vendor}/reject', [\App\Http\Controllers\Admin\VendorController::class, 'reject'])->name('vendors.reject');
        Route::patch('/vendors/{vendor}/status', [\App\Http\Controllers\Admin\VendorController::class, 'updateStatus'])->name('vendors.update-status');
        Route::get('/commission', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commission.index');
        Route::get('/commission/edit', [\App\Http\Controllers\Admin\CommissionController::class, 'edit'])->name('commission.edit');
        Route::put('/commission', [\App\Http\Controllers\Admin\CommissionController::class, 'update'])->name('commission.update');
        Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/create', [\App\Http\Controllers\Admin\PayoutController::class, 'create'])->name('payouts.create');
        Route::post('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'store'])->name('payouts.store');
        Route::get('/payouts/export', [\App\Http\Controllers\Admin\PayoutController::class, 'export'])->name('payouts.export');
        Route::get('/payouts/{payout}', [\App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('payouts.show');
        Route::patch('/payouts/{payout}', [\App\Http\Controllers\Admin\PayoutController::class, 'update'])->name('payouts.update');
        Route::post('/payouts/{payout}/mark-paid', [\App\Http\Controllers\Admin\PayoutController::class, 'markPaid'])->name('payouts.mark-paid');
        
        // Website Settings
        Route::get('/website-settings', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'index'])->name('website-settings.index');
        Route::put('/website-settings', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'update'])->name('website-settings.update');
        Route::delete('/website-settings/logo', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'removeLogo'])->name('website-settings.remove-logo');
        Route::delete('/website-settings/favicon', [\App\Http\Controllers\Admin\WebsiteSettingsController::class, 'removeFavicon'])->name('website-settings.remove-favicon');

        Route::resource('countries', \App\Http\Controllers\Admin\CountryController::class);
        Route::resource('cities', \App\Http\Controllers\Admin\CityController::class);
        Route::resource('amenities', \App\Http\Controllers\Admin\AmenityController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

    // Vendor dashboard (vendor role only)
    Route::middleware('vendor')->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\Vendor\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\Admin\Vendor\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Admin\Vendor\ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/bank-accounts', [\App\Http\Controllers\Admin\Vendor\ProfileController::class, 'storeBankAccount'])->name('profile.bank-accounts.store');
        Route::put('/profile/bank-accounts/{bankAccount}', [\App\Http\Controllers\Admin\Vendor\ProfileController::class, 'updateBankAccount'])->name('profile.bank-accounts.update');
        Route::delete('/profile/bank-accounts/{bankAccount}', [\App\Http\Controllers\Admin\Vendor\ProfileController::class, 'destroyBankAccount'])->name('profile.bank-accounts.destroy');
        Route::get('/reports', [\App\Http\Controllers\Admin\Vendor\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\Vendor\ReportController::class, 'export'])->name('reports.export');
        Route::resource('hotels', \App\Http\Controllers\Admin\Vendor\HotelController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('rooms', \App\Http\Controllers\Admin\Vendor\RoomController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('rooms/{room}/availability', [\App\Http\Controllers\Admin\Vendor\RoomController::class, 'availability'])->name('rooms.availability');
        Route::post('rooms/{room}/availability', [\App\Http\Controllers\Admin\Vendor\RoomController::class, 'storeAvailability'])->name('rooms.availability.store');
        Route::get('/bookings', [\App\Http\Controllers\Admin\Vendor\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/old', [\App\Http\Controllers\Admin\Vendor\BookingController::class, 'oldBookings'])->name('bookings.old');
        Route::post('/bookings/{uuid}/mark-old', [\App\Http\Controllers\Admin\Vendor\BookingController::class, 'markAsOld'])->name('bookings.mark-old');
        Route::post('/bookings/{uuid}/unmark-old', [\App\Http\Controllers\Admin\Vendor\BookingController::class, 'unmarkAsOld'])->name('bookings.unmark-old');
        Route::get('/bookings/{uuid}/invoice', [\App\Http\Controllers\Admin\Vendor\BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::get('/payouts', [\App\Http\Controllers\Admin\Vendor\PayoutController::class, 'index'])->name('payouts.index');
        Route::get('/support-tickets', [\App\Http\Controllers\Admin\Vendor\SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('/support-tickets/create', [\App\Http\Controllers\Admin\Vendor\SupportTicketController::class, 'create'])->name('support-tickets.create');
        Route::post('/support-tickets', [\App\Http\Controllers\Admin\Vendor\SupportTicketController::class, 'store'])->name('support-tickets.store');
        Route::get('/support-tickets/{supportTicket}', [\App\Http\Controllers\Admin\Vendor\SupportTicketController::class, 'show'])->name('support-tickets.show');
        
        // Room Images
        Route::get('rooms/{room}/images', [\App\Http\Controllers\Admin\Vendor\RoomImageController::class, 'index'])->name('rooms.images.index');
        Route::post('rooms/{room}/images', [\App\Http\Controllers\Admin\Vendor\RoomImageController::class, 'store'])->name('rooms.images.store');
        Route::put('rooms/{room}/images/{image}', [\App\Http\Controllers\Admin\Vendor\RoomImageController::class, 'update'])->name('rooms.images.update');
        Route::delete('rooms/{room}/images/{image}', [\App\Http\Controllers\Admin\Vendor\RoomImageController::class, 'destroy'])->name('rooms.images.destroy');
        Route::post('rooms/{room}/images/reorder', [\App\Http\Controllers\Admin\Vendor\RoomImageController::class, 'reorder'])->name('rooms.images.reorder');
        
        // Hotel Images
        Route::get('hotels/{hotel}/images', [\App\Http\Controllers\Admin\Vendor\HotelImageController::class, 'index'])->name('hotels.images.index');
        Route::post('hotels/{hotel}/images', [\App\Http\Controllers\Admin\Vendor\HotelImageController::class, 'store'])->name('hotels.images.store');
        Route::put('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\Vendor\HotelImageController::class, 'update'])->name('hotels.images.update');
        Route::delete('hotels/{hotel}/images/{image}', [\App\Http\Controllers\Admin\Vendor\HotelImageController::class, 'destroy'])->name('hotels.images.destroy');
        Route::post('hotels/{hotel}/images/reorder', [\App\Http\Controllers\Admin\Vendor\HotelImageController::class, 'reorder'])->name('hotels.images.reorder');
    });
});
