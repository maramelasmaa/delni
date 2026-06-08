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
        if (! Schema::hasTable('reviews')) {
            return;
        }

        $connection = config('database.default');

        // Try to drop the old index if it exists, using connection-specific syntax
        if ($connection === 'sqlite') {
            try {
                DB::statement('DROP INDEX IF EXISTS reviews_user_id_created_at_index');
            } catch (Exception) {
                // Ignore if index doesn't exist or has a different name
            }
        } elseif ($connection === 'mysql') {
            try {
                DB::statement('ALTER TABLE reviews DROP INDEX IF EXISTS `reviews_user_id_created_at_index`');
            } catch (Exception) {
                // Ignore if index doesn't exist or has a different name
            }
        }

        // Create the index with explicit name to avoid constraint issues and ensure consistency
        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'created_at'], 'idx_reviews_user_id_created_at');
            } catch (Exception) {
                // Index might already exist with this name
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the properly-named index
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_user_id_created_at');
        });

        // Recreate the old index name for consistency (if needed for backward compatibility)
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }
};
