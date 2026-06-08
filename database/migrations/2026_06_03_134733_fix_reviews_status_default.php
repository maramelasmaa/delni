<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Reviews must start as 'pending' to pass through moderation.
            // Original create_reviews_table incorrectly defaulted to 'approved',
            // bypassing the moderation queue for all new submissions.
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('approved')
                ->change();
        });
    }
};
