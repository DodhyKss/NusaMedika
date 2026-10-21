<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_topik', function (Blueprint $table) {
            $table->increments('topik_id');
            $table->string('judul', 200);
            $table->text('isi');
            // PUBLIK = semua user, PROFESI = satu profesi, BAGIAN = satu bagian.
            // target_id berisi profesi_id / bagian_id sesuai tipe (NULL untuk PUBLIK).
            $table->string('tipe', 20)->default('PUBLIK');
            $table->integer('target_id')->nullable();
            $table->unsignedInteger('user_id');
            // Dipakai untuk mengurutkan: topik dengan balasan terbaru naik ke atas.
            $table->timestamp('terakhir_aktif_at', 6)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['tipe', 'target_id'], 'forum_topik_target_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topik');
    }
};
