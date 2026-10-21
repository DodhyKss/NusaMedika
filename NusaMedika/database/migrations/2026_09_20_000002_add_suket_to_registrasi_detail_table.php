<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_detail', function (Blueprint $table) {
            $table->decimal('harga_suket', 12, 2)->nullable()->after('specimen_id');
            $table->integer('suket_mcu_id')->nullable()->after('harga_suket');
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_detail', function (Blueprint $table) {
            $table->dropColumn(['harga_suket', 'suket_mcu_id']);
        });
    }
};
