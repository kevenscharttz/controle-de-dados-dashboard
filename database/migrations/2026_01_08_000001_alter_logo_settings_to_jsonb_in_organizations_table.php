<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Apenas para PostgreSQL: altera a coluna de JSON para JSONB, 
        // evitando o erro "could not identify an equality operator for type json"
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE organizations ALTER COLUMN logo_settings TYPE jsonb USING logo_settings::jsonb');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE organizations ALTER COLUMN logo_settings TYPE json USING logo_settings::json');
        }
    }
};
