<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rekap_dpd_suaras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_id')->constrained('rekap_headers')->cascadeOnDelete();
            $table->foreignId('calon_id')->constrained('rekap_dpd_calons')->cascadeOnDelete();
            $table->unsignedInteger('suara')->default(0);
            $table->unique(['rekap_id', 'calon_id']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rekap_dpd_suaras'); }
};