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
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->uuid('id_perguruan_tinggi')->nullable()->after('tanggal_daftar');
            $table->uuid('id_prodi')->nullable()->after('id_perguruan_tinggi');
            $table->uuid('id_bidang_minat')->nullable()->after('id_prodi');
            $table->integer('sks_diakui')->nullable()->after('id_bidang_minat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn([
                'id_perguruan_tinggi',
                'id_prodi',
                'id_bidang_minat',
                'sks_diakui',
            ]);
        });
    }
};
