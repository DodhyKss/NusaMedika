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
        Schema::create('terapi_obat_dosis', function (Blueprint $table) {
            $table->integer('terapi_obat_dosis_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('flag_stop')->nullable();
            $table->decimal('signa_1', 18)->nullable();
            $table->decimal('signa_2', 18)->nullable();
            $table->integer('status_perubahan')->nullable()->comment('1 = STOP
2 = PERUBAHAN SIGNA (ATURAN PAKAI)');
            $table->integer('jenis_obat')->nullable();
            $table->integer('user_id_pengedit')->nullable();
            $table->integer('user_id_dokter_instruksi')->nullable();

            $table->index(['terapi_obat_dosis_id', 'registrasi_detail_id', 'barang_id', 'user_id_pengedit', 'user_id_dokter_instruksi'], 'terapi_obat_dosis_terapi_obat_dosis_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terapi_obat_dosis');
    }
};
