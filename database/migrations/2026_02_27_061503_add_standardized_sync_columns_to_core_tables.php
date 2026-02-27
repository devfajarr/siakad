<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'mahasiswas' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'riwayat_pendidikans' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'mata_kuliahs' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'kurikulums' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'matkul_kurikulums' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'kelas_kuliah' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'dosen_pengajar_kelas_kuliah' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'peserta_kelas_kuliah' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
        ];

        foreach ($tables as $table => $columns) {
            Schema::table($table, function (Blueprint $tableObj) use ($columns, $table) {
                if (in_array('id_feeder', $columns) && !Schema::hasColumn($table, 'id_feeder')) {
                    $tableObj->uuid('id_feeder')->nullable()->index();
                }
                if (in_array('last_synced_at', $columns) && !Schema::hasColumn($table, 'last_synced_at')) {
                    $tableObj->timestamp('last_synced_at')->nullable();
                }
                if (in_array('sync_error_message', $columns) && !Schema::hasColumn($table, 'sync_error_message')) {
                    $tableObj->text('sync_error_message')->nullable();
                }
            });
        }

        // Data Migration (Copy from old columns to new columns for compatibility)
        // 1. Mahasiswas: last_sync -> last_synced_at
        if (Schema::hasColumn('mahasiswas', 'last_sync') && Schema::hasColumn('mahasiswas', 'last_synced_at')) {
            DB::table('mahasiswas')->whereNotNull('last_sync')->update([
                'last_synced_at' => DB::raw('last_sync')
            ]);
        }

        // 2. Riwayat Pendidikans: id_riwayat_pendidikan -> id_feeder, last_sync -> last_synced_at
        if (Schema::hasColumn('riwayat_pendidikans', 'id_riwayat_pendidikan') && Schema::hasColumn('riwayat_pendidikans', 'id_feeder')) {
            DB::table('riwayat_pendidikans')->whereNotNull('id_riwayat_pendidikan')->update([
                'id_feeder' => DB::raw('id_riwayat_pendidikan::uuid')
            ]);
        }
        if (Schema::hasColumn('riwayat_pendidikans', 'last_sync') && Schema::hasColumn('riwayat_pendidikans', 'last_synced_at')) {
            DB::table('riwayat_pendidikans')->whereNotNull('last_sync')->update([
                'last_synced_at' => DB::raw('last_sync')
            ]);
        }

        // 3. Mata Kuliahs: id_matkul -> id_feeder
        if (Schema::hasColumn('mata_kuliahs', 'id_matkul') && Schema::hasColumn('mata_kuliahs', 'id_feeder')) {
            DB::table('mata_kuliahs')->whereNotNull('id_matkul')->update([
                'id_feeder' => DB::raw('id_matkul::uuid')
            ]);
        }

        // 4. Kurikulums: id_kurikulum -> id_feeder
        if (Schema::hasColumn('kurikulums', 'id_kurikulum') && Schema::hasColumn('kurikulums', 'id_feeder')) {
            DB::table('kurikulums')->whereNotNull('id_kurikulum')->update([
                'id_feeder' => DB::raw('id_kurikulum::uuid')
            ]);
        }

        // 5. Matkul Kurikulums: error_message -> sync_error_message
        if (Schema::hasColumn('matkul_kurikulums', 'error_message') && Schema::hasColumn('matkul_kurikulums', 'sync_error_message')) {
            DB::table('matkul_kurikulums')->whereNotNull('error_message')->update([
                'sync_error_message' => DB::raw('error_message')
            ]);
        }

        // 6. Kelas Kuliah: id_kelas_kuliah -> id_feeder
        if (Schema::hasColumn('kelas_kuliah', 'id_kelas_kuliah') && Schema::hasColumn('kelas_kuliah', 'id_feeder')) {
            DB::table('kelas_kuliah')->whereNotNull('id_kelas_kuliah')->update([
                'id_feeder' => DB::raw('id_kelas_kuliah::uuid')
            ]);
        }

        // 7. Dosen Pengajar: id_aktivitas_mengajar -> id_feeder
        if (Schema::hasColumn('dosen_pengajar_kelas_kuliah', 'id_aktivitas_mengajar') && Schema::hasColumn('dosen_pengajar_kelas_kuliah', 'id_feeder')) {
            DB::table('dosen_pengajar_kelas_kuliah')->whereNotNull('id_aktivitas_mengajar')->update([
                'id_feeder' => DB::raw('id_aktivitas_mengajar::uuid')
            ]);
        }

        // 8. Peserta Kelas: external_id -> id_feeder
        if (Schema::hasColumn('peserta_kelas_kuliah', 'external_id') && Schema::hasColumn('peserta_kelas_kuliah', 'id_feeder')) {
            DB::table('peserta_kelas_kuliah')->whereNotNull('external_id')->update([
                'id_feeder' => DB::raw('external_id::uuid')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'mahasiswas' => ['last_synced_at'],
            'riwayat_pendidikans' => ['id_feeder', 'last_synced_at'],
            'mata_kuliahs' => ['id_feeder'],
            'kurikulums' => ['id_feeder'],
            'matkul_kurikulums' => ['id_feeder', 'last_synced_at', 'sync_error_message'],
            'kelas_kuliah' => ['id_feeder'],
            'dosen_pengajar_kelas_kuliah' => ['id_feeder'],
            'peserta_kelas_kuliah' => ['id_feeder'],
        ];

        foreach ($tables as $table => $columns) {
            Schema::table($table, function (Blueprint $tableObj) use ($columns, $table) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $tableObj->dropColumn($column);
                    }
                }
            });
        }
    }
};
