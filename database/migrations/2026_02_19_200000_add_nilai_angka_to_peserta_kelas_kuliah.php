<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tambah kolom nilai_angka ke peserta_kelas_kuliah.
     * Dibutuhkan oleh GetListNilaiPerkuliahanKelas / UpdateNilaiPerkuliahanKelas.
     */
    public function up(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->decimal('nilai_angka', 5, 2)->nullable()->after('id_registrasi_mahasiswa')
                ->comment('Nilai numerik 0-100 dari server');
        });
    }

    public function down(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->dropColumn('nilai_angka');
        });
    }
};
