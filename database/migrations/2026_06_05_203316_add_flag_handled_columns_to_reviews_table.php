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
        Schema::table('reviews', function (Blueprint $table) {
            $table->timestamp('flag_handled_at')->nullable()->after('flagged_reason');
            $table->foreignId('flag_handled_by')
                ->nullable()
                ->after('flag_handled_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['flag_handled_by']);
            $table->dropColumn(['flag_handled_at', 'flag_handled_by']);
        });
    }
};
