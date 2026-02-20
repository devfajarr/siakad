<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Replace pivot kelas_kuliah_dosen dengan tabel proper
     * dosen_pengajar_kelas_kuliah yang mendukung:
     * - Monitoring sinkronisasi standar
     * - Dosen alias (lokal → server) untuk push
     *
     * Mapping Feeder API:
     * - id_aktivitas_mengajar : UUID unik per record (response dari Get)
     * - id_registrasi_dosen   : dosen_penugasans.external_id (UUID)
     * - id_kelas_kuliah       : kelas_kuliah.id_kelas_kuliah (UUID)
     * - sks_substansi, rencana_minggu_pertemuan, realisasi_minggu_pertemuan
     */
    public function up(): void
    {
        // ─── Drop pivot table lama ──────────────────────────
        Schema::dropIfExists('kelas_kuliah_dosen');

        // ─── Buat tabel baru ────────────────────────────────
        Schema::create('dosen_pengajar_kelas_kuliah', function (Blueprint $table) {
            $table->id();

            // Server-generated primary key
            $table->uuid('id_aktivitas_mengajar')->nullable()->unique()
                ->comment('UUID dari Get/InsertDosenPengajarKelasKuliah');

            // ─── Foreign Keys ───────────────────────────────
            $table->uuid('id_kelas_kuliah')->index();
            $table->unsignedBigInteger('id_dosen')->index()
                ->comment('FK ke dosens.id (dosen lokal yang mengajar)');
            $table->uuid('id_registrasi_dosen')->nullable()->index()
                ->comment('= dosen_penugasans.external_id (UUID registrasi Feeder)');

            // ─── Dosen Alias (untuk push) ───────────────────
            // Dosen lokal (tanpa NIDN) yang mengajar secara nyata,
            // tapi saat push ke server menggunakan id_registrasi milik
            // dosen lain yang terdaftar di pusat.
            $table->unsignedBigInteger('id_dosen_alias')->nullable()->index()
                ->comment('FK ke dosens.id — dosen pusat sebagai alias saat push');
            $table->uuid('id_registrasi_dosen_alias')->nullable()
                ->comment('= dosen_penugasans.external_id milik dosen alias');

            // ─── Data Mengajar ──────────────────────────────
            $table->decimal('sks_substansi', 5, 2)->nullable();
            $table->integer('rencana_minggu_pertemuan')->nullable();
            $table->integer('realisasi_minggu_pertemuan')->nullable();
            $table->string('substansi_pilar')->nullable();

            // ─── Monitoring Sinkronisasi (standar) ──────────
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi', 50)->default('created_local')->index();
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_push_at')->nullable();
            $table->text('sync_error_message')->nullable();

            $table->timestamps();

            // ─── Constraints ────────────────────────────────
            $table->unique(['id_kelas_kuliah', 'id_dosen'], 'dpkk_kelas_dosen_unique');
            $table->foreign('id_dosen')->references('id')->on('dosens')->onDelete('cascade');
            $table->foreign('id_dosen_alias')->references('id')->on('dosens')->onDelete('set null');
        });
    }

    /**
     * Reverse: Recreate old pivot table.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_pengajar_kelas_kuliah');

        // Recreate old pivot
        Schema::create('kelas_kuliah_dosen', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_kelas_kuliah')->index();
            $table->unsignedBigInteger('id_dosen')->index();
            $table->uuid('external_id_registrasi')->nullable();
            $table->decimal('sks_substansi', 5, 2)->nullable();
            $table->integer('rencana_minggu_pertemuan')->nullable();
            $table->integer('realisasi_minggu_pertemuan')->nullable();
            $table->string('substansi_pilar')->nullable();
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->timestamps();
            $table->unique(['id_kelas_kuliah', 'id_dosen'], 'kk_dosen_unique');
            $table->foreign('id_dosen')->references('id')->on('dosens')->onDelete('cascade');
        });
    }
};
