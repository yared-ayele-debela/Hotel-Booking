<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->json('cancellation_policy')->nullable()->after('tax_name');
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->json('cancellation_policy')->nullable()->after('total_rooms');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('cancellation_policy');
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('cancellation_policy');
        });
    }
};
