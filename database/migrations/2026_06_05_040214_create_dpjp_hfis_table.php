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
        Schema::create('dpjp_hfis', function (Blueprint $table) {
            $table->integer('dpjp_hfis_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('dpjp_hfis_kode', 20)->nullable();
            $table->string('dpjp_hfis_nama')->nullable();

            $table->index(['dpjp_hfis_id', 'user_id', 'dpjp_hfis_kode'], 'dpjp_hfis_dpjp_hfis_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpjp_hfis');
    }
};
