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
        Schema::create('kuisioner_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kuisioner')->constrained('kuisioners')->onDelete('cascade');
            $table->foreignId('id_mahasiswa')->constrained('mahasiswas', 'id')->onDelete('cascade');
            $table->foreignId('id_kelas_kuliah')->nullable()->constrained('kelas_kuliah', 'id')->onDelete('cascade');
            $table->enum('status_sinkronisasi', ['synced', 'draft'])->default('synced');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuisioner_submissions');
    }
};
