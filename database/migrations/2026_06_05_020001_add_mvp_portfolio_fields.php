<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_items', function (Blueprint $table): void {
            $table->string('short_description')->nullable()->after('title');
            $table->string('main_url')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_items', function (Blueprint $table): void {
            $table->dropColumn(['short_description', 'main_url']);
        });
    }
};
