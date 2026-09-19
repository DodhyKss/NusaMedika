<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_laboratorium_detail', function (Blueprint $table) {
            $table->increments('order_laboratorium_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('order_laboratorium_id');
            $table->integer('tindakan_id')->nullable();       // FK master (nullable utk data lama/custom)
            $table->string('nama_tindakan', 255);
            $table->decimal('harga', 12, 2)->default(0);
            $table->string('satuan_hasil', 20)->nullable();
            $table->string('nilai_normal', 100)->nullable();
            $table->text('hasil')->nullable();
            $table->smallInteger('flag_abnormal')->nullable(); // 0 normal, 1 abnormal
            $table->smallInteger('status')->default(0);        // 0 belum hasil, 1 selesai
            $table->integer('petugas_id')->nullable();

            $table->index('order_laboratorium_id');
            $table->index('tindakan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_laboratorium_detail');
    }
};
