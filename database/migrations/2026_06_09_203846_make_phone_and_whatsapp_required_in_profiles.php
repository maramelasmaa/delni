<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('profiles')->whereNull('phone')->update(['phone' => '']);
        DB::table('profiles')->whereNull('whatsapp')->update(['whatsapp' => '']);

        Schema::table('profiles', function (Blueprint $table) {
            $table->string('phone', 20)->default('')->nullable(false)->change();
            $table->string('whatsapp', 20)->default('')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
            $table->string('whatsapp', 20)->nullable()->change();
        });
    }
};
