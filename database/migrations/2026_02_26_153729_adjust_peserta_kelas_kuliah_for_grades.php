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
            // Nilai Angka (Precision 5,2 sesuai plan)
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'nilai_angka')) {
                $table->decimal('nilai_angka', 5, 2)->nullable()->comment('Nilai numerik 0-100');
            } else {
                $table->decimal('nilai_angka', 5, 2)->nullable()->change();
            }

            // Nilai Huruf
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'nilai_huruf')) {
                $table->string('nilai_huruf', 5)->nullable()->comment('A, B+, dll');
            } else {
                $table->string('nilai_huruf', 5)->nullable()->change();
            }

            // Nilai Indeks
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'nilai_indeks')) {
                $table->decimal('nilai_indeks', 4, 2)->nullable()->comment('Bobot indeks (0.00 - 4.00)');
            } else {
                $table->decimal('nilai_indeks', 4, 2)->nullable()->change();
            }

            // Sync Status
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'status_sinkronisasi')) {
                $table->string('status_sinkronisasi', 50)->default('created_local');
            }

            // External ID for Feeder mapping if not exists
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'external_id')) {
                $table->string('external_id')->nullable()->index();
            }

            // Audit
            if (!Schema::hasColumn('peserta_kelas_kuliah', 'last_push_at')) {
                $table->timestamp('last_push_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            // Biasanya tidak menghapus kolom di migration penyesuaian untuk menghindari data loss
            // Tapi jika diperlukan:
            // $table->dropColumn(['nilai_angka', 'nilai_huruf', 'nilai_indeks', 'status_sinkronisasi', 'external_id', 'last_push_at']);
        });
    }
};
