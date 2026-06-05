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
        Schema::create('hasil_lab_master', function (Blueprint $table) {
            $table->integer('hasil_lab_master_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('nilai_rujukan', 100)->nullable();
            $table->string('unit', 50)->nullable();

            $table->index(['hasil_lab_master_id', 'tindakan_id', 'unit'], 'hasil_lab_master_hasil_lab_master_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_lab_master');
    }
};
