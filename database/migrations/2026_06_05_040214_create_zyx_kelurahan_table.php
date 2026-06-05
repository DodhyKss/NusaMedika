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
        Schema::create('zyx_kelurahan', function (Blueprint $table) {
            $table->increments('zyx_kelurahan_id');
            $table->bigInteger('kelurahan_id');
            $table->integer('kecamatan_id')->nullable();
            $table->string('nama_kelurahan')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_kelurahan');
    }
};
