<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peresepan_obat_detail', function (Blueprint $table) {
            $table->increments('peresepan_obat_detail_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('peresepan_obat_id');
            $table->integer('barang_id');
            $table->decimal('jumlah', 12, 2)->default(0);
            $table->string('s_1', 30)->nullable()->comment('signatura 1, misal 3x sehari');
            $table->string('s_2', 30)->nullable()->comment('signatura 2, misal 1 tablet');
            $table->string('aturan_pakai', 255)->nullable();
            $table->string('rute_pemberian', 50)->nullable();
            $table->smallInteger('flag_dispense')->nullable()->default(0)->comment('0 = belum, 1 = sudah didispense');
            $table->decimal('jumlah_dispense', 12, 2)->nullable()->default(0);
            $table->string('no_batch', 50)->nullable();
            $table->integer('bagian_dispense_id')->nullable()->comment('depo tempat obat dikeluarkan');
            $table->timestamp('waktu_dispense', 6)->nullable();

            $table->index('peresepan_obat_detail_id');
            $table->index('peresepan_obat_id');
            $table->index('barang_id');
            $table->index('flag_dispense');
            $table->index('bagian_dispense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peresepan_obat_detail');
    }
};
