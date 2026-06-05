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
        Schema::create('intervensi', function (Blueprint $table) {
            $table->integer('intervensi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('diagnosa_keperawatan_rj_id')->nullable();
            $table->string('kode_intervensi', 10)->nullable();
            $table->string('nama_intervensi', 100)->nullable();

            $table->index(['intervensi_id', 'kode_intervensi', 'diagnosa_keperawatan_rj_id'], 'intervensi_intervensi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervensi');
    }
};
