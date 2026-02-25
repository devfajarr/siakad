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
            // Check if foreign key exists before dropping to avoid errors during re-migration
            $table->dropForeign(['id_mahasiswa']);
            $table->dropColumn('id_mahasiswa');

            $table->uuid('id_prodi')->after('id');
            $table->string('id_semester')->after('id_prodi');

            $table->foreign('id_prodi')->references('id')->on('ref_prodis')->onDelete('cascade');
            $table->foreign('id_semester')->references('id_semester')->on('semesters')->onDelete('cascade');

            $table->index(['id_prodi', 'id_semester', 'id_dosen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropIndex(['id_prodi', 'id_semester', 'id_dosen']);
            $table->dropForeign(['id_prodi']);
            $table->dropForeign(['id_semester']);
            $table->dropColumn(['id_prodi', 'id_semester']);

            $table->unsignedBigInteger('id_mahasiswa')->nullable()->after('id');
            $table->foreign('id_mahasiswa')->references('id')->on('mahasiswas')->onDelete('cascade');
            $table->unique('id_mahasiswa');
        });
    }
};
