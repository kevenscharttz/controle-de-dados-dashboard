<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            // Try to drop old composite unique index if it exists.
            // Some DB drivers (sqlite) don't expose the Doctrine schema manager, so avoid calling it.
            try {
                $conn = Schema::getConnection();
                if (method_exists($conn, 'getDoctrineSchemaManager')) {
                    $sm = $conn->getDoctrineSchemaManager();
                    $indexes = array_map(fn($i) => $i->getName(), $sm->listTableIndexes('dashboards'));
                } else {
                    $indexes = [];
                }

                if (in_array('unique_dashboard_scope', $indexes, true)) {
                    $table->dropUnique('unique_dashboard_scope');
                }
            } catch (\Throwable $e) {
                // ignore - best effort drop
            }

            // Add individual unique indexes to enforce "max 1" per scope
            // Note: MySQL allows multiple NULLs in unique indexes, so unique on nullable columns
            // only triggers when a non-null value is present (which is what we want)
            try {
                $table->unique('organization_id');
            } catch (\Throwable $e) {
                // ignore if index already exists or DB doesn't support altering
            }

            try {
                $table->unique('scope_profile_id');
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $table->unique('scope_user_id');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboards', function (Blueprint $table) {
            if (Schema::hasColumn('dashboards', 'organization_id')) {
                $table->dropUnique(['organization_id']);
            }
            if (Schema::hasColumn('dashboards', 'scope_profile_id')) {
                $table->dropUnique(['scope_profile_id']);
            }
            if (Schema::hasColumn('dashboards', 'scope_user_id')) {
                $table->dropUnique(['scope_user_id']);
            }

            // restore old composite unique
            if (! Schema::hasColumn('dashboards', 'unique_dashboard_scope')) {
                $table->unique(['organization_id', 'scope_profile_id', 'scope_user_id'], 'unique_dashboard_scope');
            }
        });
    }
};
