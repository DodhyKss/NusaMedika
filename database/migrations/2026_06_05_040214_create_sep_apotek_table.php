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
        Schema::create('sep_apotek', function (Blueprint $table) {
            $table->integer('sep_apotek_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('no_resep', 5)->nullable();
            $table->string('sep_apotek')->nullable();
            $table->string('sep_kunjungan')->nullable();
            $table->smallInteger('status_kirim')->nullable();
            $table->json('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sep_apotek');
    }
};
