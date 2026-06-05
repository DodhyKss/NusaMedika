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
        Schema::create('zyx_file_upload_berkas', function (Blueprint $table) {
            $table->increments('zyx_file_upload_berkas_id');
            $table->integer('file_upload_berkas_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('relation_number', 100)->nullable();
            $table->string('kategori', 50)->nullable();
            $table->timestamp('tgl_upload_foto', 6)->nullable();
            $table->string('nama_file')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_file_upload_berkas');
    }
};
