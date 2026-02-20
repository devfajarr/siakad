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
        // 1. Alat Transportasi
        Schema::create('alat_transportasi', function (Blueprint $table) {
            $table->string('id_alat_transportasi')->primary();
            $table->string('nama_alat_transportasi');
            $table->timestamps();
        });

        // 2. Jenis Tinggal
        Schema::create('jenis_tinggal', function (Blueprint $table) {
            $table->string('id_jenis_tinggal')->primary();
            $table->string('nama_jenis_tinggal');
            $table->timestamps();
        });

        // 3. Pekerjaan
        Schema::create('pekerjaan', function (Blueprint $table) {
            $table->string('id_pekerjaan')->primary();
            $table->string('nama_pekerjaan');
            $table->timestamps();
        });

        // 4. Penghasilan
        Schema::create('penghasilan', function (Blueprint $table) {
            $table->string('id_penghasilan')->primary();
            $table->string('nama_penghasilan');
            $table->timestamps();
        });

        // 5. Jenjang Pendidikan
        Schema::create('jenjang_pendidikan', function (Blueprint $table) {
            $table->string('id_jenjang_didik')->primary();
            $table->string('nama_jenjang_didik');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenjang_pendidikan');
        Schema::dropIfExists('penghasilan');
        Schema::dropIfExists('pekerjaan');
        Schema::dropIfExists('jenis_tinggal');
        Schema::dropIfExists('alat_transportasi');
    }
};
