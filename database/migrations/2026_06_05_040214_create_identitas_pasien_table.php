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
        Schema::create('identitas_pasien', function (Blueprint $table) {
            $table->integer('identitas_pasien_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->string('jenis_file', 25)->nullable();
            $table->string('nama_file', 150)->nullable();

            $table->index(['identitas_pasien_id', 'pasien_id'], 'identitas_pasien_identitas_pasien_id_idx');
            $table->index(['status_batal', 'jenis_file'], 'idx_identitas_pasien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identitas_pasien');
    }
};
