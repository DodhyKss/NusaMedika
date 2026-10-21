<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->string('kode_barang', 50)->nullable();
            $table->integer('satuan_id')->nullable();
            $table->smallInteger('is_racikan')->nullable()->default(0);
            $table->smallInteger('is_fornas')->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['kode_barang', 'satuan_id', 'is_racikan', 'is_fornas']);
        });
    }
};
