<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Field milik satu template Form Blanko — prasyarat PRE-06 / form 129.
 *
 * Tidak memakai FK constraint ke `dashboard_menu*` / `form` / `objek`
 * karena posisinya opsional (form dapat berdiri sendiri sebagai dokumen
 * non-EMR, misalnya "Lembar Verifikasi Farmasi").
 *
 * `opsi` dan `validasi` disimpan sebagai JSON agar tipe kontrol baru tidak
 * memerlukan migration baru. Dual-driver: `json` (bukan `jsonb`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko_field', function (Blueprint $table) {
            $table->increments('form_blanko_field_id');

            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('form_blanko_id');

            // --- field ---
            $table->string('kode_field', 100);
            $table->string('label', 200);
            $table->text('keterangan')->nullable();
            $table->string('tipe_kontrol', 30);
            $table->smallInteger('urutan')->default(0);
            $table->string('grup', 100)->nullable();
            $table->boolean('wajib')->default(false);
            $table->smallInteger('lebar_kolom')->default(6);

            // --- aturan tampil bersyarat (adaptive) ---
            $table->string('depends_on_field', 100)->nullable();
            $table->string('depends_on_value', 100)->nullable();

            // --- JSON ---
            $table->json('opsi')->nullable();
            $table->json('validasi')->nullable();
            $table->json('setelan')->nullable();

            // Direferensikan juga ke objek EMR (opsional; boleh NULL).
            $table->integer('objek_id')->nullable();

            $table->index(['form_blanko_id', 'urutan']);
            $table->unique(['form_blanko_id', 'kode_field']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko_field');
    }
};
