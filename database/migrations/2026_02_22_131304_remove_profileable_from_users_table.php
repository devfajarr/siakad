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
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['profileable_type', 'profileable_id']);
            $table->dropColumn(['profileable_type', 'profileable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profileable_type')->nullable();
            $table->unsignedBigInteger('profileable_id')->nullable();
            $table->index(['profileable_type', 'profileable_id']);
        });
    }
};
