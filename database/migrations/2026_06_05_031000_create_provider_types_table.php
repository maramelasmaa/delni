<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('name_ar');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('provider_types')->insert([
            ['code' => 'individual', 'name' => 'Individual', 'name_ar' => 'فرد', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'company', 'name' => 'Company', 'name_ar' => 'شركة', 'sort_order' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'agency', 'name' => 'Agency', 'name_ar' => 'وكالة', 'sort_order' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'clinic', 'name' => 'Clinic', 'name_ar' => 'عيادة', 'sort_order' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'studio', 'name' => 'Studio', 'name_ar' => 'استوديو', 'sort_order' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'freelancer', 'name' => 'Freelancer', 'name_ar' => 'مستقل', 'sort_order' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'other', 'name' => 'Other', 'name_ar' => 'أخرى', 'sort_order' => 999, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_types');
    }
};
