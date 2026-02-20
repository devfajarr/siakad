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
        Schema::create('agama', function (Blueprint $table) {
            $table->string('id_agama')->primary();
            $table->string('nama_agama');
            $table->timestamps();
        });

        Schema::create('negara', function (Blueprint $table) {
            $table->string('id_negara')->primary();
            $table->string('nama_negara');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negara');
        Schema::dropIfExists('agama');
    }
};
