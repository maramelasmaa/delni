<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_infos', function (Blueprint $table) {
            $table->string('welcome_badge')->nullable()->after('facebook');
            $table->string('welcome_title')->nullable()->after('welcome_badge');
            $table->text('welcome_subtitle')->nullable()->after('welcome_title');
            $table->string('ios_app_url')->nullable()->after('welcome_subtitle');
            $table->string('android_app_url')->nullable()->after('ios_app_url');
        });
    }

    public function down(): void
    {
        Schema::table('contact_infos', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_badge',
                'welcome_title',
                'welcome_subtitle',
                'ios_app_url',
                'android_app_url',
            ]);
        });
    }
};
