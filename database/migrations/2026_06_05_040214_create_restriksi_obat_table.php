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
        Schema::create('restriksi_obat', function (Blueprint $table) {
            $table->integer('restriksi_obat_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id')->nullable();
            $table->text('restriksi')->nullable();
            $table->text('peresepan')->nullable();
            $table->text('saran')->nullable();
            $table->integer('jumlah')->nullable();

            $table->index(['restriksi_obat_id', 'barang_id'], 'restriksi_obat_restriksi_obat_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restriksi_obat');
    }
};
