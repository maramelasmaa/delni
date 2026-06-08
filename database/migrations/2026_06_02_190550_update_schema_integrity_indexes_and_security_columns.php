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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_login_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->boolean('security_flagged')->default(false)->index();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('approved_at');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'failed_login_attempts', 'last_failed_login_at', 'locked_until', 'security_flagged']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['starts_at', 'ends_at']);
            $table->dropIndex(['approved_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
