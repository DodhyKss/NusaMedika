<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_radiologi_detail', function (Blueprint $table) {
            $table->increments('order_radiologi_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('order_radiologi_id');
            $table->integer('tindakan_id')->nullable();
            $table->string('nama_tindakan', 255);
            $table->decimal('harga', 12, 2)->default(0);
            $table->text('hasil')->nullable();                // kesan/deskripsi hasil
            $table->smallInteger('flag_abnormal')->nullable();
            $table->smallInteger('status')->default(0);
            $table->integer('petugas_id')->nullable();

            $table->index('order_radiologi_id');
            $table->index('tindakan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_radiologi_detail');
    }
};
