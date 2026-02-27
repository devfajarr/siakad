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
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->decimal('tugas1', 5, 2)->default(0)->after('riwayat_pendidikan_id');
            $table->decimal('tugas2', 5, 2)->default(0)->after('tugas1');
            $table->decimal('tugas3', 5, 2)->default(0)->after('tugas2');
            $table->decimal('tugas4', 5, 2)->default(0)->after('tugas3');
            $table->decimal('tugas5', 5, 2)->default(0)->after('tugas4');
            $table->decimal('aktif', 5, 2)->default(0)->after('tugas5');
            $table->decimal('etika', 5, 2)->default(0)->after('aktif');
            $table->decimal('uts', 5, 2)->default(0)->after('etika');
            $table->decimal('uas', 5, 2)->default(0)->after('uts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->dropColumn(['tugas1', 'tugas2', 'tugas3', 'tugas4', 'tugas5', 'aktif', 'etika', 'uts', 'uas']);
        });
    }
};
