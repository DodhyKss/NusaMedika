<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referensi_bagian', function (Blueprint $table) {
            $table->increments('referensi_bagian_id');
            $table->string('nama_referensi_bagian', 100)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
        });
    }

    public function down(): void
    {
        // Saat rollback penuh (migrate:refresh), down() migration rename
        // legacy (2026_09_05_000001) mengembalikan nama referensi_bagian_id,
        // jadi bersihkan kedua kemungkinan nama tabel.
        foreach (['referensi_bagian', 'referensi_bagian_id'] as $tabel) {
            if (Schema::hasTable($tabel)) {
                Schema::dropIfExists($tabel);
            }
        }
    }
};
