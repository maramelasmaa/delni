<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        // If new schema already exists, skip (likely created by new migrations)
        if (Schema::hasColumn('reviews', 'status') && Schema::hasColumn('reviews', 'is_flagged')) {
            return;
        }

        // If old schema doesn't exist, skip
        if (! Schema::hasColumn('reviews', 'is_approved') || ! Schema::hasColumn('reviews', 'is_flagged')) {
            return;
        }

        // Add new status enum if it doesn't exist
        if (! Schema::hasColumn('reviews', 'status')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('user_id')->index();
            });
        }

        // Convert old boolean flags to new enum values
        DB::table('reviews')->where('is_approved', true)->update(['status' => 'approved']);
        DB::table('reviews')->where('is_approved', false)->update(['status' => 'rejected']);

        // Drop old is_approved column if new status column exists
        if (Schema::hasColumn('reviews', 'is_approved')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('is_approved');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        if (! Schema::hasColumn('reviews', 'status')) {
            return;
        }

        if (Schema::hasColumn('reviews', 'is_approved')) {
            return;
        }

        // Restore old schema: add back is_approved, drop status
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('rating');
        });

        DB::table('reviews')->where('status', 'approved')->update(['is_approved' => true]);
        DB::table('reviews')->whereIn('status', ['rejected', 'pending'])->update(['is_approved' => false]);

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
