<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Status singkat ala "status" di aplikasi sosial: teks oleh user, boleh kedaluwarsa.
        Schema::create('status_sosial', function (Blueprint $table) {
            $table->increments('status_sosial_id');
            $table->unsignedInteger('user_id');
            $table->text('isi');
            // NULL = tidak pernah kedaluwarsa.
            $table->timestamp('tanggal_kadaraluarsa', 6)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'tanggal_kadaraluarsa'], 'status_sosial_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_sosial');
    }
};
