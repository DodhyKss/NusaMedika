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
        Schema::create('lab_hasil_teks', function (Blueprint $table) {
            $table->string('no_lab', 15)->nullable();
            $table->string('kode_test', 10)->nullable();
            $table->string('teks', 3500)->nullable();
            $table->string('nama_pemeriksaan', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_hasil_teks');
    }
};
