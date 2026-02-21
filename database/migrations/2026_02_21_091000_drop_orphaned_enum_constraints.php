<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Drop orphaned PostgreSQL CHECK constraints from old enum definitions.
     */
    public function up(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $tables = [
            'mata_kuliahs',
            'kurikulums',
            'matkul_kurikulums',
            'kelas_kuliah',
            'kelas_dosen',
            'peserta_kelas_kuliah',
        ];

        foreach ($tables as $table) {
            $this->dropCheckConstraint($table, 'status_sinkronisasi');
            $this->dropCheckConstraint($table, 'sumber_data');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding enums would require knowing the exact old values, 
        // and since we want to move away from enums, we leave them dropped.
    }

    /**
     * Drop a check constraint if it exists.
     */
    private function dropCheckConstraint(string $table, string $column): void
    {
        $constraintName = "{$table}_{$column}_check";

        try {
            // Check if constraint exists in pg_constraint
            $exists = DB::selectOne("
                SELECT 1 FROM pg_constraint 
                WHERE conname = ?
            ", [$constraintName]);

            if ($exists) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraintName}");
            }
        } catch (\Exception $e) {
            // Log error but continue
            Log::warning("Failed to drop constraint {$constraintName} on table {$table}: " . $e->getMessage());
        }
    }
};
