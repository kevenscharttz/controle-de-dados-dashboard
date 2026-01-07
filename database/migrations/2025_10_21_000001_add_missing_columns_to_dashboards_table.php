<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->string('type')->default('dashboard')->after('title');
            $table->string('platform_custom')->nullable()->after('platform');
            $table->text('description')->nullable()->after('type');
            $table->json('settings')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn(['type', 'platform_custom', 'description', 'settings']);
        });
    }
};
