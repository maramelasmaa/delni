<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_stats', function (Blueprint $table) {
            // Homepage placement
            $table->boolean('is_homepage_featured')->default(false)->index();
            $table->date('homepage_featured_until')->nullable();

            // Top search placement
            $table->boolean('is_top_search')->default(false)->index();
            $table->date('top_search_until')->nullable();

            // Category spotlight
            $table->boolean('is_top_category')->default(false)->index();
            $table->date('top_category_until')->nullable();

            // Subcategory spotlight
            $table->boolean('is_top_subcategory')->default(false)->index();
            $table->date('top_subcategory_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profile_stats', function (Blueprint $table) {
            $table->dropIndex(['is_homepage_featured']);
            $table->dropIndex(['is_top_search']);
            $table->dropIndex(['is_top_category']);
            $table->dropIndex(['is_top_subcategory']);

            $table->dropColumn([
                'is_homepage_featured',
                'homepage_featured_until',
                'is_top_search',
                'top_search_until',
                'is_top_category',
                'top_category_until',
                'is_top_subcategory',
                'top_subcategory_until',
            ]);
        });
    }
};
