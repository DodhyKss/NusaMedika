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
        Schema::create('tim_bedah_detail', function (Blueprint $table) {
            $table->integer('tim_bedah_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tim_bedah_id')->nullable()->index('tim_bedah_detail_tim_bedah_id_idx');
            $table->timestamp('tanggal_tugas', 6)->nullable()->index('tim_bedah_detail_tanggal_tugas_idx');
            $table->integer('user_id')->nullable()->index('tim_bedah_detail_user_id_idx');

            $table->index(['tim_bedah_detail_id', 'tim_bedah_id', 'user_id', 'tanggal_tugas'], 'idx_tim_bedah_detail01');
            $table->index(['tim_bedah_detail_id', 'tim_bedah_id', 'tanggal_tugas', 'user_id'], 'tim_bedah_detail_tim_bedah_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_bedah_detail');
    }
};
