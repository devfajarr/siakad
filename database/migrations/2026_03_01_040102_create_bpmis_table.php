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
        Schema::create('bpmis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->unique()->constrained('dosens', 'id')->onDelete('cascade');
            $table->string('sk_tugas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpmis');
    }
};
