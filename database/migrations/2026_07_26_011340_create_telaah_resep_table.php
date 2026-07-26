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
        Schema::create('telaah_resep', function (Blueprint $table) {
            $table->integer('telaah_resep_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_id')->nullable();
            $table->integer('telaah')->nullable();
            $table->string('berat_badan', 5)->nullable();
            $table->integer('kejelasan')->nullable();
            $table->string('ket_kejelasan', 150)->nullable();
            $table->integer('obat')->nullable();
            $table->string('ket_obat', 150)->nullable();
            $table->integer('dosis')->nullable();
            $table->string('ket_dosis', 150)->nullable();
            $table->integer('rute')->nullable();
            $table->string('ket_rute', 150)->nullable();
            $table->integer('waktu')->nullable();
            $table->string('ket_waktu', 150)->nullable();
            $table->integer('duplikasi')->nullable();
            $table->string('ket_duplikasi', 150)->nullable();
            $table->integer('alergi')->nullable();
            $table->string('ket_alergi', 150)->nullable();
            $table->integer('interaksi')->nullable();
            $table->string('ket_interaksi', 150)->nullable();
            $table->integer('bb_anak')->nullable();
            $table->string('ket_bb_anak', 150)->nullable();
            $table->integer('indikasi')->nullable();
            $table->string('ket_indikasi', 150)->nullable();
            $table->integer('peresepan_obat_dispense_head_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telaah_resep');
    }
};
