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
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->timestamp('bypass_krs_until')->nullable()->after('user_id');
        });

        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->string('status_krs', 20)->default('paket')->after('id_registrasi_mahasiswa');
        });

        Schema::create('periode_krs', function (Blueprint $table) {
            $table->id();
            $table->string('id_semester', 255);
            $table->timestamp('tgl_mulai')->nullable();
            $table->timestamp('tgl_selesai')->nullable();
            $table->timestamps();

            $table->foreign('id_semester')->references('id_semester')->on('semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn('bypass_krs_until');
        });

        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->dropColumn('status_krs');
        });

        Schema::dropIfExists('periode_krs');
    }
};
