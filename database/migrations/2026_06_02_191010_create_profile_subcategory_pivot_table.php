<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profile_subcategory')) {
            Schema::create('profile_subcategory', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['profile_id', 'subcategory_id']);
            });
        }

        if (Schema::hasTable('profiles') && Schema::hasColumn('profiles', 'subcategory_id')) {
            $profiles = DB::table('profiles')->whereNotNull('subcategory_id')->get(['id', 'subcategory_id']);

            foreach ($profiles as $profile) {
                DB::table('profile_subcategory')->updateOrInsert([
                    'profile_id' => $profile->id,
                    'subcategory_id' => $profile->subcategory_id,
                ], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $dropColumns = [];
            if (Schema::hasColumn('profiles', 'subcategory_id')) {
                $dropColumns[] = 'subcategory_id';
            }

            if (! empty($dropColumns)) {
                Schema::table('profiles', function (Blueprint $table) use ($dropColumns) {
                    $table->dropColumn($dropColumns);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('profiles')) {
            return;
        }

        if (! Schema::hasColumn('profiles', 'subcategory_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete()->index()->after('category_id');
            });
        }

        if (Schema::hasTable('profile_subcategory')) {
            $cursor = DB::table('profile_subcategory')->orderBy('profile_id')->cursor();
            $updated = [];

            foreach ($cursor as $row) {
                if (isset($updated[$row->profile_id])) {
                    continue;
                }

                $categoryId = DB::table('subcategories')->where('id', $row->subcategory_id)->value('category_id');
                DB::table('profiles')->where('id', $row->profile_id)->update([
                    'category_id' => $categoryId,
                    'subcategory_id' => $row->subcategory_id,
                ]);
                $updated[$row->profile_id] = true;
            }

            Schema::dropIfExists('profile_subcategory');
        }
    }
};
