<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('ref_all_prodis')) {
            Schema::create('ref_all_prodis', function (Blueprint $table) {
                $table->uuid('id_prodi')->primary();
                $table->string('kode_program_studi', 10)->nullable();
                $table->string('nama_program_studi', 100);
                $table->char('status', 1)->nullable();
                $table->integer('id_jenjang_pendidikan')->nullable();
                $table->string('nama_jenjang_pendidikan', 50)->nullable();
                $table->uuid('id_perguruan_tinggi')->nullable();
                $table->string('kode_perguruan_tinggi', 10)->nullable();
                $table->string('nama_perguruan_tinggi', 80)->nullable();
                $table->timestamps();

                $table->index('id_perguruan_tinggi');
                $table->index('status');
                $table->index('nama_program_studi');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_all_prodis');
    }
};
