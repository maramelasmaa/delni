<?php

declare(strict_types=1);

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
        // profiles: index created_at for sort-by-newest queries
        Schema::table('profiles', function (Blueprint $table) {
            $table->index('created_at', 'profiles_created_at_index');
        });

        // profile_stats: index rating_avg and reviews_count for search and listing rankings
        Schema::table('profile_stats', function (Blueprint $table) {
            $table->index('rating_avg', 'profile_stats_rating_avg_index');
            $table->index('reviews_count', 'profile_stats_reviews_count_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('profiles_created_at_index');
        });

        Schema::table('profile_stats', function (Blueprint $table) {
            $table->dropIndex('profile_stats_rating_avg_index');
            $table->dropIndex('profile_stats_reviews_count_index');
        });
    }
};
