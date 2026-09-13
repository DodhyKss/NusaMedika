<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan_detail', function (Blueprint $table) {
            $table->integer('supplier_id')->nullable();
            $table->integer('distributor_id')->nullable();
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->integer('supplier_id')->nullable()->change();
            $table->integer('distributor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan_detail', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'distributor_id']);
        });

        Schema::table('pemesanan', function (Blueprint $table) {
            $table->integer('supplier_id')->change();
        });
    }
};
