<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            // Visibility query filters: is_complete=1 AND provider_access_ends_at >= now()
            // Composite index satisfies both conditions in one scan.
            $table->index(['is_complete', 'provider_access_ends_at'], 'profiles_is_complete_access_ends_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropIndex('profiles_is_complete_access_ends_at_index');
        });
    }
};
