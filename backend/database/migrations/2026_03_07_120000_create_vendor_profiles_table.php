<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('business_name')->nullable();
            $table->text('business_details')->nullable(); // JSON or text for address, phone, etc.
            $table->json('documents')->nullable(); // optional file paths
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id');
        });

        // Create approved profiles for existing vendors (backward compatibility)
        $vendorIds = DB::table('users')->where('role', Role::VENDOR->value)->pluck('id');
        $now = now();
        foreach ($vendorIds as $userId) {
            DB::table('vendor_profiles')->insert([
                'user_id' => $userId,
                'status' => 'approved',
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};
