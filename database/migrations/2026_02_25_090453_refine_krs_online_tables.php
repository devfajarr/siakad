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
        // 1. Refine krs_periods (rename and add columns)
        if (Schema::hasTable('periode_krs')) {
            Schema::rename('periode_krs', 'krs_periods');
        }

        Schema::table('krs_periods', function (Blueprint $table) {
            $table->string('nama_periode')->after('id_semester')->nullable();
            $table->boolean('is_active')->default(true)->after('tgl_selesai');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        });

        // 2. Refine peserta_kelas_kuliah
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'status_krs')) {
                $table->string('status_krs', 20)->default('paket')->after('riwayat_pendidikan_id');
            }
            $table->timestamp('last_acc_at')->nullable()->after('status_krs');
            $table->foreignId('acc_by')->nullable()->after('last_acc_at')->constrained('dosens')->onDelete('set null');
        });

        // 3. Auto-ACC Legacy Data (peserta_kelas_kuliah)
        \DB::table('peserta_kelas_kuliah')->update(['status_krs' => 'acc']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->dropForeign(['acc_by']);
            $table->dropColumn(['last_acc_at', 'acc_by', 'status_krs']);
        });

        Schema::table('krs_periods', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['nama_periode', 'is_active', 'created_by', 'updated_by']);
        });

        if (Schema::hasTable('krs_periods')) {
            Schema::rename('krs_periods', 'periode_krs');
        }
    }
};
