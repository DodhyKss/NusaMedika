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
        Schema::create('spesial_person', function (Blueprint $table) {
            $table->integer('spesial_person_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->integer('karakter')->nullable();
            $table->string('keterangan', 100)->nullable();

            $table->index(['spesial_person_id', 'pasien_id'], 'idx_spesial_person01');
            $table->index(['spesial_person_id', 'pasien_id'], 'spesial_person_spesial_person_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spesial_person');
    }
};
