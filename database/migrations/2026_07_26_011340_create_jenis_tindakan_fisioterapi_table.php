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
        Schema::create('jenis_tindakan_fisioterapi', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nama_jenis_tindakan')->nullable();
            $table->smallInteger('status_batal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tindakan_fisioterapi');
    }
};
