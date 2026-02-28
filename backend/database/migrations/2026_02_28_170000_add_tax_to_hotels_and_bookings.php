<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table): void {
            $table->decimal('tax_rate', 5, 4)->nullable()->after('status');
            $table->string('tax_name', 64)->nullable()->after('tax_rate');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table): void {
            $table->dropColumn(['tax_rate', 'tax_name']);
        });
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('tax_amount');
        });
    }
};
