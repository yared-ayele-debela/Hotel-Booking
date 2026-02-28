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
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->unsignedBigInteger('room_type_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('capacity');
            $table->decimal('base_price', 10, 2);
            $table->unsignedInteger('total_rooms');
            $table->timestamps();
            $table->softDeletes();

            $table->index('hotel_id');
            $table->index(['hotel_id', 'room_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

