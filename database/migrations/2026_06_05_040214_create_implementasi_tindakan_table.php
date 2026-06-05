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
        Schema::create('implementasi_tindakan', function (Blueprint $table) {
            $table->integer('implementasi_tindakan_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_implementasi_tindakan', 250)->nullable();
            $table->smallInteger('informasi')->nullable();
            $table->integer('tindakan_id')->nullable();

            $table->index(['implementasi_tindakan_id', 'tindakan_id'], 'implementasi_tindakan_implementasi_tindakan_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('implementasi_tindakan');
    }
};
