<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel order penunjang medis lanjutan (prasyarat PRE-02 / PRE-03 / PRE-04):
 * Order Patologi Anatomi, Order Mikrobiologi, dan Order POCT.
 *
 * POLA SAMA dengan `order_laboratorium` / `order_radiologi` (form 6 & 7):
 * header di tabel `order_*`, item baris di `order_*_detail`. Data order TIDAK
 * disimpan di `emr_detail` — `emr_id` pada URL `/emr/form/{slug}/...`
 * bermakna `order_*_id`.
 *
 * Kolom yang dipakai `App\Helpers\PenunjangHelper` (helper generik over
 * `$jenis`) — menambah jenis baru cukup menambah entri di
 * `PenunjangHelper::tables()` + `normalizeJenis()`.
 *
 * Dual-driver MySQL 8 / PostgreSQL: hanya `increments()` dan kolom audit
 * standar. Tidak ada kolom JSON atau tipe khas satu driver.
 */
return new class extends Migration
{
    /** Helper privat: definisi kolom audit + inti untuk tabel order. */
    private function kolomOrder(Blueprint $table, string $pk): void
    {
        $table->increments($pk);
        $table->string('no_order', 30);
        $table->integer('pasien_id');
        $table->integer('registrasi_id');
        $table->integer('registrasi_detail_id');          // detail asal (pemohon)
        $table->integer('registrasi_detail_tujuan_id');  // detail tujuan (unit penunjang)
        $table->integer('bagian_asal_id');
        $table->integer('bagian_tujuan_id');
        $table->integer('dokter_id')->nullable();
        $table->string('prioritas', 15)->nullable();     // BIASA / CITO
        $table->smallInteger('status_order')->default(0);
        $table->timestamp('tanggal_order')->nullable();
        $table->timestamp('tanggal_terima')->nullable();
        $table->timestamp('tanggal_hasil')->nullable();
        $table->integer('petugas_pelaksana_id')->nullable();
        $table->smallInteger('kelas_id')->nullable();
        $table->integer('hak_kelas_id')->nullable();
        $table->text('keterangan')->nullable();

        $table->timestamp('input_time', 6)->nullable();
        $table->integer('input_user_id')->nullable();
        $table->timestamp('mod_time', 6)->nullable();
        $table->integer('mod_user_id')->nullable();
        $table->smallInteger('status_batal')->default(0);
    }

    /** Helper privat: kolom item baris (tindakan + hasil). */
    private function kolomDetail(Blueprint $table, string $pk, string $fk): void
    {
        $table->increments($pk);
        $table->integer($fk);
        $table->integer('tindakan_id')->nullable();
        $table->string('nama_tindakan', 255)->nullable();
        $table->decimal('harga', 12, 2)->nullable();
        $table->string('satuan_hasil', 20)->nullable();
        $table->string('nilai_normal', 100)->nullable();
        $table->text('hasil')->nullable();
        $table->smallInteger('flag_abnormal')->nullable();   // 0 normal / 1 abnormal
        $table->smallInteger('status')->default(0);
        $table->integer('petugas_id')->nullable();

        $table->timestamp('input_time', 6)->nullable();
        $table->integer('input_user_id')->nullable();
        $table->timestamp('mod_time', 6)->nullable();
        $table->integer('mod_user_id')->nullable();
        $table->smallInteger('status_batal')->default(0);
    }

    public function up(): void
    {
        // --- Order Patologi Anatomi (form 49) ---
        // Kolom khusus: `jenis_pesanan` = group_tindakan peminta.
        if (! Schema::hasTable('order_patologi')) {
            Schema::create('order_patologi', function (Blueprint $table) {
                $this->kolomOrder($table, 'order_patologi_id');
                $table->integer('jenis_pesanan')->nullable();   // FK logis group_tindakan
            });
            Schema::create('order_patologi_detail', function (Blueprint $table) {
                $this->kolomDetail($table, 'order_patologi_detail_id', 'order_patologi_id');
                $table->string('spesimen', 50)->nullable();     // Biopsi / Sitologi / Kspecimen / Blocks
            });
        }

        // --- Order Mikrobiologi (form 50) ---
        // Kolom khusus: unit & jenis pemeriksaan, sumber & jumlah spesimen.
        if (! Schema::hasTable('order_mikrobiologi')) {
            Schema::create('order_mikrobiologi', function (Blueprint $table) {
                $this->kolomOrder($table, 'order_mikrobiologi_id');
                $table->integer('unit_pemeriksaan')->nullable();  // bagian mikrobiologi
                $table->string('jenis_pemeriksaan', 20)->nullable(); // Dalam / Luar
                $table->string('spesimen', 50)->nullable();
                $table->smallInteger('jumlah_spesimen')->nullable();
            });
            Schema::create('order_mikrobiologi_detail', function (Blueprint $table) {
                $this->kolomDetail($table, 'order_mikrobiologi_detail_id', 'order_mikrobiologi_id');
            });
        }

        // --- Order POCT (form 51) ---
        // POCT tidak punya "item tindakan" seperti lab/rad: hasil ditulis per
        // parameter. Kolom `indikasi` menjelaskan alasan pemeriksaan.
        if (! Schema::hasTable('order_poct')) {
            Schema::create('order_poct', function (Blueprint $table) {
                $this->kolomOrder($table, 'order_poct_id');
                $table->string('indikasi', 255)->nullable();
            });
            Schema::create('order_poct_detail', function (Blueprint $table) {
                $this->kolomDetail($table, 'order_poct_detail_id', 'order_poct_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_poct_detail');
        Schema::dropIfExists('order_poct');
        Schema::dropIfExists('order_mikrobiologi_detail');
        Schema::dropIfExists('order_mikrobiologi');
        Schema::dropIfExists('order_patologi_detail');
        Schema::dropIfExists('order_patologi');
    }
};
