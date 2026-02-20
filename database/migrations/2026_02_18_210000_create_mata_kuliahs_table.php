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
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_matkul')->nullable()->index()->comment('Server UUID');
            $table->uuid('id_prodi')->nullable()->index();
            $table->string('kode_mk')->index()->unique();
            $table->string('nama_mk');
            $table->decimal('sks', 5, 2)->default(0);
            $table->decimal('sks_tatap_muka', 5, 2)->default(0);
            $table->decimal('sks_praktek', 5, 2)->default(0);
            $table->decimal('sks_praktek_lapangan', 5, 2)->default(0);
            $table->decimal('sks_simulasi', 5, 2)->default(0);
            $table->string('metode_kuliah')->nullable();
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();
            $table->string('jenis_mk')->nullable();
            $table->string('kelompok_mk')->nullable();
            $table->integer('semester')->nullable();
            $table->boolean('status_aktif')->default(true);

            // Monitoring Columns
            $table->enum('sumber_data', ['server', 'lokal'])->default('server');
            $table->enum('status_sinkronisasi', [
                'synced',
                'created_local',
                'updated_local',
                'deleted_local',
                'pending_push'
            ])->default('synced')->index();
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->integer('sync_version')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliahs');
    }
};
