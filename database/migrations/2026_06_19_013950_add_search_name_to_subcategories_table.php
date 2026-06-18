<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('search_name')->nullable()->after('name_ar')->index();
        });

        // Backfill existing rows on MySQL only (SQLite lacks REGEXP_REPLACE).
        // Normalizes the most common Arabic variants so existing production data
        // is searchable immediately without a separate job.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("
                UPDATE subcategories
                SET search_name = REGEXP_REPLACE(
                    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                        name_ar,
                        'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'),
                        'ة', 'ه'), 'ى', 'ي'),
                        'ئ', 'ي'),
                    '[\\u064B-\\u065F]', ''
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn('search_name');
        });
    }
};
