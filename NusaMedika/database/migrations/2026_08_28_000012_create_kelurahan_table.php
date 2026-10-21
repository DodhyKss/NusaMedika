<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan', function (Blueprint $table) {
            $table->increments('kelurahan_id');
            $table->integer('kecamatan_id')->nullable();
            $table->string('nama_kelurahan')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->bigInteger('kode_wilayah_kelurahan')->nullable();

            $table->index('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan');
    }
};
