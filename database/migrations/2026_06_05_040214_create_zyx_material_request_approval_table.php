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
        Schema::create('zyx_material_request_approval', function (Blueprint $table) {
            $table->increments('zyx_material_request_approval_id');
            $table->integer('material_request_approval_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('kategori_barang_spesifik', 10)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('approval_level')->nullable();
            $table->integer('flag_edit')->nullable()->comment('1=Dapat Merubah QTY Per Unit Bagian;
2=Dapat Merubah QTY Per Unit Item');
            $table->integer('flag_pengadaan')->nullable();
            $table->string('text_level', 200)->nullable();
            $table->smallInteger('jenis_approval')->nullable()->comment('NULL = approval MR biasa
1 = approval MR cito');
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('material_request_template_approved_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_material_request_approval');
    }
};
