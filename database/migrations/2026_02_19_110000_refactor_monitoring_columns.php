<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Refactor monitoring columns agar konsisten di semua tabel perkuliahan.
     * Standard: sumber_data, status_sinkronisasi, is_deleted_server,
     *           last_synced_at, last_push_at, sync_error_message
     */
    public function up(): void
    {
        // ─── 1. kelas_kuliah: rename external_id → id_kelas_kuliah ──
        Schema::table('kelas_kuliah', function (Blueprint $table) {
            $table->renameColumn('external_id', 'id_kelas_kuliah');
        });

        // ─── 2. mata_kuliahs: +last_push_at, +sync_error_message, -sync_version ──
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->timestamp('last_push_at')->nullable()->after('last_synced_at');
            $table->text('sync_error_message')->nullable()->after('last_push_at');
            $table->dropColumn('sync_version');
        });

        // ─── 3. kurikulums: +last_push_at, +sync_error_message ──
        Schema::table('kurikulums', function (Blueprint $table) {
            $table->timestamp('last_push_at')->nullable()->after('last_synced_at');
            $table->text('sync_error_message')->nullable()->after('last_push_at');
        });

        // ─── 4. matkul_kurikulums: +last_push_at, +sync_error_message ──
        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->timestamp('last_push_at')->nullable()->after('last_synced_at');
            $table->text('sync_error_message')->nullable()->after('last_push_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_kuliah', function (Blueprint $table) {
            $table->renameColumn('id_kelas_kuliah', 'external_id');
        });

        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->dropColumn(['last_push_at', 'sync_error_message']);
            $table->integer('sync_version')->nullable()->after('last_synced_at');
        });

        Schema::table('kurikulums', function (Blueprint $table) {
            $table->dropColumn(['last_push_at', 'sync_error_message']);
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->dropColumn(['last_push_at', 'sync_error_message']);
        });
    }
};
