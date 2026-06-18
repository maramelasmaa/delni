<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop the strict unique constraint so rejected reviews don't permanently
            // block a user from leaving a new review for the same provider.
            // Duplicate-active-review enforcement is now handled at the app layer
            // (ReviewCreationService + CreateReviewRequest) — only approved/pending
            // reviews block a new submission.
            $table->dropUnique('reviews_profile_id_user_id_unique');

            // Composite index on the columns used by the eligibility check:
            // where('profile_id', x)->where('user_id', y)->whereIn('status', [...])
            $table->index(['profile_id', 'user_id', 'status'], 'idx_reviews_profile_user_status');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_profile_user_status');
            $table->unique(['profile_id', 'user_id']);
        });
    }
};
