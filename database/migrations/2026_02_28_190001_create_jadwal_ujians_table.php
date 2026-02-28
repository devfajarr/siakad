<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal_ujians', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->string('id_semester', 5);
            $table->string('tipe_ujian'); // UTS / UAS
            $table->date('tanggal_ujian');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('tipe_waktu')->default('Universal'); // Pagi / Sore / Universal
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('kelas_kuliah_id')
                ->references('id')->on('kelas_kuliah')
                ->onDelete('cascade');

            $table->foreign('id_semester')
                ->references('id_semester')->on('semesters')
                ->onDelete('restrict');

            // Indexes
            $table->index('kelas_kuliah_id', 'idx_jadwal_ujians_kelas_kuliah_id');
            $table->index('id_semester', 'idx_jadwal_ujians_semester');

            // Satu kelas hanya punya 1 jadwal UTS dan 1 jadwal UAS
            $table->unique(['kelas_kuliah_id', 'tipe_ujian'], 'jadwal_ujians_kelas_tipe_unique');
        });

        // Check constraints (PostgreSQL)
        DB::statement("ALTER TABLE jadwal_ujians ADD CONSTRAINT jadwal_ujians_tipe_ujian_check CHECK (tipe_ujian::text = ANY (ARRAY['UTS'::varchar, 'UAS'::varchar]::text[]))");
        DB::statement("ALTER TABLE jadwal_ujians ADD CONSTRAINT jadwal_ujians_tipe_waktu_check CHECK (tipe_waktu::text = ANY (ARRAY['Pagi'::varchar, 'Sore'::varchar, 'Universal'::varchar]::text[]))");
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujians');
    }
};
