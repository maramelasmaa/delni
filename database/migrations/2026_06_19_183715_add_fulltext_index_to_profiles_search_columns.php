<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('profiles', function (Blueprint $table): void {
            // LIKE %keyword% ignores B-tree indexes entirely (leading wildcard).
            // A composite FULLTEXT index lets MySQL use MATCH AGAINST instead,
            // scanning the inverted index rather than every row.
            $table->fullText(['search_business_name', 'search_bio'], 'profiles_search_fulltext');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropFullText('profiles_search_fulltext');
        });
    }
};
