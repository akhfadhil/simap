<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('rekap_partais', function (Blueprint $table) {
            // ganti kolom jenis enum, tambah dprd_kab tetap ada tapi partai dikaitkan ke dapil
            $table->foreignId('dapil_id')->nullable()->constrained('dapils')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_partais', function (Blueprint $table) {
            //
        });
    }
};
