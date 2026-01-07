<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('single_dashboard');
            $table->string('logo_alt_text')->nullable()->after('logo_url');
            $table->json('logo_settings')->nullable()->after('logo_alt_text');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'logo_alt_text', 'logo_settings']);
        });
    }
};
