<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }

    public function down(): void
    {
        // Intentionally not reversible — subscription plans are permanently removed.
        // Restore from a database backup if needed.
    }
};
