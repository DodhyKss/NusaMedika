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
        Schema::create('jenis_kategori', function (Blueprint $table) {
            $table->integer('jenis_kategori_id');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_jenis_kategori');
            $table->integer('urutan_jenis_kategori')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_kategori');
    }
};
