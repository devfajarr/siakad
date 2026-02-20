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
        Schema::create('kurikulums', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_kurikulum')->nullable()->unique()->index(); // Server Key
            $table->string('nama_kurikulum', 60);
            $table->uuid('id_prodi')->index();
            $table->string('id_semester', 5)->index(); // Foreign key to Semesters (usually char(5))
            $table->integer('jumlah_sks_lulus')->default(0);
            $table->integer('jumlah_sks_wajib')->default(0);
            $table->integer('jumlah_sks_pilihan')->default(0);

            // Monitoring Columns
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi', 50)->default('created_local'); // synced, created_local, updated_local, deleted_local, pending_push
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // Optional: Foreign Key Constraints if tables exist
            // $table->foreign('id_prodi')->references('id_prodi')->on('program_studis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulums');
    }
};
