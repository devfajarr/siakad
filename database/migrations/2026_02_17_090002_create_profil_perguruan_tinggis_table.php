<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('profil_perguruan_tinggis')) {
            Schema::create('profil_perguruan_tinggis', function (Blueprint $table) {
                $table->uuid('id_perguruan_tinggi')->primary();
                $table->char('kode_perguruan_tinggi', 8)->nullable();
                $table->string('nama_perguruan_tinggi', 80)->nullable();
                $table->timestamps();

                $table->index('kode_perguruan_tinggi');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_perguruan_tinggis');
    }
};
