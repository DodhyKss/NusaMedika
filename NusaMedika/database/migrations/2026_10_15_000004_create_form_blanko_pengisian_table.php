<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengisian Form Blanko — prasyarat PRE-06 / form 129.
 *
 * Satu baris = satu kali pengisian satu template Form Blanko untuk satu
 * registrasi_detail. Semua nilai field disimpan dalam satu kolom JSON
 * (`data`) berbentuk { "kode_field": nilai, ... }.
 *
 * Kolom `ringkasan` menyimpan salinan nilai field pertama yang terisi
 * (untuk kolom ringkasan pada tabel riwayat).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko_pengisian', function (Blueprint $table) {
            $table->increments('form_blanko_pengisian_id');

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('form_blanko_id');
            $table->integer('registrasi_detail_id');

            // Selalu JSON — dipakai MySQL dan PostgreSQL.
            $table->json('data');

            // Ringkasan tampilan riwayat (label pendek).
            $table->json('ringkasan')->nullable();

            // Jumlah field terisi — penyaringan cepat tanpa parse JSON.
            $table->smallInteger('jumlah_terisi')->default(0);

            $table->index(['form_blanko_id', 'registrasi_detail_id']);
            $table->index('registrasi_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko_pengisian');
    }
};
