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
        Schema::create('kuisioner_jawaban_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_submission')->constrained('kuisioner_submissions')->onDelete('cascade');
            $table->foreignId('id_pertanyaan')->constrained('kuisioner_pertanyaans')->onDelete('cascade');
            $table->integer('jawaban_skala')->nullable(); // Value 1 - 5 target
            $table->text('jawaban_teks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuisioner_jawaban_details');
    }
};
