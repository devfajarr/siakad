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
        // 1. Riwayat Fungsional
        Schema::create('dosen_riwayat_fungsionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('jabatan_fungsional');
            $table->string('sk_nomor')->nullable();
            $table->date('sk_tanggal')->nullable();
            $table->date('tmt_jabatan')->nullable();
            $table->timestamps();
        });

        // 2. Riwayat Pangkat
        Schema::create('dosen_riwayat_pangkats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('pangkat_golongan');
            $table->string('sk_nomor')->nullable();
            $table->date('sk_tanggal')->nullable();
            $table->date('tmt_pangkat')->nullable();
            $table->timestamps();
        });

        // 3. Riwayat Pendidikan
        Schema::create('dosen_riwayat_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('jenjang_pendidikan');
            $table->string('gelar_akademik')->nullable();
            $table->string('perguruan_tinggi')->nullable();
            $table->string('program_studi')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('sk_penyetaraan')->nullable(); // Fixed typo
            $table->date('tanggal_ijazah')->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->timestamps();
        });

        // 4. Riwayat Sertifikasi
        Schema::create('dosen_riwayat_sertifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('jenis_sertifikasi')->nullable();
            $table->string('nomor_sertifikasi')->nullable();
            $table->year('tahun_sertifikasi')->nullable();
            $table->string('bidang_studi')->nullable();
            $table->timestamps();
        });

        // 5. Riwayat Penelitian
        Schema::create('dosen_riwayat_penelitians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('judul_penelitian');
            $table->string('kategori_kegiatan')->nullable(); // e.g. Penelitian Monodisiplin
            $table->string('kelompok_bidang')->nullable();
            $table->string('lembaga_iptek')->nullable();
            $table->year('tahun_kegiatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_riwayat_penelitians');
        Schema::dropIfExists('dosen_riwayat_sertifikasis');
        Schema::dropIfExists('dosen_riwayat_pendidikans');
        Schema::dropIfExists('dosen_riwayat_pangkats');
        Schema::dropIfExists('dosen_riwayat_fungsionals');
    }
};
