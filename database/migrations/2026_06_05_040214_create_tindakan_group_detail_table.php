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
        Schema::create('tindakan_group_detail', function (Blueprint $table) {
            $table->integer('tindakan_group_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tindakan_group_id')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->integer('number_urutan')->nullable();

            $table->index(['tindakan_group_detail_id', 'tindakan_group_id', 'tindakan_id'], 'idx_tindakan_group_detail01');
            $table->index(['tindakan_group_detail_id', 'tindakan_group_id', 'tindakan_id'], 'tindakan_group_detail_tindakan_group_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan_group_detail');
    }
};
