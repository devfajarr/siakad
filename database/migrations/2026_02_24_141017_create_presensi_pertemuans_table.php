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
        Schema::create('presensi_pertemuan', function (Blueprint $table) {
            $table->id();
            // Relasi ke Kelas Kuliah (UUID sesuai pola sync project)
            $table->uuid('id_kelas_kuliah')->index();

            // Siapa yang mengisi presensi
            $table->unsignedBigInteger('id_dosen')->index();

            // Detail Pertemuan
            $table->smallInteger('pertemuan_ke');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->text('materi')->nullable();
            $table->string('metode_pembelajaran', 20)->default('Luring')->comment('Luring, Daring');

            // ─── Monitoring Sinkronisasi (Rules Poin 5) ─────────────
            $table->string('sumber_data', 20)->default('lokal');
            $table->string('status_sinkronisasi', 50)->default('created_local');
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_push_at')->nullable();
            $table->string('sync_action', 20)->default('insert');
            $table->boolean('is_local_change')->default(false);
            $table->boolean('is_deleted_local')->default(false);
            $table->text('sync_error_message')->nullable();

            $table->timestamps();

            // Constraint: Satu kelas hanya boleh punya satu entri per nomor pertemuan
            $table->unique(['id_kelas_kuliah', 'pertemuan_ke'], 'presensi_pertemuan_unique');

            // Foreign Key
            $table->foreign('id_dosen')->references('id')->on('dosens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_pertemuan');
    }
};
