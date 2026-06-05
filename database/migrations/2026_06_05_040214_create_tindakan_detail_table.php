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
        Schema::create('tindakan_detail', function (Blueprint $table) {
            $table->integer('tindakan_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->integer('status_batal')->nullable()->index('tindakan_detail_status_batal_idx');
            $table->integer('tindakan_id')->nullable()->index('tindakan_detail_tindakan_id_idx');
            $table->string('nama_tindakan_detail')->nullable();
            $table->integer('bagian_id')->nullable()->index('tindakan_detail_bagian_id_idx');
            $table->integer('profesi_id')->nullable()->index('tindakan_detail_profesi_id_idx');

            $table->index(['tindakan_detail_id', 'tindakan_id', 'bagian_id', 'profesi_id'], 'idx_tindakan_detail01');
            $table->index(['tindakan_detail_id', 'tindakan_id', 'bagian_id', 'profesi_id'], 'tindakan_detail_tindakan_detail_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan_detail');
    }
};
