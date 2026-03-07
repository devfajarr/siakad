<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('konten');
            $table->enum('kategori', ['krs', 'kuisioner', 'ujian', 'jadwal', 'umum'])->default('umum');
            $table->string('icon')->nullable();
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'tgl_mulai', 'tgl_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
    }
};
