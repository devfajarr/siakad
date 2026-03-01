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
        Schema::create('kuisioner_pertanyaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kuisioner')->constrained('kuisioners')->onDelete('cascade');
            $table->text('teks_pertanyaan');
            $table->enum('tipe_input', ['likert', 'pilihan_ganda', 'esai'])->default('likert');
            $table->json('opsi_jawaban')->nullable(); // For mapping multiple choices
            $table->integer('urutan')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuisioner_pertanyaans');
    }
};
