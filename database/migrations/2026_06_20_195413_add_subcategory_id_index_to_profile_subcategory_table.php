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
        Schema::table('profile_subcategory', function (Blueprint $table) {
            $table->index('subcategory_id', 'profile_subcategory_subcategory_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_subcategory', function (Blueprint $table) {
            $table->dropIndex('profile_subcategory_subcategory_id_idx');
        });
    }
};
