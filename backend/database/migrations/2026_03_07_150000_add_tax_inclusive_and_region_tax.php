<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('tax_inclusive')->default(false)->after('tax_name');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 4)->nullable()->after('code');
            $table->string('tax_name', 64)->nullable()->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('tax_inclusive');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'tax_name']);
        });
    }
};
