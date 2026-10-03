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
        Schema::table('licenses', function (Blueprint $table) {
            // Add last_seen_at if it doesn't exist
            if (!Schema::hasColumn('licenses', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('trial_ends_at');
            }

            // Add trial_signature if it doesn't exist
            if (!Schema::hasColumn('licenses', 'trial_signature')) {
                $table->string('trial_signature')->nullable()->after('last_seen_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (Schema::hasColumn('licenses', 'last_seen_at')) {
                $table->dropColumn('last_seen_at');
            }
            if (Schema::hasColumn('licenses', 'trial_signature')) {
                $table->dropColumn('trial_signature');
            }
        });
    }
};
