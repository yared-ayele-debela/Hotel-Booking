<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('guest_email')->nullable()->after('customer_id');
            $table->string('guest_name')->nullable()->after('guest_email');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['guest_email']);
            $table->dropColumn(['guest_email', 'guest_name']);
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
