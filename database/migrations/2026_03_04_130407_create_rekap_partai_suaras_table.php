<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rekap_partai_suaras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekap_id')->constrained('rekap_headers')->cascadeOnDelete();
            $table->foreignId('partai_id')->constrained('rekap_partais')->cascadeOnDelete();
            $table->unsignedInteger('suara')->default(0);
            $table->unique(['rekap_id', 'partai_id']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rekap_partai_suaras'); }
};