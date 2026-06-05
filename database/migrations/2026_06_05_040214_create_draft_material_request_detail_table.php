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
        Schema::create('draft_material_request_detail', function (Blueprint $table) {
            $table->integer('draft_material_request_detail_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('draft_material_request_id')->nullable();
            $table->integer('bagian_id')->nullable();
            $table->integer('barang_id')->nullable();
            $table->integer('material_request_id')->nullable();
            $table->decimal('kuantitas', 18)->nullable();
            $table->decimal('kuantitas_acc_kainst', 18)->nullable();
            $table->decimal('kuantitas_acc_kasie', 18)->nullable();
            $table->text('group_permintaan_brg_detail_id')->nullable();
            $table->smallInteger('jenis_material_request_detail')->nullable()->comment('NULL = pembentukan MR melalui skema perencanaan
1 = pembentukan MR melalui draft manual
2 = pembentukan MR melalui draft manual CITO');
            $table->integer('kebutuhan_po')->nullable()->comment('1 = Reguler
2 = BPJS
3 = Fopi');
            $table->text('keterangan_batal')->nullable();

            $table->index(['draft_material_request_detail_id', 'bagian_id', 'barang_id', 'material_request_id'], 'draft_material_request_detail_draft_material_request_detail_id_');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_material_request_detail');
    }
};
