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
        Schema::create('zyx_bank', function (Blueprint $table) {
            $table->increments('zyx_bank_id');
            $table->integer('bank_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_bank', 20)->nullable();
            $table->string('no_rekening', 25)->nullable();
            $table->string('nama_pemilik', 100)->nullable();
            $table->string('no_kartu', 20)->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('faktur')->nullable();
            $table->integer('pembayaran')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_bank');
    }
};
