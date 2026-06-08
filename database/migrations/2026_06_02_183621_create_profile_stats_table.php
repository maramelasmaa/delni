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
        Schema::create('profile_stats', function (Blueprint $table) {
            $table->foreignId('profile_id')->primary()->constrained()->cascadeOnDelete();
            $table->decimal('rating_avg', 3, 1)->default(0.0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_top_rated')->default(false);
            $table->boolean('is_featured')->default(false)->index();
            $table->date('featured_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_stats');
    }
};
