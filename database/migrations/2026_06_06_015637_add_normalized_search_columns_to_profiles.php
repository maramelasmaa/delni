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
        Schema::table('profiles', function (Blueprint $table) {
            // Normalized (Arabic) search columns
            // Used for LIKE queries to find profiles regardless of:
            // - Hamza variants (أ، إ، آ → ا)
            // - Diacritics (removed)
            // - Ta variants (ة → ه)
            // - Alef/ya variants (ى → ي)
            //
            // Use string() with reasonable limits for search indexing
            $table->string('search_business_name', 255)->nullable()->after('business_name')->index();
            $table->string('search_bio', 500)->nullable()->after('bio')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex(['search_business_name']);
            $table->dropIndex(['search_bio']);
            $table->dropColumn(['search_business_name', 'search_bio']);
        });
    }
};
