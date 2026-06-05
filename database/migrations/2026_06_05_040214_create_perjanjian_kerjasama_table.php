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
        Schema::create('perjanjian_kerjasama', function (Blueprint $table) {
            $table->integer('perjanjian_kerjasama_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->string('jenis_pks', 20)->nullable();
            $table->timestamp('tgl_pks_awal', 6)->nullable();
            $table->timestamp('tgl_pks_akhir', 6)->nullable();
            $table->string('berkas_perjanjian', 150)->nullable();

            $table->index(['perjanjian_kerjasama_id', 'nasabah_id', 'supplier_id', 'tgl_pks_awal', 'jenis_pks', 'tgl_pks_akhir'], 'perjanjian_kerjasama_perjanjian_kerjasama_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjanjian_kerjasama');
    }
};
