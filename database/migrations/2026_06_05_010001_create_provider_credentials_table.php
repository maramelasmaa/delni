<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('issuer')->nullable();
            $table->string('verification_url')->nullable();
            $table->date('issue_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_credentials');
    }
};
