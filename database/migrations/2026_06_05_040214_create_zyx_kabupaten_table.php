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
        Schema::create('zyx_kabupaten', function (Blueprint $table) {
            $table->increments('zyx_kabupaten_id');
            $table->integer('kabupaten_id');
            $table->integer('provinsi_id')->nullable();
            $table->string('nama_kabupaten')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_kabupaten');
    }
};
