<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Tier identifier
            $table->enum('tier', ['basic', 'standard', 'premium', 'enterprise'])
                ->default('basic')
                ->after('is_active');

            // Featured placement allocation
            $table->unsignedSmallInteger('featured_days_per_subscription')
                ->default(0)
                ->comment('Days of featured placement included per subscription');

            // Homepage placement
            $table->boolean('includes_homepage_featured')
                ->default(false)
                ->comment('Plan includes homepage featured placement');

            // Top search placement
            $table->boolean('includes_top_search')
                ->default(false)
                ->comment('Plan includes top search placement');

            // Category spotlight
            $table->boolean('includes_category_spotlight')
                ->default(false)
                ->comment('Plan includes category spotlight placement');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'tier',
                'featured_days_per_subscription',
                'includes_homepage_featured',
                'includes_top_search',
                'includes_category_spotlight',
            ]);
        });
    }
};
