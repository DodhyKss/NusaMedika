<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog Form Blanko (form generator) — prasyarat PRE-06 / form 129.
 *
 * Berbeda dengan tabel `form` / `objek` / `objek_form_control`, tabel ini
 * BUKAN bagian dari model EMR objek. Definisi field disimpan di tabel anak
 * `form_blanko_field`, sehingga satu baris = satu template form.
 *
 * Dual-driver: `increments()` (MySQL AUTO_INCREMENT / PG serial),
 * `$table->json()` (MySQL JSON / PG json), tidak memakai `jsonb`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_blanko', function (Blueprint $table) {
            $table->increments('form_blanko_id');

            // --- audit (tanpa created_at/updated_at, sesuai konvensi repo) ---
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            // --- identitas template ---
            $table->string('kode_blanko', 50)->unique();
            $table->string('nama_blanko', 150);
            $table->string('slug', 150)->unique();
            $table->text('deskripsi')->nullable();

            // --- hierarki (meniru dashboard_menu -> sub -> sub_extra) ---
            $table->integer('menu_induk')->nullable();
            $table->integer('sub_induk')->nullable();
            $table->integer('extra_induk')->nullable();

            // --- klasifikasi/kategori untuk filter katalog ---
            $table->string('kategori', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->smallInteger('is_aktif')->default(1);

            // --- cetak ---
            $table->string('ukuran_halaman', 20)->default('A4');
            $table->string('orientasi_halaman', 10)->default('portrait');
            $table->boolean('tampilkan_identitas_pasien')->default(true);
            $table->boolean('tampilkan_logo')->default(true);
            $table->text('kop_surat')->nullable();

            // --- adaptif (paralel filter di katalog_form_blanko.php legacy) ---
            // JSON berisi: kategori_umur[], jenis_kelamin[], jenis_rawat[], profesi_id[]
            $table->json('filter_adaptif')->nullable();

            // --- revisi definisi ---
            $table->smallInteger('versi')->default(1);
            $table->timestamp('publish_time', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_blanko');
    }
};
