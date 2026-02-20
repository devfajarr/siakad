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
            if (!Schema::hasColumn('mahasiswas', 'error_desc')) {
                $table->text('error_desc')->nullable()->after('last_sync');
            }
            // Update kebutuhan khusus defaults if possible. 
            // Changing default value doesn't affect existing nulls usually, unless we update them.
            // But strict schema requires default 0.
            $table->integer('id_kebutuhan_khusus_mahasiswa')->default(0)->change();
            $table->integer('id_kebutuhan_khusus_ayah')->default(0)->change();
            $table->integer('id_kebutuhan_khusus_ibu')->default(0)->change();
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            if (!Schema::hasColumn('riwayat_pendidikans', 'error_desc')) {
                $table->text('error_desc')->nullable()->after('last_sync');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn('error_desc');
            $table->integer('id_kebutuhan_khusus_mahasiswa')->nullable()->change();
            $table->integer('id_kebutuhan_khusus_ayah')->nullable()->change();
            $table->integer('id_kebutuhan_khusus_ibu')->nullable()->change();
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->dropColumn('error_desc');
        });
    }
};
