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
        Schema::create('obat_bpjs', function (Blueprint $table) {
            $table->string('kodeobat', 20)->primary();
            $table->string('namaobat')->nullable();
            $table->integer('prb')->nullable();
            $table->integer('kronis')->nullable();
            $table->integer('kemo')->nullable();
            $table->decimal('harga', 18)->nullable();
            $table->text('restriksi')->nullable();
            $table->string('generik')->nullable();
            $table->string('aktif', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_bpjs');
    }
};
