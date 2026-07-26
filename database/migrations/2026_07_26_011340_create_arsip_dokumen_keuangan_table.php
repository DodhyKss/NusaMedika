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
        Schema::create('arsip_dokumen_keuangan', function (Blueprint $table) {
            $table->integer('arsip_dokumen_keuangan_id');
            $table->integer('input_user_id');
            $table->timestamp('input_time', 6);
            $table->integer('mod_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->text('file_path');
            $table->integer('jenis_dokumen');
            $table->string('nama_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumen_keuangan');
    }
};
