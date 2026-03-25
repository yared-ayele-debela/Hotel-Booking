<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_disputes', function (Blueprint $table): void {
            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_disputes', function (Blueprint $table): void {
            $table->dropUnique(['booking_id']);
        });
    }
};
