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
        // 1. Tambah kolom ke tabel utama (dosen_pengajar_kelas_kuliah)
        Schema::table('dosen_pengajar_kelas_kuliah', function (Blueprint $table) {
            // id_jenis_evaluasi (kecil/tiny)
            $table->string('jenis_evaluasi', 2)->nullable()->after('realisasi_minggu_pertemuan')
                ->comment('1: Eval Akademik, 2: Partisipatif, 3: Proyek, 4: Kognitif');

            // Dosen Alias (Honorer)
            $table->string('dosen_alias')->nullable()->after('jenis_evaluasi')
                ->comment('Nama/Alias dosen honorer (backup string)');

            // id_dosen_alias (Relasi ke dosens.id untuk Select2)
            $table->unsignedBigInteger('id_dosen_alias_lokal')->nullable()->after('dosen_alias')
                ->comment('FK ke dosens.id — dosen honorer/lokal untuk display');

            $table->foreign('id_dosen_alias_lokal')->references('id')->on('dosens')->onDelete('set null');

            // Kolom sync standar jika belum ada
            if (!Schema::hasColumn('dosen_pengajar_kelas_kuliah', 'sync_action')) {
                $table->string('sync_action', 20)->default('insert')->after('status_sinkronisasi');
            }
            if (!Schema::hasColumn('dosen_pengajar_kelas_kuliah', 'is_local_change')) {
                $table->boolean('is_local_change')->default(false)->after('sync_action');
            }
            if (!Schema::hasColumn('dosen_pengajar_kelas_kuliah', 'is_deleted_local')) {
                $table->boolean('is_deleted_local')->default(false)->after('is_local_change');
            }
        });

        // 2. Drop tabel lama (redundant)
        Schema::dropIfExists('kelas_dosen');
    }

    /**
     * Reverse: Recreate legacy table (minimal logic).
     */
    public function down(): void
    {
        Schema::table('dosen_pengajar_kelas_kuliah', function (Blueprint $table) {
            $table->dropForeign(['id_dosen_alias_lokal']);
            $table->dropColumn(['jenis_evaluasi', 'dosen_alias', 'id_dosen_alias_lokal']);
        });

        // Recreate legacy if needed (structurally)
        Schema::create('kelas_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
            $table->decimal('bobot_sks', 5, 2);
            $table->unsignedSmallInteger('jumlah_rencana_pertemuan');
            $table->unsignedSmallInteger('jumlah_realisasi_pertemuan')->nullable();
            $table->enum('jenis_evaluasi', ['1', '2', '3', '4']);
            $table->uuid('feeder_id')->nullable();
            $table->timestamps();
        });
    }
};
