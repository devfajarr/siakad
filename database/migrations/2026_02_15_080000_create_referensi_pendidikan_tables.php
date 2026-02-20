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
        Schema::create('jenis_daftars', function (Blueprint $table) {
            $table->decimal('id_jenis_daftar', 2, 0)->primary(); // Feeder uses numeric IDs for this usually (1, 2, etc.) but char is safe. Let's check dictionary or assume string/decimal. Feeder docs usually say '1', '2'. 
            // Previous analysis showed id_jenis_daftar as char(2). Let's stick to string to be safe or decimal if sure. 
            // For now, string is safest. 
            // Wait, previous migration used char(2). Let's use string.
        });
        Schema::dropIfExists('jenis_daftars');

        Schema::create('jenis_daftars', function (Blueprint $table) {
            $table->string('id_jenis_daftar')->primary();
            $table->string('nama_jenis_daftar');
            $table->timestamps();
        });

        Schema::create('jalur_pendaftarans', function (Blueprint $table) {
            $table->string('id_jalur_daftar')->primary(); // often numeric but string is safe
            $table->string('nama_jalur_daftar');
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->string('id_semester')->primary(); // 20231, 20232
            $table->string('nama_semester');
            $table->string('id_tahun_ajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('jalur_pendaftarans');
        Schema::dropIfExists('jenis_daftars');
    }
};
