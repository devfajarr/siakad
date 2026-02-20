<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('program_studis')) {
            Schema::create('program_studis', function (Blueprint $table) {
                $table->uuid('id_prodi')->primary();
                $table->string('kode_program_studi', 10)->nullable();
                $table->string('nama_program_studi', 100);
                $table->char('status', 1)->nullable();
                $table->integer('id_jenjang_pendidikan')->nullable();
                $table->string('nama_jenjang_pendidikan', 50)->nullable();
                $table->timestamps();

                $table->index('kode_program_studi');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studis');
    }
};
