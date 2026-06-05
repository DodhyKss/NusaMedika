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
        Schema::create('detail_tindakan_bedah', function (Blueprint $table) {
            $table->integer('detail_tindakan_bedah_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_tindakan_bedah')->nullable();
            $table->integer('jenis_tindakan')->nullable();

            $table->index(['detail_tindakan_bedah_id', 'jenis_tindakan', 'nama_tindakan_bedah'], 'detail_tindakan_bedah_detail_tindakan_bedah_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_tindakan_bedah');
    }
};
