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
        Schema::table('semesters', function (Blueprint $table) {
            // Check if column exists before adding (optional, but good practice if re-running)
            // But usually we just add new columns. 
            // Existing: id_semester, nama_semester, id_tahun_ajaran, timestamps
            // New: semester, a_periode_aktif, tanggal_mulai, tanggal_selesai

            if (!Schema::hasColumn('semesters', 'semester')) {
                $table->string('semester')->nullable();
            }
            if (!Schema::hasColumn('semesters', 'a_periode_aktif')) {
                $table->string('a_periode_aktif')->nullable(); // boolean or string? Feeder says '1'/'0'. Let's use string to be safe.
            }
            if (!Schema::hasColumn('semesters', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable();
            }
            if (!Schema::hasColumn('semesters', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn(['semester', 'a_periode_aktif', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
