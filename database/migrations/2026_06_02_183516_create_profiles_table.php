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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('business_name')->nullable();
            $table->enum('type', ['individual', 'business'])->default('individual');
            $table->text('bio')->nullable();
            $table->string('slug')->unique()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('whatsapp', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_complete')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
