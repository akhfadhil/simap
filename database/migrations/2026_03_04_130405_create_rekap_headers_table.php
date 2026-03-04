<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rekap_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tps_id')->constrained('tps')->cascadeOnDelete();
            $table->enum('jenis', ['ppwp', 'dpd', 'dpr_ri', 'dprd_prov', 'dprd_kab']);
            $table->unique(['tps_id', 'jenis']); // 1 rekap per jenis per TPS

            // I. Data Pemilih
            $table->unsignedInteger('dpt_lk')->default(0);
            $table->unsignedInteger('dpt_pr')->default(0);

            // I.B. Pengguna Hak Pilih
            $table->unsignedInteger('pengguna_dpt_lk')->default(0);
            $table->unsignedInteger('pengguna_dpt_pr')->default(0);
            $table->unsignedInteger('pengguna_dptb_lk')->default(0);
            $table->unsignedInteger('pengguna_dptb_pr')->default(0);
            $table->unsignedInteger('pengguna_dpk_lk')->default(0);
            $table->unsignedInteger('pengguna_dpk_pr')->default(0);

            // II. Data Surat Suara
            $table->unsignedInteger('ss_diterima')->default(0);
            $table->unsignedInteger('ss_digunakan')->default(0);
            $table->unsignedInteger('ss_rusak')->default(0);
            $table->unsignedInteger('ss_sisa')->default(0);

            // III. Disabilitas
            $table->unsignedInteger('disabilitas_lk')->default(0);
            $table->unsignedInteger('disabilitas_pr')->default(0);

            // V. Suara Sah & Tidak Sah
            $table->unsignedInteger('suara_tidak_sah')->default(0);

            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('difinalisasi_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rekap_headers'); }
};