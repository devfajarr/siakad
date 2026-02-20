<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pembiayaans')) {
            Schema::create('pembiayaans', function (Blueprint $table) {
                $table->string('id_pembiayaan')->primary();
                $table->string('nama_pembiayaan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pembiayaans');
    }
};
