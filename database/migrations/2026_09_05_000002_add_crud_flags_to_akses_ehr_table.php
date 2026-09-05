<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akses_ehr', function (Blueprint $table) {
            $table->smallInteger('akses_create')->nullable()->default(0);
            $table->smallInteger('akses_read')->nullable()->default(0);
            $table->smallInteger('akses_update')->nullable()->default(0);
            $table->smallInteger('akses_delete')->nullable()->default(0);
        });

        // Baris akses yang sudah ada sebelumnya artinya profesi itu "punya akses"
        // ke form tsb -> beri semua aksi (create/read/update/delete = 1).
        DB::table('akses_ehr')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->update([
                'akses_create' => 1,
                'akses_read' => 1,
                'akses_update' => 1,
                'akses_delete' => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('akses_ehr', function (Blueprint $table) {
            $table->dropColumn(['akses_create', 'akses_read', 'akses_update', 'akses_delete']);
        });
    }
};
