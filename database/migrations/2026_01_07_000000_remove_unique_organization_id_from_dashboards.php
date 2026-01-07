<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            // Best-effort: drop unique index on organization_id so multiple dashboards per org are allowed
            try {
                $table->dropUnique(['organization_id']);
            } catch (\Throwable $e) {
                // ignore if index doesn't exist or DB doesn't support altering
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            try {
                $table->unique('organization_id');
            } catch (\Throwable $e) {
                // ignore if index already exists
            }
        });
    }
};
