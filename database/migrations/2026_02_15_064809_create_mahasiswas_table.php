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
        Schema::create('mahasiswas', function (Blueprint $table) {
            // Primary Key & Sync
            $table->id();
            $table->uuid('id_feeder')->nullable()->index();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_sync')->nullable();

            // Field Wajib (Mandatory - Not Null)
            $table->string('nama_mahasiswa', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 32);
            $table->date('tanggal_lahir');
            $table->integer('id_agama')->index();
            $table->string('nik', 16)->unique();
            $table->char('nisn', 10)->unique()->comment('Wajib 10 digit angka');
            $table->string('nama_ibu_kandung', 100);
            $table->char('kewarganegaraan', 2)->default('ID');
            $table->char('id_wilayah', 8)->comment('ID Kecamatan');
            $table->string('kelurahan', 60);
            $table->boolean('penerima_kps')->default(0);
            $table->string('handphone', 20);
            $table->string('email', 60)->unique();

            // Field Nullable (Opsional)
            $table->string('nomor_kps')->nullable();
            $table->string('npwp')->nullable();
            $table->string('jalan')->nullable();
            $table->string('dusun')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('telepon')->nullable();
            $table->integer('id_alat_transportasi')->nullable();
            $table->integer('id_jenis_tinggal')->nullable();

            // Field Kebutuhan Khusus (Nullable)
            $table->integer('id_kebutuhan_khusus_mahasiswa')->nullable();
            $table->integer('id_kebutuhan_khusus_ayah')->nullable();
            $table->integer('id_kebutuhan_khusus_ibu')->nullable();

            // Data Orang Tua - Ayah
            $table->string('nik_ayah', 16)->nullable();
            $table->string('nama_ayah')->nullable();
            $table->date('tgl_lahir_ayah')->nullable();
            $table->integer('id_pendidikan_ayah')->nullable();
            $table->integer('id_pekerjaan_ayah')->nullable();
            $table->integer('id_penghasilan_ayah')->nullable();

            // Data Orang Tua - Ibu
            $table->string('nik_ibu', 16)->nullable();
            $table->date('tgl_lahir_ibu')->nullable();
            $table->integer('id_pendidikan_ibu')->nullable();
            $table->integer('id_pekerjaan_ibu')->nullable();
            $table->integer('id_penghasilan_ibu')->nullable();

            // Data Wali
            $table->string('nama_wali')->nullable();
            $table->date('tgl_lahir_wali')->nullable();
            $table->integer('id_pendidikan_wali')->nullable();
            $table->integer('id_pekerjaan_wali')->nullable();
            $table->integer('id_penghasilan_wali')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
