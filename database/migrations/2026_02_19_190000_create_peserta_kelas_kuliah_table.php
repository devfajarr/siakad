<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel peserta_kelas_kuliah (acuan KRS).
     *
     * Mapping Feeder API:
     * - id_kelas_kuliah  → kelas_kuliah.id_kelas_kuliah (UUID)
     * - id_registrasi_mahasiswa → riwayat_pendidikans.id_riwayat_pendidikan (UUID)
     */
    public function up(): void
    {
        Schema::create('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->id();

            // ─── Foreign Keys (UUID, sesuai Feeder) ─────────────
            $table->uuid('id_kelas_kuliah')->index();
            $table->uuid('id_registrasi_mahasiswa')->index()->comment('= riwayat_pendidikans.id_riwayat_pendidikan');

            // ─── Data Nilai (dari GetPesertaKelasKuliah response) ──
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->decimal('nilai_indeks', 4, 2)->nullable();

            // ─── Monitoring Sinkronisasi (standar) ──────────────
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi', 50)->default('created_local')->index();
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_push_at')->nullable();
            $table->text('sync_error_message')->nullable();

            $table->timestamps();

            // ─── Constraints ────────────────────────────────────
            $table->unique(['id_kelas_kuliah', 'id_registrasi_mahasiswa'], 'peserta_kk_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_kelas_kuliah');
    }
};
