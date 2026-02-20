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
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index(); // ID from Feeder
            $table->string('nidn')->nullable()->unique();
            $table->string('nip')->nullable();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->integer('id_agama')->nullable();
            $table->integer('id_status_aktif')->nullable();

            // Sync status
            $table->enum('status_sinkronisasi', ['pusat', 'lokal', 'tersinkronisasi'])->default('lokal');

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_struktural')->default(false);
            $table->boolean('is_pengajar')->default(true);

            $table->timestamps();
        });

        Schema::create('dosen_penugasans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('external_id')->nullable()->index();
            $table->string('id_tahun_ajaran')->nullable(); // Using string to be safe, e.g. '20231'
            $table->string('id_prodi')->nullable()->index();
            $table->string('jenis_penugasan')->nullable();
            $table->string('unit_penugasan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->enum('sumber_data', ['pusat', 'lokal'])->default('lokal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_penugasans');
        Schema::dropIfExists('dosens');
    }
};
