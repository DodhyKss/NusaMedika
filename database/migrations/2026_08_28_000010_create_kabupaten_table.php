<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->increments('kabupaten_id');
            $table->integer('provinsi_id')->nullable();
            $table->string('nama_kabupaten')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('kode_wilayah_kabupaten')->nullable();

            $table->index('provinsi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};
