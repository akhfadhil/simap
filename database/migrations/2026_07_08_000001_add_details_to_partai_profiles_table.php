<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partai_profiles', function (Blueprint $table) {
            $table->text('alamat_kantor')->nullable();
            $table->string('status_kantor')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('telp_ketua')->nullable();
            $table->string('nama_sekretaris')->nullable();
            $table->string('telp_sekretaris')->nullable();
            $table->string('nama_bendahara')->nullable();
            $table->string('telp_bendahara')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('partai_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'alamat_kantor',
                'status_kantor',
                'google_maps_url',
                'nama_ketua',
                'telp_ketua',
                'nama_sekretaris',
                'telp_sekretaris',
                'nama_bendahara',
                'telp_bendahara',
            ]);
        });
    }
};
