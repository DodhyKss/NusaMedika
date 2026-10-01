<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Tindiakan dirombok: katalog saja (kode + nama + kategori).
     * - bagian_id pindah ke group_tindakan (Master Group Tindakan)
     * - satuan_hasil & nilai_normal diisi manual di form Order Laboratorium
     * - kode_bpjs / kode_inacbg / kode_loinc / keterangan postponed (bridging)
     */
    public function up(): void
    {
        Schema::table('tindakan', function (Blueprint $table) {
            $table->dropColumn([
                'bagian_id',
                'kategori',
                'satuan_hasil',
                'nilai_normal',
                'kode_bpjs',
                'kode_inacbg',
                'kode_loinc',
                'keterangan',
            ]);
        });

        Schema::table('tindakan', function (Blueprint $table) {
            $table->integer('kategori_tindakan_id')->nullable()->after('nama_tindakan');
            $table->index('kategori_tindakan_id');
        });
    }

    public function down(): void
    {
        Schema::table('tindakan', function (Blueprint $table) {
            $table->dropIndex(['kategori_tindakan_id']);
            $table->dropColumn('kategori_tindakan_id');
        });

        Schema::table('tindakan', function (Blueprint $table) {
            $table->integer('bagian_id')->nullable()->after('nama_tindakan');
            $table->string('kategori', 20)->default('LAIN')->after('bagian_id');
            $table->string('satuan_hasil', 20)->nullable()->after('kategori');
            $table->string('nilai_normal', 100)->nullable()->after('satuan_hasil');
            $table->string('kode_bpjs', 20)->nullable()->after('nilai_normal');
            $table->string('kode_inacbg', 20)->nullable()->after('kode_bpjs');
            $table->string('kode_loinc', 20)->nullable()->after('kode_inacbg');
            $table->text('keterangan')->nullable()->after('kode_loinc');
            $table->index('bagian_id');
            $table->index('kategori');
        });
    }
};
