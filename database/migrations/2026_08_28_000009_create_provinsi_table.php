<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinsi', function (Blueprint $table) {
            $table->increments('provinsi_id');
            $table->string('nama_provinsi')->nullable();
            $table->integer('status_batal')->nullable();
            $table->integer('kode_wilayah_provinsi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinsi');
    }
};
