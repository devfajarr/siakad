<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change id_pembiayaan from UUID to String (Varchar)
        // because keys like "1", "2", "3" are not valid UUIDs.
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            // We use DB::statement to alter type because Schema::table->change() 
            // sometimes has issues with UUID to String casting in Postgres
            DB::statement('ALTER TABLE riwayat_pendidikans ALTER COLUMN id_pembiayaan TYPE VARCHAR(255) USING id_pembiayaan::varchar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            DB::statement('ALTER TABLE riwayat_pendidikans ALTER COLUMN id_pembiayaan TYPE UUID USING id_pembiayaan::uuid');
        });
    }
};
