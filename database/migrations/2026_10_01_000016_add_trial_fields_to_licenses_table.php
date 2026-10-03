<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->boolean('is_trial')->default(false)->after('status');
            $table->timestamp('trial_started_at')->nullable()->after('is_trial');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->timestamp('first_run_at')->nullable()->after('activated_at');
            $table->timestamp('last_seen_at')->nullable()->after('trial_ends_at');
            $table->string('trial_signature')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['is_trial', 'trial_started_at', 'trial_ends_at', 'first_run_at', 'last_seen_at', 'trial_signature']);
        });
    }
};
