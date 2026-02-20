<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_riwayat_pendidikan')->nullable()->index(); // id_registrasi_mahasiswa from Feeder
            $table->uuid('id_mahasiswa')->index(); // Foreign key to local mahasiswa table? Or Feeder UUID? Let's use string/uuid
            $table->char('nim', 24);
            $table->char('id_jenis_daftar', 2); // 1 = Peserta didik baru, 2 = Pindahan, etc.
            $table->char('id_jalur_daftar', 4)->nullable();
            $table->char('id_periode_masuk', 5); // 20231
            $table->date('tanggal_daftar');
            $table->char('id_jenis_keluar', 1)->nullable(); // C, D, L, M, K, N, G, X
            $table->date('tanggal_keluar')->nullable();
            $table->string('keterangan_keluar')->nullable();
            $table->string('nomor_sk_yudisium')->nullable();
            $table->date('tanggal_sk_yudisium')->nullable();
            $table->string('nomor_ijazah')->nullable();

            // Perguruan Tinggi Asal (Untuk Pindahan)
            $table->uuid('id_perguruan_tinggi_asal')->nullable();
            $table->uuid('id_prodi_asal')->nullable();

            $table->uuid('id_pembiayaan')->nullable(); // Mandiri, Beasiswa, dll
            $table->decimal('biaya_masuk', 16, 2)->nullable();

            // Sync status
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_sync')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikans');
    }
};
