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
        Schema::create('index_remunerasi', function (Blueprint $table) {
            $table->integer('index_remunerasi_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('schema_index', 5)->nullable();
            $table->string('index_interfensi', 25)->nullable();
            $table->string('index_interfensi_rules', 25)->nullable();
            $table->integer('group_index')->nullable();
            $table->string('group_index_deskripsi', 155)->nullable();
            $table->string('kategori_index', 50)->nullable();
            $table->string('kategori_index_deskripsi', 155)->nullable();
            $table->decimal('point_index', 18)->nullable();
            $table->string('logic_rules', 10)->nullable();

            $table->index(['index_remunerasi_id', 'schema_index', 'index_interfensi', 'index_interfensi_rules'], 'index_remunerasi_index_remunerasi_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('index_remunerasi');
    }
};
