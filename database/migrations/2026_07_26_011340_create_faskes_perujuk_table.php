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
        Schema::create('faskes_perujuk', function (Blueprint $table) {
            $table->integer('id_perujuk');
            $table->string('nama_faskes')->nullable();
            $table->smallInteger('status_batal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faskes_perujuk');
    }
};
