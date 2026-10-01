<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Tarif: satu tarif per tindakan per kelas perawatan.
     * Tarif BPJS dihapus — harga VIP/kelas khusus sudah terpisah lewat kelas_ruang.
     */
    public function up(): void
    {
        Schema::table('tindakan_harga', function (Blueprint $table) {
            $table->dropColumn('tarif_bpjs');
        });
    }

    public function down(): void
    {
        Schema::table('tindakan_harga', function (Blueprint $table) {
            $table->decimal('tarif_bpjs', 12, 2)->nullable()->after('tarif');
        });
    }
};
