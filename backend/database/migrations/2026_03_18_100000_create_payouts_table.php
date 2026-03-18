<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 12, 2)->comment('Gross revenue from bookings');
            $table->decimal('commission', 12, 2);
            $table->decimal('net', 12, 2);
            $table->string('status')->default('pending'); // pending, processing, paid
            $table->string('reference')->nullable()->comment('Bank transfer ref, PayPal ID, etc.');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });

        Schema::create('payout_booking', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payout_id')->constrained('payouts')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_booking');
        Schema::dropIfExists('payouts');
    }
};
