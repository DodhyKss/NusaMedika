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
        Schema::create('form', function (Blueprint $table) {
            $table->integer('form_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_form', 100)->nullable();
            $table->string('id_dash_menu', 10)->nullable();
            $table->smallInteger('ri')->nullable();
            $table->smallInteger('rj')->nullable();
            $table->smallInteger('igd')->nullable();
            $table->smallInteger('mcu')->nullable();

            $table->index(['form_id', 'id_dash_menu', 'ri', 'rj', 'igd'], 'form_form_id_idx');
            $table->index(['form_id', 'id_dash_menu'], 'idx_form');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form');
    }
};
