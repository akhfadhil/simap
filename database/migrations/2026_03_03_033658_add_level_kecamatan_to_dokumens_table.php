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
        Schema::table('dokumens', function (Blueprint $table) {
            $table->enum('level', ['tps', 'kecamatan'])->default('tps')->after('jenis');
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->onDelete('cascade')->after('tps_id');
        });
    }

    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropColumn(['level', 'kecamatan_id']);
        });
    }
};
