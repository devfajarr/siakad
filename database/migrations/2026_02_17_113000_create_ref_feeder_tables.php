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
        // 1. Perguruan Tinggi Reference Table
        if (!Schema::hasTable('ref_perguruan_tinggis')) {
            Schema::create('ref_perguruan_tinggis', function (Blueprint $table) {
                $table->uuid('id')->primary(); // id_perguruan_tinggi from feeder
                $table->string('kode_perguruan_tinggi', 20)->nullable();
                $table->string('nama_perguruan_tinggi', 255);
                $table->timestamps();
            });
        }

        // 2. Program Studi Reference Table
        if (!Schema::hasTable('ref_prodis')) {
            Schema::create('ref_prodis', function (Blueprint $table) {
                // Primary Key (Feeder ID)
                $table->uuid('id')->primary();

                // Columns
                $table->string('kode_program_studi', 20)->nullable();
                $table->string('nama_program_studi', 255);
                $table->char('status', 1)->nullable();
                $table->integer('id_jenjang_pendidikan')->nullable();
                $table->string('nama_jenjang_pendidikan', 100)->nullable();
                $table->uuid('id_perguruan_tinggi');

                // Indexes
                $table->index('id_perguruan_tinggi');
                $table->index('nama_program_studi');

                // Foreign Key
                $table->foreign('id_perguruan_tinggi')
                    ->references('id')
                    ->on('ref_perguruan_tinggis')
                    ->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_prodis');
        Schema::dropIfExists('ref_perguruan_tinggis');
    }
};
