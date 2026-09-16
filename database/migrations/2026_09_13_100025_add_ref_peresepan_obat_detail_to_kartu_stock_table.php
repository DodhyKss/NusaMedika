<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_stock', function (Blueprint $table) {
            $table->integer('ref_peresepan_obat_detail_id')->nullable()->after('ref_mutasi_barang_detail_id');
        });
    }

    public function down(): void
    {
        Schema::table('kartu_stock', function (Blueprint $table) {
            $table->dropColumn('ref_peresepan_obat_detail_id');
        });
    }
};
