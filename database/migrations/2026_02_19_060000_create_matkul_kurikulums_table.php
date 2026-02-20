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
        Schema::create('matkul_kurikulums', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_kurikulum')->index();
            $table->uuid('id_matkul')->index();
            $table->integer('semester')->nullable();
            $table->decimal('sks_mk', 5, 2)->nullable();
            $table->decimal('sks_tatap_muka', 5, 2)->nullable();
            $table->decimal('sks_praktek', 5, 2)->nullable();
            $table->decimal('sks_praktek_lapangan', 5, 2)->nullable();
            $table->decimal('sks_simulasi', 5, 2)->nullable();
            $table->boolean('wajib')->default(true);

            // Sync Meta
            $table->string('status_sinkronisasi', 50)->default('synced');
            $table->boolean('is_deleted_server')->default(false);

            $table->timestamps();

            // Constraints (Logical only, not foreign key to avoid issues if parent not synced yet)
            // But good to index for performance
            $table->unique(['id_kurikulum', 'id_matkul'], 'id_kurikulum_matkul_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matkul_kurikulums');
    }
};
