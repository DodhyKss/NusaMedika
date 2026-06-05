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
        Schema::create('zyx_material_request', function (Blueprint $table) {
            $table->increments('zyx_material_request_id');
            $table->integer('material_request_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('acc_wadir_user_id')->nullable();
            $table->timestamp('tgl_acc_wadir', 6)->nullable();
            $table->integer('acc_ppk_user_id')->nullable();
            $table->timestamp('tgl_acc_ppk', 6)->nullable();
            $table->integer('draft_material_request_id')->nullable();
            $table->smallInteger('flag_mr_cito')->nullable()->comment('1 = material request yang dibentuk dari material request CITO');
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_material_request');
    }
};
