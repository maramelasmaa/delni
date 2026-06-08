<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->string('provider_type')->default('other')->after('type');
            $table->boolean('offers_remote_work')->default(false)->after('provider_type');
            $table->string('map_url')->nullable()->after('offers_remote_work');
            $table->string('service_area_note', 500)->nullable()->after('map_url');
        });

        Schema::table('provider_links', function (Blueprint $table): void {
            $table->string('type')->default('other')->after('profile_id');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->index(['profile_id', 'is_active', 'sort_order']);
        });

        DB::table('profiles')
            ->where(function ($query): void {
                $query->whereNotNull('website')
                    ->orWhereNotNull('instagram')
                    ->orWhereNotNull('facebook')
                    ->orWhereNotNull('linkedin');
            })
            ->orderBy('id')
            ->chunkById(100, function ($profiles): void {
                foreach ($profiles as $profile) {
                    foreach (['website', 'instagram', 'facebook', 'linkedin'] as $index => $type) {
                        $url = $profile->{$type} ?? null;

                        if (! filled($url)) {
                            continue;
                        }

                        $exists = DB::table('provider_links')
                            ->where('profile_id', $profile->id)
                            ->where('type', $type)
                            ->where('url', $url)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        DB::table('provider_links')->insert([
                            'profile_id' => $profile->id,
                            'type' => $type,
                            'label' => ucfirst($type),
                            'url' => $url,
                            'sort_order' => $index,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('provider_links', function (Blueprint $table): void {
            $table->dropIndex(['profile_id', 'is_active', 'sort_order']);
            $table->dropColumn(['type', 'is_active']);
        });

        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn(['provider_type', 'offers_remote_work', 'map_url', 'service_area_note']);
        });
    }
};
