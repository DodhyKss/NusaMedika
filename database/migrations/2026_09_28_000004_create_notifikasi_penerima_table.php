<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu penerima. Inilah yang menyimpan status "sudah ditutup" per user,
        // sehingga notifikasi yang sama masih terlihat oleh user lain yang belum menutupnya.
        Schema::create('notifikasi_penerima', function (Blueprint $table) {
            $table->increments('notifikasi_penerima_id');
            // unsignedInteger wajib: users.user_id & notifikasi.notifikasi_id bertipe
            // int unsigned, dan MySQL menolak foreign key bila tipe kolom tidak sama.
            $table->unsignedInteger('notifikasi_id');
            $table->unsignedInteger('user_id');
            $table->smallInteger('is_read')->default(0);
            $table->timestamp('dibaca_time', 6)->nullable();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->foreign('notifikasi_id')->references('notifikasi_id')->on('notifikasi')->cascadeOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'is_read'], 'notifikasi_penerima_user_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_penerima');
    }
};
