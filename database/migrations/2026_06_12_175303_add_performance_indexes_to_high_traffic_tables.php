<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing performance indexes to high-traffic tables.
     *
     * EXISTING indexes NOT duplicated here:
     * - subscriptions: (user_id, is_active, ends_at) — added in 2026_06_10_000000
     * - reviews: status — added in create_reviews_table migration
     * - profiles: slug (unique), city_id, category_id, is_complete — added in create_profiles_table
     *
     * NEW indexes added here:
     * - reviews: composite (profile_id, status, deleted_at) — covers approved-reviews-per-profile query
     * - users: is_active — covers visibility subquery user filter
     * - users: is_suspended — covers account status checks
     */
    public function up(): void
    {
        // reviews: composite index for approved-reviews-per-profile queries
        // Pattern: WHERE profile_id = ? AND status = 'approved' AND deleted_at IS NULL
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['profile_id', 'status', 'deleted_at'], 'reviews_profile_id_status_deleted_at_index');
        });

        // users: is_active and is_suspended used by ProfileVisibilityService and account middleware
        Schema::table('users', function (Blueprint $table) {
            $table->index('is_active', 'users_is_active_index');
            $table->index('is_suspended', 'users_is_suspended_index');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_profile_id_status_deleted_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_active_index');
            $table->dropIndex('users_is_suspended_index');
        });
    }
};
