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
        Schema::create('material_request_template_approved', function (Blueprint $table) {
            $table->integer('material_request_template_approved_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('jenis_konfigurasi', 10)->nullable();
            $table->string('kategori_barang', 20)->nullable();
            $table->integer('level_approval')->nullable();
            $table->string('level_jabatan', 50)->nullable();
            $table->string('client_id', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_request_template_approved');
    }
};
