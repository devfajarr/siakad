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
        Schema::table('presensi_pertemuan', function (Blueprint $table) {
            $table->string('status_verifikasi')->default('pending')->after('metode_pembelajaran');
            $table->unsignedBigInteger('verified_by')->nullable()->after('status_verifikasi');
            $table->timestamp('verified_at')->nullable()->after('verified_by');

            // Optionally add a foreign key constraint for verified_by if it refers to users
            // $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_pertemuan', function (Blueprint $table) {
            // $table->dropForeign(['verified_by']);
            $table->dropColumn(['status_verifikasi', 'verified_by', 'verified_at']);
        });
    }
};
