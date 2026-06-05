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
        Schema::create('zyx_draft_material_request', function (Blueprint $table) {
            $table->increments('zyx_draft_material_request_id');
            $table->integer('draft_material_request_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('acc_kainst_user_id')->nullable();
            $table->timestamp('tgl_acc_kainst', 6)->nullable();
            $table->integer('acc_kasie_user_id')->nullable();
            $table->timestamp('tgl_acc_kasie', 6)->nullable();
            $table->smallInteger('flag_mr_cito')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('kebutuhan_po')->nullable();
            $table->string('kategori_barang', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_draft_material_request');
    }
};
