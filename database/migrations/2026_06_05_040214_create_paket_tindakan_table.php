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
        Schema::create('paket_tindakan', function (Blueprint $table) {
            $table->integer('paket_tindakan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('ref_tindakan_id')->nullable();
            $table->integer('tindakan_group_id')->nullable();

            $table->index(['paket_tindakan_id', 'tindakan_id', 'bagian_id', 'ref_tindakan_id', 'tindakan_group_id'], 'paket_tindakan_paket_tindakan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_tindakan');
    }
};
