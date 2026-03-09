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
            $table->text('komentar')->nullable()->after('status');
        });

        // Ubah enum status tambah 'ditolak'
        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM('menunggu_verifikasi','terverifikasi','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi'");
    }

    public function down(): void
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('komentar');
        });

        DB::statement("ALTER TABLE dokumens MODIFY COLUMN status ENUM('menunggu_verifikasi','terverifikasi') NOT NULL DEFAULT 'menunggu_verifikasi'");
    }
};
