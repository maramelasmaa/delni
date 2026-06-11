<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('user');
            $table->ipAddress('ip_address')->index();
            $table->string('provider'); // 'deepseek', etc.
            $table->string('model');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('estimated_cost', 10, 6)->default(0);
            $table->string('endpoint')->nullable();
            $table->string('request_type'); // 'extraction', 'search', etc.
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
