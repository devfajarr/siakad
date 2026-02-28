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
        Schema::create('pengaturan_ujians', function (Blueprint $table) {
            $table->id();
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id_semester')->on('semesters')->cascadeOnDelete();
            $table->enum('tipe_ujian', ['UTS', 'UAS']);
            $table->dateTime('tgl_mulai_cetak')->nullable();
            $table->dateTime('tgl_akhir_cetak')->nullable();
            $table->timestamps();

            // Kombinasi satu semester hanya boleh punya satu aturan UTS dan satu UAS
            $table->unique(['semester_id', 'tipe_ujian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_ujians');
    }
};
