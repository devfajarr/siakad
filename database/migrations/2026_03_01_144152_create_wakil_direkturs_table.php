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
        Schema::create('wakil_direkturs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens', 'id')->onDelete('cascade');
            $table->integer('tipe_wadir')->comment('1: Bidang Akademik, 2: Bidang Keuangan, 3: Bidang Kemahasiswaan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wakil_direkturs');
    }
};
