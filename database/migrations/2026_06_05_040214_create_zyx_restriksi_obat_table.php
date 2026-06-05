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
        Schema::create('zyx_restriksi_obat', function (Blueprint $table) {
            $table->increments('zyx_restriksi_obat_id');
            $table->integer('restriksi_obat_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->text('restriksi')->nullable();
            $table->text('peresepan')->nullable();
            $table->text('saran')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
            $table->integer('jumlah')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_restriksi_obat');
    }
};
