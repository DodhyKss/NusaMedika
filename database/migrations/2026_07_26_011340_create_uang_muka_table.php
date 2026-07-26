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
        Schema::create('uang_muka', function (Blueprint $table) {
            $table->integer('uang_muka_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->decimal('deposit', 18)->nullable();
            $table->integer('tipe_bayar_id')->nullable();
            $table->string('status_deposit', 20)->nullable();
            $table->decimal('debit', 18)->nullable();
            $table->decimal('kredit', 18)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uang_muka');
    }
};
