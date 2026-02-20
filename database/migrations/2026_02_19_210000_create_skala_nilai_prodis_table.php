<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabel skala_nilai_prodis (referensi skala penilaian per prodi).
     *
     * Mapping Feeder API (GetDetailSkalaNilaiProdi):
     * - id_bobot_nilai       → UUID, primary key di server
     * - id_prodi             → UUID FK ke prodi
     * - nilai_huruf          → char(3), contoh: A, B+, B, C+, ...
     * - nilai_indeks         → numeric(4,2), contoh: 4.00, 3.50, ...
     * - bobot_minimum        → numeric(5,2), batas bawah nilai angka
     * - bobot_maksimum       → numeric(5,2), batas atas nilai angka
     * - tanggal_mulai_efektif → date, mulai berlaku
     * - tanggal_akhir_efektif → date, akhir berlaku
     */
    public function up(): void
    {
        Schema::create('skala_nilai_prodis', function (Blueprint $table) {
            $table->id();

            // ─── Server Primary Key ─────────────────────────────
            $table->uuid('id_bobot_nilai')->unique()->comment('PK dari server (id_bobot_nilai)');

            // ─── Foreign Key ────────────────────────────────────
            $table->uuid('id_prodi')->index()->comment('FK ke prodi di server');

            // ─── Data Skala Nilai ───────────────────────────────
            $table->string('nilai_huruf', 5)->comment('Nilai huruf: A, B+, B, C+, C, D, E');
            $table->decimal('nilai_indeks', 4, 2)->comment('Bobot indeks: 4.00, 3.50, ...');
            $table->decimal('bobot_minimum', 5, 2)->comment('Batas bawah nilai angka');
            $table->decimal('bobot_maksimum', 5, 2)->comment('Batas atas nilai angka');
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();

            // ─── Monitoring Sinkronisasi ────────────────────────
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // ─── Indexes ────────────────────────────────────────
            $table->index(['id_prodi', 'nilai_huruf'], 'skala_nilai_prodi_huruf_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skala_nilai_prodis');
    }
};
