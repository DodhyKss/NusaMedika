<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Percakapan 1-1 antar user. user_a_id selalu < user_b_id supaya pasangan
        // (a,b) dan (b,a) tidak pernah membuat dua baris untuk obrolan yang sama.
        Schema::create('percakapan', function (Blueprint $table) {
            $table->increments('percakapan_id');
            $table->unsignedInteger('user_a_id');
            $table->unsignedInteger('user_b_id');
            $table->text('pesan_terakhir')->nullable();
            $table->timestamp('pesan_terakhir_at', 6)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->foreign('user_a_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('user_b_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->unique(['user_a_id', 'user_b_id'], 'percakapan_pasangan_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percakapan');
    }
};
