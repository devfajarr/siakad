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
        Schema::create('kelas_dosen', function (Blueprint $table) {
            $table->id();

            // Relasi lokal
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();

            // Mapping dictionary InsertDosenPengajarKelasKuliah
            $table->decimal('bobot_sks', 5, 2)->comment('record[sks_substansi_total]');
            $table->unsignedSmallInteger('jumlah_rencana_pertemuan')->comment('record[rencana_minggu_pertemuan]');
            $table->unsignedSmallInteger('jumlah_realisasi_pertemuan')->nullable()->comment('record[realisasi_minggu_pertemuan]');
            $table->enum('jenis_evaluasi', ['1', '2', '3', '4'])->comment('record[id_jenis_evaluasi]');

            // ID dari server (response Insert/Get)
            $table->uuid('feeder_id')->nullable()->unique()->comment('data[id_aktivitas_mengajar]');

            // Monitoring sinkronisasi
            $table->enum('status_sinkronisasi', ['pending', 'synced', 'updated_local', 'deleted_local', 'failed'])
                ->default('pending')
                ->index();
            $table->enum('sync_action', ['insert', 'update', 'delete'])->default('insert')->index();
            $table->boolean('is_from_server')->default(false);
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_push_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->unique(['kelas_kuliah_id', 'dosen_id'], 'kelas_dosen_kelas_dosen_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_dosen');
    }
};
