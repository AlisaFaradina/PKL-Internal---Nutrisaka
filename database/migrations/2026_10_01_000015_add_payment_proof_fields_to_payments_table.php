<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_file')->nullable()->after('notes');
            $table->enum('confirmation_status', ['pending', 'confirmed', 'rejected'])->default('pending')->after('proof_file');
            $table->timestamp('payment_uploaded_at')->nullable()->after('confirmation_status');
            $table->timestamp('confirmed_at')->nullable()->after('payment_uploaded_at');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('confirmed_at');
            $table->text('rejection_reason')->nullable()->after('confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'proof_file',
                'confirmation_status',
                'payment_uploaded_at',
                'confirmed_at',
                'confirmed_by',
                'rejection_reason'
            ]);
        });
    }
};
