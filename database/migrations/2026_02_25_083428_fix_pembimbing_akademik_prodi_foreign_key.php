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
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign(['id_prodi']);
            $table->foreign('id_prodi')->references('id_prodi')->on('program_studis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign(['id_prodi']);
            $table->foreign('id_prodi')->references('id')->on('ref_prodis')->onDelete('cascade');
        });
    }
};
