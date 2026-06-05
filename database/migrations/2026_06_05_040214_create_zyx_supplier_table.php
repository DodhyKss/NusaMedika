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
        Schema::create('zyx_supplier', function (Blueprint $table) {
            $table->increments('zyx_supplier_id');
            $table->integer('supplier_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_supplier')->nullable();
            $table->string('telp_supplier', 100)->nullable();
            $table->string('email_supplier', 100)->nullable();
            $table->string('alamat_supplier')->nullable();
            $table->string('telp_supplier_2', 20)->nullable();
            $table->integer('tipe_supplier')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_supplier');
    }
};
