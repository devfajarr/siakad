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
        Schema::create('kaprodis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');
            $table->uuid('id_prodi')->unique(); // Unique: 1 Prodi = 1 Kaprodi Aktif
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studis')->onDelete('cascade');
            $table->string('sumber_data')->default('lokal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kaprodis');
    }
};
