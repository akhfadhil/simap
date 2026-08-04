<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partai_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nama');
            $table->string('nama_singkat');
            $table->string('logo_path')->nullable();
            $table->string('warna_utama')->nullable();
            $table->string('warna_aksen')->nullable();
            $table->integer('nomor_urut_aktif');
            $table->json('nomor_urut_historis_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (! Schema::hasColumn('users', 'partai_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('partai_profile_id')->nullable()->after('partai_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'partai_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('partai_profile_id');
            });
        }

        Schema::dropIfExists('partai_profiles');
    }
};
