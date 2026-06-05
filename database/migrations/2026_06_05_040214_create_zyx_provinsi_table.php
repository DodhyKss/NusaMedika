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
        Schema::create('zyx_provinsi', function (Blueprint $table) {
            $table->increments('zyx_provinsi_id');
            $table->integer('provinsi_id');
            $table->string('nama_provinsi')->nullable();
            $table->integer('status_batal')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_provinsi');
    }
};
