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
        Schema::create('zyx_implementasi_tindakan', function (Blueprint $table) {
            $table->increments('zyx_implementasi_tindakan_id');
            $table->integer('implementasi_tindakan_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_implementasi_tindakan', 250)->nullable();
            $table->smallInteger('informasi')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('tindakan_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_implementasi_tindakan');
    }
};
