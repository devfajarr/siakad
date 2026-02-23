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
        Schema::create('jadwal_kuliahs', function (Blueprint $table) {
            $table->id();
            // Foreign keys
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->onDelete('cascade');
            $table->foreignId('ruang_id')->constrained('ruangs')->onDelete('restrict');

            // Waktu & Detail
            $table->tinyInteger('hari')->comment('1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('jenis_pertemuan')->nullable()->comment('Teori, Praktikum, Tutorial, dll');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kuliahs');
    }
};
