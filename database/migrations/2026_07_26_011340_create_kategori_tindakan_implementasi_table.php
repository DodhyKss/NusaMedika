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
        Schema::create('kategori_tindakan_implementasi', function (Blueprint $table) {
            $table->integer('kategori_tindakan_implementasi_id');
            $table->integer('input_user_id')->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_kategori');
            $table->integer('jenis_kategori_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_tindakan_implementasi');
    }
};
