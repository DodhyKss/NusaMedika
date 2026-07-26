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
            $table->integer('tindakan_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->integer('status_batal')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->string('nama_tindakan_detail')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('profesi_id')->nullable();
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
