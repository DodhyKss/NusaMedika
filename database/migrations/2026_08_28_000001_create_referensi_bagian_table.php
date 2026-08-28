<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referensi_bagian_id', function (Blueprint $table) {
            $table->increments('referensi_bagian_id_id');
            $table->string('nama_referensi_bagian_id', 100)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referensi_bagian_id');
    }
};
