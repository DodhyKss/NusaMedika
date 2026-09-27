<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan', function (Blueprint $table) {
            $table->increments('pesan_id');
            $table->unsignedInteger('percakapan_id');
            $table->unsignedInteger('pengirim_id');
            $table->text('isi');
            // Waktu pesan dibaca oleh penerima (kosong = belum dibaca).
            $table->timestamp('dibaca_at', 6)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->foreign('percakapan_id')->references('percakapan_id')->on('percakapan')->cascadeOnDelete();
            $table->foreign('pengirim_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index('percakapan_id', 'pesan_percakapan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan');
    }
};
