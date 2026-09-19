<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindakan_harga', function (Blueprint $table) {
            $table->increments('tindakan_harga_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();

            $table->integer('tindakan_id');
            $table->integer('kelas_ruang_id')->nullable(); // NULL = tarif default
            $table->decimal('tarif', 12, 2)->default(0);
            $table->decimal('tarif_bpjs', 12, 2)->nullable();

            $table->unique(['tindakan_id', 'kelas_ruang_id']);
            $table->index('tindakan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindakan_harga');
    }
};
