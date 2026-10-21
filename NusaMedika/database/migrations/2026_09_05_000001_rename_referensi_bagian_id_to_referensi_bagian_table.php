<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Upgrade database lama: rename tabel referensi_bagian_id -> referensi_bagian
     * sekaligus rapikan kolom mengikuti konvensi (PK = {table}_id).
     * Migration ini hanya berjalan bila tabel lama masih ada (fresh install
     * sudah membuat tabel referensi_bagian langsung dari create migration).
     */
    public function up(): void
    {
        if (! Schema::hasTable('referensi_bagian_id')) {
            return;
        }

        Schema::rename('referensi_bagian_id', 'referensi_bagian');

        DB::statement('ALTER TABLE referensi_bagian RENAME COLUMN referensi_bagian_id_id TO referensi_bagian_id');
        DB::statement('ALTER TABLE referensi_bagian RENAME COLUMN nama_referensi_bagian_id TO nama_referensi_bagian');
    }

    public function down(): void
    {
        if (! Schema::hasTable('referensi_bagian')) {
            return;
        }

        DB::statement('ALTER TABLE referensi_bagian RENAME COLUMN referensi_bagian_id TO referensi_bagian_id_id');
        DB::statement('ALTER TABLE referensi_bagian RENAME COLUMN nama_referensi_bagian TO nama_referensi_bagian_id');

        Schema::rename('referensi_bagian', 'referensi_bagian_id');
    }
};
