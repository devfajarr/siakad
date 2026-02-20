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
        Schema::create('ref_wilayah', function (Blueprint $table) {
            $table->string('id_wilayah')->primary();
            $table->string('nama_wilayah');
            $table->integer('id_level_wilayah');
            $table->string('id_induk_wilayah')->nullable()->index();
            $table->string('id_negara')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_wilayah');
    }
};
