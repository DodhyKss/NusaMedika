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
        Schema::create('zyx_kecamatan', function (Blueprint $table) {
            $table->increments('zyx_kecamatan_id');
            $table->integer('kecamatan_id');
            $table->integer('kabupaten_id')->nullable();
            $table->string('nama_kecamatan')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_kecamatan');
    }
};
