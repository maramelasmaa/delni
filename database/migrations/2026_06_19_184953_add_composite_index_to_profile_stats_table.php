<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_stats', function (Blueprint $table): void {
            // ORDER BY rating_avg DESC, reviews_count DESC on top-rated/sort queries
            // produces filesort on an unindexed column. Composite index eliminates it.
            $table->index(['rating_avg', 'reviews_count'], 'profile_stats_rating_reviews_index');
        });
    }

    public function down(): void
    {
        Schema::table('profile_stats', function (Blueprint $table): void {
            $table->dropIndex('profile_stats_rating_reviews_index');
        });
    }
};
