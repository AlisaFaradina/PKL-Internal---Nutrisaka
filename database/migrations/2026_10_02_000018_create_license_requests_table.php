<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_requests', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('email');
            $table->string('phone');
            $table->text('address')->nullable();
            $table->string('device_id');
            $table->string('app_id')->default('APP-NUTRISAKA-SPPG-2026');
            $table->string('password')->nullable(); // Hashed password, never plaintext
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->unsignedBigInteger('license_id')->nullable();
            $table->string('license_token')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_requests');
    }
};
