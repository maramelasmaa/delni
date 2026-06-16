<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('profile_stats', 'is_featured')) {
            return;
        }

        Schema::table('profile_stats', function (Blueprint $table): void {
            $table->dropIndex(['is_featured']);
            $table->dropColumn(['is_featured', 'featured_until']);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('profile_stats', 'is_featured')) {
            return;
        }

        Schema::table('profile_stats', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->index()->after('is_top_rated');
            $table->date('featured_until')->nullable()->after('is_featured');
        });
    }
};
