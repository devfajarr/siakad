<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            // Rename columns to match API schema
            $table->renameColumn('sks_mk', 'sks_mata_kuliah');
            $table->renameColumn('wajib', 'apakah_wajib');
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            // Add missing sync columns
            $table->enum('sumber_data', ['server', 'lokal'])->default('server')->after('apakah_wajib');
            $table->timestamp('last_synced_at')->nullable()->after('is_deleted_server');
        });
    }

    public function down(): void
    {
        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->dropColumn(['sumber_data', 'last_synced_at']);
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->renameColumn('sks_mata_kuliah', 'sks_mk');
            $table->renameColumn('apakah_wajib', 'wajib');
        });
    }
};
