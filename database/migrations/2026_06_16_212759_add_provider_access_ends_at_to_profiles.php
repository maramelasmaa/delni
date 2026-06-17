<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dateTime('provider_access_ends_at')->nullable()->after('is_complete');
        });

        // Backfill: set provider_access_ends_at from the latest active subscription ends_at
        if (Schema::hasTable('subscriptions')) {
            DB::statement('
                UPDATE profiles
                SET provider_access_ends_at = (
                    SELECT MAX(ends_at)
                    FROM subscriptions
                    WHERE subscriptions.user_id = profiles.user_id
                    AND subscriptions.is_active = 1
                )
                WHERE EXISTS (
                    SELECT 1 FROM subscriptions
                    WHERE subscriptions.user_id = profiles.user_id
                    AND subscriptions.is_active = 1
                )
            ');
        }
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn('provider_access_ends_at');
        });
    }
};
