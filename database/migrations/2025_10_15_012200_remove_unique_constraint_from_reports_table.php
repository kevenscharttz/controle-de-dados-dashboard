<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Some drivers (SQLite) may not have the named index. For MySQL/Postgres
        // the index may also be missing if it was never created. Check for the
        // index first and drop it only when present to make this migration idempotent.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $index = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?", ['reports_organization_unique']);
            if (!empty($index)) {
                DB::statement('DROP INDEX "reports_organization_unique"');
            }
        } elseif ($driver === 'mysql') {
            // MySQL: check information_schema.statistics for the index name
            $index = DB::select("SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?", ['reports', 'reports_organization_unique']);
            if (!empty($index)) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->dropUnique('reports_organization_unique');
                });
            }
        } else {
            // Fallback for other DBs (Postgres, etc.) - attempt to drop but swallow
            // errors by checking pg_indexes on Postgres.
            if ($driver === 'pgsql') {
                $index = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", ['reports', 'reports_organization_unique']);
                if (!empty($index)) {
                    Schema::table('reports', function (Blueprint $table) {
                        $table->dropUnique('reports_organization_unique');
                    });
                }
            } else {
                // As a last resort, attempt to drop and ignore if the DB throws.
                try {
                    Schema::table('reports', function (Blueprint $table) {
                        $table->dropUnique('reports_organization_unique');
                    });
                } catch (\Exception $e) {
                    // ignore — we only want to ensure migrations continue in dev envs
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unique(['organization_id'], 'reports_organization_unique');
        });
    }
};
