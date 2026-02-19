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
        Schema::create('kelas_kuliah', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id')->nullable()->index()->comment('id_kelas_kuliah dari server');

            // Foreign Keys (UUID-based, sesuai pola tabel lain)
            $table->uuid('id_prodi')->nullable()->index();
            $table->string('id_semester', 5)->nullable()->index();
            $table->uuid('id_matkul')->nullable()->index();
            $table->uuid('id_kurikulum')->nullable()->index()->comment('Opsional, untuk asosiasi lokal');

            // Data Utama
            $table->string('nama_kelas_kuliah');
            $table->decimal('sks_mk', 5, 2)->default(0);
            $table->decimal('sks_tm', 5, 2)->default(0);
            $table->decimal('sks_prak', 5, 2)->default(0);
            $table->decimal('sks_prak_lap', 5, 2)->default(0);
            $table->decimal('sks_sim', 5, 2)->default(0);

            // Detail Perkuliahan
            $table->text('bahasan')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();
            $table->string('mode')->nullable()->comment('Mode perkuliahan');
            $table->string('lingkup')->nullable()->comment('Lingkup kelas');

            // PDDikti Flags
            $table->smallInteger('apa_untuk_pditt')->default(0);
            $table->smallInteger('a_selenggara_pditt')->default(0);
            $table->uuid('id_mou')->nullable();

            // ─── Monitoring Sinkronisasi ────────────────────────────
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi', 50)->default('created_local')->index();
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_push_at')->nullable();
            $table->text('sync_error_message')->nullable();

            $table->timestamps();
        });

        // ─── Pivot: Dosen Pengajar ─────────────────────────────
        Schema::create('kelas_kuliah_dosen', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_kelas_kuliah')->index()->comment('external_id kelas_kuliah');
            $table->unsignedBigInteger('id_dosen')->index();
            $table->uuid('external_id_registrasi')->nullable()->comment('ID registrasi dosen di Feeder');

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_kuliah_dosen');
        Schema::dropIfExists('kelas_kuliah');
    }
};
