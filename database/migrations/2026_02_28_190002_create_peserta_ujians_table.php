<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peserta_ujians', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('jadwal_ujian_id');
            $table->unsignedBigInteger('peserta_kelas_kuliah_id');

            // Eligibility snapshot
            $table->boolean('is_eligible')->default(false);
            $table->string('keterangan_tidak_layak', 255)->nullable();
            $table->decimal('persentase_kehadiran', 5, 2)->default(0);
            $table->integer('jumlah_hadir')->default(0);

            // Alur cetak kartu ujian
            $table->string('status_cetak')->default('belum'); // belum / diminta / dicetak
            $table->timestamp('diminta_pada')->nullable();
            $table->timestamp('dicetak_pada')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('jadwal_ujian_id')
                ->references('id')->on('jadwal_ujians')
                ->onDelete('cascade');

            $table->foreign('peserta_kelas_kuliah_id')
                ->references('id')->on('peserta_kelas_kuliah')
                ->onDelete('cascade');

            // Indexes
            $table->index('jadwal_ujian_id', 'idx_peserta_ujians_jadwal_ujian_id');
            $table->index('status_cetak', 'idx_peserta_ujians_status_cetak');

            // Satu peserta hanya 1x per ujian
            $table->unique(
                ['jadwal_ujian_id', 'peserta_kelas_kuliah_id'],
                'peserta_ujians_jadwal_peserta_unique'
            );
        });

        // Check constraint (PostgreSQL)
        DB::statement("ALTER TABLE peserta_ujians ADD CONSTRAINT peserta_ujians_status_cetak_check CHECK (status_cetak::text = ANY (ARRAY['belum'::varchar, 'diminta'::varchar, 'dicetak'::varchar]::text[]))");
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_ujians');
    }
};
