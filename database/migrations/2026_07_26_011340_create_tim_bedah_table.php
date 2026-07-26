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
        Schema::create('tim_bedah', function (Blueprint $table) {
            $table->integer('tim_bedah_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('shift', 10)->nullable();
            $table->string('nama_tim_bedah', 50)->nullable();
            $table->string('simbol_warna', 10)->nullable();
            $table->smallInteger('jenis_tindakan')->nullable();
            $table->integer('bagian_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_bedah');
    }
};
