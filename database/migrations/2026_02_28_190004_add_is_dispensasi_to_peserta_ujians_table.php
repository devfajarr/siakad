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
        Schema::table('peserta_ujians', function (Blueprint $table) {
            $table->boolean('is_dispensasi')->default(false)->after('is_eligible')
                ->comment('True jika mahasiswa tidak eligible secara sistem tapi mendapat dispensasi akademik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_ujians', function (Blueprint $table) {
            $table->dropColumn('is_dispensasi');
        });
    }
};
