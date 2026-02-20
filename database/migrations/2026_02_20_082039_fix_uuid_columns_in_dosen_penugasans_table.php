<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL memerlukan raw SQL untuk mengubah tipe kolom dari string ke UUID
        // Menggunakan DB::statement untuk menghindari masalah casting

        // Ubah external_id dari string menjadi uuid
        DB::statement('ALTER TABLE dosen_penugasans ALTER COLUMN external_id TYPE UUID USING external_id::uuid');

        // Ubah id_prodi dari string menjadi uuid
        DB::statement('ALTER TABLE dosen_penugasans ALTER COLUMN id_prodi TYPE UUID USING id_prodi::uuid');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke string (varchar)
        DB::statement('ALTER TABLE dosen_penugasans ALTER COLUMN external_id TYPE VARCHAR(255) USING external_id::varchar');
        DB::statement('ALTER TABLE dosen_penugasans ALTER COLUMN id_prodi TYPE VARCHAR(255) USING id_prodi::varchar');
    }
};
