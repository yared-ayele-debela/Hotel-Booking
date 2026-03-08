<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('late_checkout_price', 10, 2)->nullable()->after('check_out');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('late_checkout')->default(false)->after('total_price');
            $table->decimal('late_checkout_amount', 10, 2)->nullable()->after('late_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('late_checkout_price');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['late_checkout', 'late_checkout_amount']);
        });
    }
};
