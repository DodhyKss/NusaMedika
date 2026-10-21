<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->increments('stock_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('barang_id');
            $table->string('no_batch', 50)->nullable();
            $table->integer('bagian_id');
            $table->decimal('jumlah', 18, 2)->nullable()->default(0);

            $table->unique(['barang_id', 'bagian_id', 'no_batch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
