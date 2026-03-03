<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->onDelete('set null');
            $table->foreignId('desa_id')->nullable()->constrained('desas')->onDelete('set null');
            $table->foreignId('tps_id')->nullable()->constrained('tps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropForeign(['desa_id']);
            $table->dropForeign(['tps_id']);
            $table->dropColumn(['kecamatan_id', 'desa_id', 'tps_id']);
        });
    }
};
