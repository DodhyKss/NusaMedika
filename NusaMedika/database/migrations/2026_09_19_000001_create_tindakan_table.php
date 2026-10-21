<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindakan', function (Blueprint $table) {
            $table->increments('tindakan_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->string('kode_tindakan', 20)->unique();
            $table->string('nama_tindakan', 255);
            $table->integer('bagian_id');
            $table->string('kategori', 20)->default('LAIN'); // LAB | RAD | LAIN
            $table->string('satuan_hasil', 20)->nullable();
            $table->string('nilai_normal', 100)->nullable();
            $table->string('kode_bpjs', 20)->nullable();
            $table->string('kode_inacbg', 20)->nullable();
            $table->string('kode_loinc', 20)->nullable();
            $table->text('keterangan')->nullable();

            $table->index('bagian_id');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindakan');
    }
};
