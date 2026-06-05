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
        Schema::create('slot', function (Blueprint $table) {
            $table->integer('slot_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('pesan_slot_bedah_id')->nullable();
            $table->integer('slot_kamar_bedah')->nullable();
            $table->integer('tim_bedah_id')->nullable();
            $table->timestamp('tgl_jam_bedah_start', 6)->nullable();
            $table->timestamp('tgl_jam_bedah_finish', 6)->nullable();
            $table->timestamp('slot_time', 6)->nullable();
            $table->smallInteger('jenis_tindakan')->nullable();

            $table->index(['slot_id', 'tim_bedah_id', 'pesan_slot_bedah_id'], 'idx_slot01');
            $table->index(['slot_id', 'tim_bedah_id', 'pesan_slot_bedah_id', 'slot_kamar_bedah'], 'slot_slot_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot');
    }
};
