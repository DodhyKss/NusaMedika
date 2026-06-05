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
        Schema::create('tim_bedah', function (Blueprint $table) {
            $table->integer('tim_bedah_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable()->index('tim_bedah_status_batal_idx');
            $table->string('shift', 10)->nullable()->index('tim_bedah_shift_idx');
            $table->string('nama_tim_bedah', 50)->nullable();
            $table->string('simbol_warna', 10)->nullable();
            $table->smallInteger('jenis_tindakan')->nullable();
            $table->integer('bagian_id')->nullable()->index('tim_bedah_bagian_id_idx');

            $table->index(['tim_bedah_id', 'shift', 'nama_tim_bedah', 'jenis_tindakan'], 'tim_bedah_tim_bedah_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_bedah');
    }
};
