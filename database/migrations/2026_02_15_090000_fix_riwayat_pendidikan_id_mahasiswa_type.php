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
        // Since we cannot easily change UUID to BigInt if there's data (and we likely don't have dbal),
        // we will drop and re-add. Assuming table is empty or data is disposable during dev.
        // If critical, we would need a temporary column.

        // Postgres might complain about casting if we use 'change()', so raw SQL or drop/add is best.

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn('id_mahasiswa');
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            // Re-add as foreignId pointing to mahasiswas.id (BigInt)
            // Note: placement 'after' is not supported in Postgres via Schema builder easily without raw sql or modifying table creation.
            // But for functionality, position doesn't matter much.
            $table->foreignId('id_mahasiswa')->constrained('mahasiswas')->cascadeOnDelete()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropForeign(['id_mahasiswa']);
            $table->dropColumn('id_mahasiswa');
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->uuid('id_mahasiswa')->index();
        });
    }
};
