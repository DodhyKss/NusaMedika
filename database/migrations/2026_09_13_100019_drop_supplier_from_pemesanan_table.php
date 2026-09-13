<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'distributor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->integer('supplier_id')->nullable()->after('no_pemesanan');
            $table->integer('distributor_id')->nullable()->after('supplier_id');
        });
    }
};
