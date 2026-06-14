<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite covering indexes for the discoverable-profiles visibility query.
     *
     * Pattern: WHERE profiles.is_complete = 1 AND profiles.category_id = ?
     *          WHERE profiles.is_complete = 1 AND profiles.city_id = ?
     *
     * Putting is_complete first (low cardinality, always filtered) lets MySQL
     * narrow to visible profiles before applying the high-cardinality column filter.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->index(['is_complete', 'category_id'], 'profiles_is_complete_category_id_index');
            $table->index(['is_complete', 'city_id'], 'profiles_is_complete_city_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('profiles_is_complete_category_id_index');
            $table->dropIndex('profiles_is_complete_city_id_index');
        });
    }
};
