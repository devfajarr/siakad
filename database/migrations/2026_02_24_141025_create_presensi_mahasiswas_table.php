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
        Schema::create('presensi_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('presensi_pertemuan_id')->index();
            $table->unsignedBigInteger('riwayat_pendidikan_id')->index();

            // Status: H=Hadir, S=Sakit, I=Izin, A=Alfa
            $table->char('status_kehadiran', 1)->default('H');
            $table->string('keterangan')->nullable();

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

            // Constraint: Satu mahasiswa hanya satu status per pertemuan
            $table->unique(['presensi_pertemuan_id', 'riwayat_pendidikan_id'], 'presensi_mhs_unique');

            // Foreign Keys
            $table->foreign('presensi_pertemuan_id')->references('id')->on('presensi_pertemuan')->onDelete('cascade');
            $table->foreign('riwayat_pendidikan_id')->references('id')->on('riwayat_pendidikans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_mahasiswa');
    }
};
