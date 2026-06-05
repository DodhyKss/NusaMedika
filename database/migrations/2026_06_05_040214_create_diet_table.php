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
        Schema::create('diet', function (Blueprint $table) {
            $table->integer('diet_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('bed_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->string('deskripsi')->nullable();
            $table->integer('registrasi_id')->nullable();

            $table->index(['diet_id', 'registrasi_detail_id', 'bed_id', 'pasien_id', 'registrasi_id'], 'diet_diet_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet');
    }
};
