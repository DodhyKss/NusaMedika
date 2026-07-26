<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Set connection to not use transaction so we can catch individual errors
        \Illuminate\Support\Facades\DB::connection()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if (Schema::hasTable('akses_ehr')) {
            try {
                Schema::table('akses_ehr', function (Blueprint $table) {
                    $table->primary(['akses_ehr_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_ehr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('akses_ehr', function (Blueprint $table) {
                    $table->index(['akses_ehr_id', 'profesi_id', 'form_id', 'bagian_id', 'level_id'], 'akses_ehr_akses_ehr_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_ehr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('akses_ehr', function (Blueprint $table) {
                    $table->index(['form_id', 'akses_ehr_id', 'profesi_id'], 'idx_akses_ehr');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_ehr: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('akses_log_detail')) {
            try {
                Schema::table('akses_log_detail', function (Blueprint $table) {
                    $table->primary(['akses_log_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_log_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('akses_log_detail', function (Blueprint $table) {
                    $table->index(['akses_log_detail_id', 'akses_log_id', 'sub_menu_id', 'akses_time'], 'akses_log_detail_akses_log_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_log_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('akses_log')) {
            try {
                Schema::table('akses_log', function (Blueprint $table) {
                    $table->primary(['akses_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('akses_log', function (Blueprint $table) {
                    $table->index(['akses_log_id', 'session_id', 'user_id', 'ip_address', 'token'], 'akses_log_akses_log_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akses_log: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('akta_bayi')) {
            try {
                Schema::table('akta_bayi', function (Blueprint $table) {
                    $table->primary(['akta_bayi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akta_bayi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('akta_bayi', function (Blueprint $table) {
                    $table->index(['akta_bayi_id', 'pasien_id', 'bayi_id', 'kartu_keluarga', 'jenis_kelamin', 'jenis_kelahiran'], 'akta_bayi_akta_bayi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on akta_bayi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('alat')) {
            try {
                Schema::table('alat', function (Blueprint $table) {
                    $table->primary(['alat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on alat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('alat', function (Blueprint $table) {
                    $table->index(['alat_id', 'jenis_alat', 'bagian_id'], 'alat_alat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on alat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('antrian_area')) {
            try {
                Schema::table('antrian_area', function (Blueprint $table) {
                    $table->primary(['antrian_area_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_area: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_area', function (Blueprint $table) {
                    $table->index(['antrian_area_id', 'spesifikasi_antrian', 'input_user_id', 'input_time'], 'antrian_area_antrian_area_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_area: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('antrian_system_detail')) {
            try {
                Schema::table('antrian_system_detail', function (Blueprint $table) {
                    $table->primary(['antrian_system_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system_detail', function (Blueprint $table) {
                    $table->index(['antrian_system_detail_id', 'antrian_system_id', 'loket', 'ip', 'mac', 'input_user_id', 'input_time'], 'antrian_system_detail_antrian_system_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('antrian_system')) {
            try {
                Schema::table('antrian_system', function (Blueprint $table) {
                    $table->primary(['antrian_system_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system', function (Blueprint $table) {
                    $table->index(['antrian_system_id', 'input_time', 'input_user_id', 'deffect'], 'antrian_system_antrian_system_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('antrian_system_urutan')) {
            try {
                Schema::table('antrian_system_urutan', function (Blueprint $table) {
                    $table->primary(['antrian_system_urutan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_urutan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system_urutan', function (Blueprint $table) {
                    $table->index(['tanggal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_urutan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system_urutan', function (Blueprint $table) {
                    $table->index(['tipe']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_urutan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system_urutan', function (Blueprint $table) {
                    $table->index(['urutan']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_urutan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('antrian_system_urutan', function (Blueprint $table) {
                    $table->index(['antrian_system_urutan_id', 'antrian_system_id', 'tanggal', 'urutan', 'tipe', 'loket', 'flag_panggil', 'input_time', 'time_panggil'], 'antrian_system_urutan_antrian_system_urutan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on antrian_system_urutan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('arsip_dokumen_keuangan')) {
            try {
                Schema::table('arsip_dokumen_keuangan', function (Blueprint $table) {
                    $table->primary(['arsip_dokumen_keuangan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on arsip_dokumen_keuangan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('arsip_dokumen')) {
            try {
                Schema::table('arsip_dokumen', function (Blueprint $table) {
                    $table->primary(['arsip_dokumen_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on arsip_dokumen: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('arsip_dokumen', function (Blueprint $table) {
                    $table->index(['arsip_dokumen_id', 'bagian_id', 'no_urut_arsip', 'tgl_upload_file', 'jenis_file'], 'arsip_dokumen_arsip_dokumen_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on arsip_dokumen: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('asa_konfigurasi')) {
            try {
                Schema::table('asa_konfigurasi', function (Blueprint $table) {
                    $table->primary(['asa_konfigurasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on asa_konfigurasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('asa_konfigurasi', function (Blueprint $table) {
                    $table->index(['asa_konfigurasi_id', 'flag_aktif', 'input_time'], 'asa_konfigurasi_asa_konfigurasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on asa_konfigurasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('aset_detail')) {
            try {
                Schema::table('aset_detail', function (Blueprint $table) {
                    $table->primary(['aset_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on aset_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('aset_detail', function (Blueprint $table) {
                    $table->index(['aset_detail_id', 'barang_id', 'aset_id', 'bed_id', 'input_time'], 'aset_detail_aset_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on aset_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('aset')) {
            try {
                Schema::table('aset', function (Blueprint $table) {
                    $table->primary(['aset_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on aset: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('aset', function (Blueprint $table) {
                    $table->index(['aset_id', 'bagian_id', 'input_user_id', 'input_time'], 'aset_aset_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on aset: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bagian')) {
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->primary(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['nama_bagian']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['referensi_bagian']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['seri_bagian']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['bagian_id', 'referensi_bagian'], 'bagian_bagian_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bagian', function (Blueprint $table) {
                    $table->index(['bagian_id', 'referensi_bagian', 'status_batal'], 'idx_bagian01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bagian: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bank')) {
            try {
                Schema::table('bank', function (Blueprint $table) {
                    $table->primary(['bank_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bank: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bank', function (Blueprint $table) {
                    $table->index(['bank_id', 'no_kartu', 'input_time'], 'bank_bank_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bank: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang_golongan_detail')) {
            try {
                Schema::table('barang_golongan_detail', function (Blueprint $table) {
                    $table->primary(['barang_golongan_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_golongan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang_golongan_detail', function (Blueprint $table) {
                    $table->index(['barang_golongan_detail_id', 'barang_golongan_id', 'barang_id'], 'barang_golongan_detail_barang_golongan_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_golongan_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang_golongan')) {
            try {
                Schema::table('barang_golongan', function (Blueprint $table) {
                    $table->primary(['barang_golongan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_golongan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang_golongan', function (Blueprint $table) {
                    $table->index(['barang_golongan_id', 'status_batal', 'nama_golongan'], 'barang_golongan_barang_golongan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_golongan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang_jenis')) {
            try {
                Schema::table('barang_jenis', function (Blueprint $table) {
                    $table->primary(['barang_jenis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_jenis: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang_jenis', function (Blueprint $table) {
                    $table->index(['barang_jenis_id', 'status_batal', 'nama_jenis'], 'barang_jenis_barang_jenis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_jenis: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang_principal_supplier')) {
            try {
                Schema::table('barang_principal_supplier', function (Blueprint $table) {
                    $table->primary(['barang_principal_supplier_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_principal_supplier: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang_principal_supplier', function (Blueprint $table) {
                    $table->index(['barang_principal_supplier_id', 'barang_id', 'supplier_id', 'principal_id'], 'barang_principal_supplier_barang_principal_supplier_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_principal_supplier: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang_sub_golongan')) {
            try {
                Schema::table('barang_sub_golongan', function (Blueprint $table) {
                    $table->primary(['barang_sub_golongan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_sub_golongan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang_sub_golongan', function (Blueprint $table) {
                    $table->index(['barang_sub_golongan_id', 'barang_golongan_id'], 'barang_sub_golongan_barang_sub_golongan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang_sub_golongan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('barang')) {
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->primary(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['kategori_barang']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['barang_jenis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['satuan_besar']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['satuan_kecil']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['satuan_pakai']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['barang_id_lama']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('barang', function (Blueprint $table) {
                    $table->index(['barang_id', 'kategori_barang', 'barang_jenis_id', 'barang_sub_golongan_id', 'jenis_golongan', 'komposisi_id'], 'barang_barang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on barang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('batch_barang')) {
            try {
                Schema::table('batch_barang', function (Blueprint $table) {
                    $table->primary(['batch_barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on batch_barang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bayi')) {
            try {
                Schema::table('bayi', function (Blueprint $table) {
                    $table->primary(['bayi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bayi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bayi', function (Blueprint $table) {
                    $table->index(['bayi_id', 'pasien_id', 'no_skk', 'no_urut'], 'bayi_bayi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bayi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bed_log_hd')) {
            try {
                Schema::table('bed_log_hd', function (Blueprint $table) {
                    $table->primary(['bed_log_hd_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log_hd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log_hd', function (Blueprint $table) {
                    $table->index(['bed_log_hd_id', 'pasien_id', 'registrasi_detail_id', 'bed_id'], 'bed_log_hd_bed_log_hd_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log_hd: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bed_log')) {
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->primary(['bed_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['input_time']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['status_bed_log']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['bed_log_id', 'pasien_id', 'bed_id', 'registrasi_detail_id'], 'bed_log_bed_log_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['pasien_id', 'bed_id', 'registrasi_detail_id', 'bed_log_id', 'input_time', 'status_bed_log'], 'bed_log_pasien_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_log', function (Blueprint $table) {
                    $table->index(['bed_log_id', 'pasien_id', 'bed_id', 'registrasi_detail_id'], 'idx_bed_log01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_log: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bed')) {
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->primary(['bed_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['no_kamar']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['status_bed']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['kelas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['pasien_id_1']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['pasien_id_2']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['siap_kirim']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['flag_extra']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['bor']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['bed_id', 'bagian_id', 'no_kamar', 'kelas_id', 'pasien_id_1'], 'bed_bed_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['bed_id', 'pasien_id_1', 'status_batal'], 'idx_bed');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed', function (Blueprint $table) {
                    $table->index(['pasien_id_1'], 'idx_bed_pasien');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bed_waitlist')) {
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->primary(['bed_waitlist_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['bed_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['bed_id_asal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['bed_id_tujuan']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['status_bed_waitlist']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['bed_waitlist_id', 'pasien_id', 'bed_log_id', 'bed_id_asal', 'bed_id_tujuan'], 'bed_waitlist_bed_waitlist_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bed_waitlist', function (Blueprint $table) {
                    $table->index(['bed_waitlist_id', 'pasien_id', 'registrasi_detail_id', 'bed_log_id', 'bed_id_asal', 'bed_id_tujuan'], 'idx_bed_waitlist01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bed_waitlist: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('berkas')) {
            try {
                Schema::table('berkas', function (Blueprint $table) {
                    $table->primary(['berkas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on berkas: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('berkas', function (Blueprint $table) {
                    $table->index(['berkas_id', 'pasien_id', 'user_id_kirim', 'bagian_id_kirim', 'user_id_terima', 'bagian_id_terima'], 'berkas_berkas_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on berkas: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bill_kasir_detail')) {
            try {
                Schema::table('bill_kasir_detail', function (Blueprint $table) {
                    $table->primary(['bill_kasir_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_kasir_detail', function (Blueprint $table) {
                    $table->index(['bill_kasir_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_kasir_detail', function (Blueprint $table) {
                    $table->index(['tarif_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_kasir_detail', function (Blueprint $table) {
                    $table->index(['bill_kasir_detail_id', 'bill_kasir_id', 'jenis_tindakan_id', 'tarif_id', 'pegawai_id', 'tindakan_id', 'harga_jual_obat_id', 'kuitansi_id', 'kuitansi_user_id', 'peresepan_obat_dispense_id', 'emr_id'], 'bill_kasir_detail_bill_kasir_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bill_kasir')) {
            try {
                Schema::table('bill_kasir', function (Blueprint $table) {
                    $table->primary(['bill_kasir_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_kasir', function (Blueprint $table) {
                    $table->index(['bill_kasir_id', 'mod_user_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'nasabah_id', 'kelas_ruang_id', 'hak_kelas_ruang_id', 'peresepan_obat_id', 'faktur_id'], 'bill_kasir_bill_kasir_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_kasir', function (Blueprint $table) {
                    $table->index(['bill_kasir_id', 'status_batal', 'registrasi_detail_id', 'nasabah_id', 'pasien_id', 'bagian_id'], 'idx_bill_kasir01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_kasir: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bill_obat_otc')) {
            try {
                Schema::table('bill_obat_otc', function (Blueprint $table) {
                    $table->primary(['bill_obat_otc_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_obat_otc: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_obat_otc', function (Blueprint $table) {
                    $table->index(['bill_obat_otc_id', 'bill_temp_detail_id'], 'bill_obat_otc_bill_obat_otc_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_obat_otc: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bill_temp_detail')) {
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->primary(['bill_temp_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['tarif_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['harga_jual_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['peresepan_obat_dispense_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['emr_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['bill_temp_detail_id', 'bill_temp_id', 'jenis_tindakan_id', 'tarif_id', 'pegawai_id', 'tindakan_id', 'peresepan_obat_detail_id', 'harga_jual_obat_id'], 'bill_temp_detail_bill_temp_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp_detail', function (Blueprint $table) {
                    $table->index(['bill_temp_id', 'status_batal', 'tindakan_id', 'tarif_id', 'jenis_tindakan_id', 'peresepan_obat_detail_id'], 'idx_bill_temp_detail');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bill_temp')) {
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->primary(['bill_temp_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['flag_tampil']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['bill_temp_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'nasabah_id', 'kelas_ruang_id', 'hak_kelas_ruang_id', 'peresepan_obat_id'], 'bill_temp_bill_temp_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id', 'status_batal'], 'bill_temp_de_registrasi_detail_id');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bill_temp', function (Blueprint $table) {
                    $table->index(['bill_temp_id', 'status_batal', 'registrasi_detail_id', 'nasabah_id', 'bagian_id'], 'idx_bill_temp01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bill_temp: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('billing_selisih')) {
            try {
                Schema::table('billing_selisih', function (Blueprint $table) {
                    $table->primary(['billing_selisih_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on billing_selisih: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('billing_selisih', function (Blueprint $table) {
                    $table->index(['billing_selisih_id', 'registrasi_id'], 'billing_selisih_billing_selisih_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on billing_selisih: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bridging_eis_log')) {
            try {
                Schema::table('bridging_eis_log', function (Blueprint $table) {
                    $table->primary(['bridging_eis_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bridging_eis_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bridging_eis_log', function (Blueprint $table) {
                    $table->index(['bridging_eis_log_id', 'mod_time'], 'bridging_eis_log_bridging_eis_log_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bridging_eis_log: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('bridging_ris')) {
            try {
                Schema::table('bridging_ris', function (Blueprint $table) {
                    $table->primary(['bridging_ris_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bridging_ris: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('bridging_ris', function (Blueprint $table) {
                    $table->index(['bridging_ris_id', 'tindakan_id'], 'bridging_ris_bridging_ris_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on bridging_ris: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('capture_bor')) {
            try {
                Schema::table('capture_bor', function (Blueprint $table) {
                    $table->primary(['capture_bor_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on capture_bor: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('capture_bor', function (Blueprint $table) {
                    $table->index(['capture_bor_id', 'bagian_id', 'kelas_ruang_id'], 'capture_bor_capture_bor_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on capture_bor: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('cicilan_detail')) {
            try {
                Schema::table('cicilan_detail', function (Blueprint $table) {
                    $table->primary(['cicilan_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cicilan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('cicilan_detail', function (Blueprint $table) {
                    $table->index(['cicilan_detail_id', 'cicilan_id', 'kuitansi_id'], 'cicilan_detail_cicilan_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cicilan_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('cicilan')) {
            try {
                Schema::table('cicilan', function (Blueprint $table) {
                    $table->primary(['cicilan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cicilan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('cicilan', function (Blueprint $table) {
                    $table->index(['cicilan_id', 'registrasi_id', 'pasien_id'], 'cicilan_cicilan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cicilan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('cito_konfigurasi')) {
            try {
                Schema::table('cito_konfigurasi', function (Blueprint $table) {
                    $table->primary(['cito_konfigurasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cito_konfigurasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('cito_konfigurasi', function (Blueprint $table) {
                    $table->index(['cito_konfigurasi_id', 'bagian_id'], 'cito_konfigurasi_cito_konfigurasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cito_konfigurasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('covid_claim')) {
            try {
                Schema::table('covid_claim', function (Blueprint $table) {
                    $table->primary(['covid_claim_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on covid_claim: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('covid_claim', function (Blueprint $table) {
                    $table->index(['covid_claim_id', 'registrasi_id', 'covid_claim_number'], 'covid_claim_covid_claim_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on covid_claim: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('cuti_dokter_detail')) {
            try {
                Schema::table('cuti_dokter_detail', function (Blueprint $table) {
                    $table->primary(['cuti_dokter_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cuti_dokter_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('cuti_dokter_detail', function (Blueprint $table) {
                    $table->index(['cuti_dokter_detail_id', 'cuti_dokter_id', 'registrasi_detail_id'], 'cuti_dokter_detail_cuti_dokter_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cuti_dokter_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('cuti_dokter')) {
            try {
                Schema::table('cuti_dokter', function (Blueprint $table) {
                    $table->primary(['cuti_dokter_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cuti_dokter: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('cuti_dokter', function (Blueprint $table) {
                    $table->index(['cuti_dokter_id', 'cuti_user_id'], 'cuti_dokter_cuti_dokter_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on cuti_dokter: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('dashboard_menu_sub_extra')) {
            try {
                Schema::table('dashboard_menu_sub_extra', function (Blueprint $table) {
                    $table->primary(['dashboard_menu_sub_extra_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu_sub_extra: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('dashboard_menu_sub_extra', function (Blueprint $table) {
                    $table->index(['dashboard_menu_sub_extra_id', 'dashboard_menu_sub_id'], 'dashboard_menu_sub_extra_dashboard_menu_sub_extra_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu_sub_extra: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('dashboard_menu_sub')) {
            try {
                Schema::table('dashboard_menu_sub', function (Blueprint $table) {
                    $table->primary(['dashboard_menu_sub_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu_sub: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('dashboard_menu_sub', function (Blueprint $table) {
                    $table->index(['dashboard_menu_sub_id', 'dashboard_menu_id'], 'dashboard_menu_sub_dashboard_menu_sub_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu_sub: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('dashboard_menu')) {
            try {
                Schema::table('dashboard_menu', function (Blueprint $table) {
                    $table->primary(['dashboard_menu_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('dashboard_menu', function (Blueprint $table) {
                    $table->index(['dashboard_menu_id', 'mod_user_id'], 'dashboard_menu_dashboard_menu_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dashboard_menu: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('detail_akses_tindakan')) {
            try {
                Schema::table('detail_akses_tindakan', function (Blueprint $table) {
                    $table->primary(['detail_akses_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on detail_akses_tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('detail_akses_tindakan', function (Blueprint $table) {
                    $table->index(['detail_akses_tindakan_id', 'jenis_tindakan', 'nama_akses'], 'detail_akses_tindakan_detail_akses_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on detail_akses_tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('detail_tindakan_bedah')) {
            try {
                Schema::table('detail_tindakan_bedah', function (Blueprint $table) {
                    $table->primary(['detail_tindakan_bedah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on detail_tindakan_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('detail_tindakan_bedah', function (Blueprint $table) {
                    $table->index(['detail_tindakan_bedah_id', 'jenis_tindakan', 'nama_tindakan_bedah'], 'detail_tindakan_bedah_detail_tindakan_bedah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on detail_tindakan_bedah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan_indikasi_kriteria')) {
            try {
                Schema::table('diagnosa_keperawatan_indikasi_kriteria', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_indikasi_kriteria_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_indikasi_kriteria: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan_indikasi_kriteria', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_indikasi_kriteria_id', 'diagnosa_keperawatan_id'], 'diagnosa_keperawatan_indikasi_kriteria_diagnosa_keperawatan_ind');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_indikasi_kriteria: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan_indikasi')) {
            try {
                Schema::table('diagnosa_keperawatan_indikasi', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_indikasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_indikasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan_indikasi', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_indikasi_id', 'diagnosa_keperawatan_id'], 'diagnosa_keperawatan_indikasi_diagnosa_keperawatan_indikasi_id_');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_indikasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan_intervensi')) {
            try {
                Schema::table('diagnosa_keperawatan_intervensi', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_intervensi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_intervensi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan_intervensi', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_intervensi_id', 'kode_intervensi', 'diagnosa_keperawatan_id'], 'diagnosa_keperawatan_intervensi_diagnosa_keperawatan_intervensi');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_intervensi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan_luaran')) {
            try {
                Schema::table('diagnosa_keperawatan_luaran', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_luaran_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_luaran: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan_luaran', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_luaran_id', 'kode_luaran'], 'diagnosa_keperawatan_luaran_diagnosa_keperawatan_luaran_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_luaran: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan_rj')) {
            try {
                Schema::table('diagnosa_keperawatan_rj', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_rj_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_rj: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan_rj', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_rj_id', 'kode_diagnosa', 'diagnosa_umum', 'diagnosa_obgyn'], 'diagnosa_keperawatan_rj_diagnosa_keperawatan_rj_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan_rj: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_keperawatan')) {
            try {
                Schema::table('diagnosa_keperawatan', function (Blueprint $table) {
                    $table->primary(['diagnosa_keperawatan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_keperawatan', function (Blueprint $table) {
                    $table->index(['diagnosa_keperawatan_id', 'kode_diagnosa', 'diagnosa_anak', 'diagnosa_bayi'], 'diagnosa_keperawatan_diagnosa_keperawatan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_keperawatan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diagnosa_rawat')) {
            try {
                Schema::table('diagnosa_rawat', function (Blueprint $table) {
                    $table->primary(['diagnosa_rawat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_rawat', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_rawat', function (Blueprint $table) {
                    $table->index(['icd_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_rawat', function (Blueprint $table) {
                    $table->index(['diagnosa_rawat_id', 'registrasi_id', 'icd_id', 'jenis_diagnosa'], 'diagnosa_rawat_diagnosa_rawat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diagnosa_rawat', function (Blueprint $table) {
                    $table->index(['diagnosa_rawat_id', 'registrasi_id', 'icd_id', 'status_batal'], 'idx_diagnosa_rawat01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diagnosa_rawat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('diet')) {
            try {
                Schema::table('diet', function (Blueprint $table) {
                    $table->primary(['diet_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diet: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('diet', function (Blueprint $table) {
                    $table->index(['diet_id', 'registrasi_detail_id', 'bed_id', 'pasien_id', 'registrasi_id'], 'diet_diet_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on diet: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('dpjp_hfis')) {
            try {
                Schema::table('dpjp_hfis', function (Blueprint $table) {
                    $table->primary(['dpjp_hfis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dpjp_hfis: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('dpjp_hfis', function (Blueprint $table) {
                    $table->index(['dpjp_hfis_id', 'user_id', 'dpjp_hfis_kode'], 'dpjp_hfis_dpjp_hfis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on dpjp_hfis: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('draft_material_request_detail')) {
            try {
                Schema::table('draft_material_request_detail', function (Blueprint $table) {
                    $table->primary(['draft_material_request_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on draft_material_request_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('draft_material_request_detail', function (Blueprint $table) {
                    $table->index(['draft_material_request_detail_id', 'bagian_id', 'barang_id', 'material_request_id'], 'draft_material_request_detail_draft_material_request_detail_id_');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on draft_material_request_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('draft_material_request')) {
            try {
                Schema::table('draft_material_request', function (Blueprint $table) {
                    $table->primary(['draft_material_request_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on draft_material_request: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('draft_material_request', function (Blueprint $table) {
                    $table->index(['draft_material_request_id', 'acc_kainst_user_id', 'acc_kasie_user_id'], 'draft_material_request_draft_material_request_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on draft_material_request: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('emr_detail')) {
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->primary(['emr_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['objek_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_id', 'emr_detail_id', 'objek_id', 'variabel', 'input_user_id', 'input_time'], 'emr_detail_emr_id2_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_id', 'variabel'], 'emr_detail_emr_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_id', 'objek_id'], 'emr_detail_emr_id_idx3');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_detail_id', 'emr_id', 'objek_id', 'variabel'], 'emr_detail_id2_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr_detail', function (Blueprint $table) {
                    $table->index(['emr_id', 'objek_id'], 'idx_emr_detail_emr_objek');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('emr')) {
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->primary(['emr_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['input_user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['form_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['tgl_jam']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['tgl_jam'], 'emr_de_idx_year_2024');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['tgl_jam'], 'emr_de_idx_year_2025');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['emr_id', 'form_id', 'pegawai_id', 'pasien_id', 'registrasi_id', 'registrasi_detail_id', 'tgl_jam'], 'emr_emr_id2_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['emr_id', 'pasien_id', 'registrasi_detail_id', 'pegawai_id', 'form_id'], 'emr_emr_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['tgl_jam'], 'emr_tgl_jam_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['form_id', 'status_batal'], 'idx_emr_form_status');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('emr', function (Blueprint $table) {
                    $table->index(['pasien_id'], 'idx_emr_pasien_id');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on emr: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('faktur_detail')) {
            try {
                Schema::table('faktur_detail', function (Blueprint $table) {
                    $table->primary(['faktur_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('faktur_detail', function (Blueprint $table) {
                    $table->index(['faktur_detail_id', 'faktur_id', 'registrasi_id', 'pasien_id', 'verif_user_id'], 'faktur_detail_faktur_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('faktur_detail', function (Blueprint $table) {
                    $table->index(['faktur_detail_id', 'faktur_id', 'registrasi_id', 'pasien_id'], 'idx_faktur_detail_faktur_detail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('faktur')) {
            try {
                Schema::table('faktur', function (Blueprint $table) {
                    $table->primary(['faktur_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('faktur', function (Blueprint $table) {
                    $table->index(['faktur_id', 'nasabah_id', 'jenis_rawat'], 'faktur_faktur_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('faktur', function (Blueprint $table) {
                    $table->index(['faktur_id', 'nasabah_id', 'kode_faktur', 'jenis_rawat'], 'idx_faktur01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faktur: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('faskes_perujuk')) {
            try {
                Schema::table('faskes_perujuk', function (Blueprint $table) {
                    $table->primary(['id_perujuk']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on faskes_perujuk: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('file_upload_berkas')) {
            try {
                Schema::table('file_upload_berkas', function (Blueprint $table) {
                    $table->primary(['file_upload_berkas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on file_upload_berkas: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('flash_news')) {
            try {
                Schema::table('flash_news', function (Blueprint $table) {
                    $table->primary(['flash_news_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on flash_news: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('flash_news', function (Blueprint $table) {
                    $table->index(['flash_news_id', 'tgl_flash_news', 'flag_flash_news'], 'flash_news_flash_news_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on flash_news: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('floor_stock')) {
            try {
                Schema::table('floor_stock', function (Blueprint $table) {
                    $table->primary(['floor_stock_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on floor_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('floor_stock', function (Blueprint $table) {
                    $table->index(['floor_stock_id', 'barang_id', 'bagian_id'], 'floor_stock_floor_stock_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on floor_stock: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('form')) {
            try {
                Schema::table('form', function (Blueprint $table) {
                    $table->primary(['form_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on form: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('form', function (Blueprint $table) {
                    $table->index(['form_id', 'id_dash_menu', 'ri', 'rj', 'igd'], 'form_form_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on form: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('form', function (Blueprint $table) {
                    $table->index(['form_id', 'id_dash_menu'], 'idx_form');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on form: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('harga_jual_obat_pakai')) {
            try {
                Schema::table('harga_jual_obat_pakai', function (Blueprint $table) {
                    $table->primary(['harga_jual_obat_pakai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat_pakai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat_pakai', function (Blueprint $table) {
                    $table->index(['harga_jual_obat_pakai_id', 'barang_id', 'permintaan_brg_detail_id'], 'harga_jual_obat_pakai_harga_jual_obat_pakai_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat_pakai: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('harga_jual_obat')) {
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->primary(['harga_jual_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['penerimaan_brg_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['nomor_batch']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['tgl_expired']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('harga_jual_obat', function (Blueprint $table) {
                    $table->index(['harga_jual_obat_id', 'barang_id', 'penerimaan_brg_detail_id', 'tgl_expired', 'status_selesai'], 'harga_jual_obat_harga_jual_obat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on harga_jual_obat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_lab_detail')) {
            try {
                Schema::table('hasil_lab_detail', function (Blueprint $table) {
                    $table->primary(['hasil_lab_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_lab_detail', function (Blueprint $table) {
                    $table->index(['hasil_lab_detail_id', 'hasil_lab_id', 'tindakan_id', 'kode_pemeriksaan', 'pasien_id'], 'hasil_lab_detail_hasil_lab_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_lab_master')) {
            try {
                Schema::table('hasil_lab_master', function (Blueprint $table) {
                    $table->primary(['hasil_lab_master_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab_master: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_lab_master', function (Blueprint $table) {
                    $table->index(['hasil_lab_master_id', 'tindakan_id', 'unit'], 'hasil_lab_master_hasil_lab_master_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab_master: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_lab')) {
            try {
                Schema::table('hasil_lab', function (Blueprint $table) {
                    $table->primary(['hasil_lab_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_lab', function (Blueprint $table) {
                    $table->index(['hasil_lab_id', 'registrasi_detail_id', 'pasien_id', 'user_konfirmasi_id'], 'hasil_lab_hasil_lab_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_lab: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_mikrobiologi')) {
            try {
                Schema::table('hasil_mikrobiologi', function (Blueprint $table) {
                    $table->primary(['hasil_mikrobiologi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_mikrobiologi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_patologi_anatomi_detail')) {
            try {
                Schema::table('hasil_patologi_anatomi_detail', function (Blueprint $table) {
                    $table->primary(['hasil_patologi_anatomi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_anatomi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_patologi_anatomi_detail', function (Blueprint $table) {
                    $table->index(['hasil_patologi_anatomi_detail_id', 'tindakan_id', 'tindakan_group_id', 'user_deskripsi_id', 'user_hasil_id', 'pegawai_id'], 'hasil_patologi_anatomi_detail_hasil_patologi_anatomi_detail_id_');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_anatomi_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_patologi_anatomi')) {
            try {
                Schema::table('hasil_patologi_anatomi', function (Blueprint $table) {
                    $table->primary(['hasil_patologi_anatomi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_anatomi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_patologi_anatomi', function (Blueprint $table) {
                    $table->index(['hasil_patologi_anatomi_id', 'registrasi_detail_id', 'pasien_id', 'tindakan_group_id', 'no_order_pesanan'], 'hasil_patologi_anatomi_hasil_patologi_anatomi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_anatomi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_patologi_foto')) {
            try {
                Schema::table('hasil_patologi_foto', function (Blueprint $table) {
                    $table->primary(['hasil_patologi_foto_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_foto: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_patologi_foto', function (Blueprint $table) {
                    $table->index(['hasil_patologi_foto_id', 'pasien_id', 'registrasi_detail_id', 'hasil_patologi_anatomi_id', 'tgl_upload_foto'], 'hasil_patologi_foto_hasil_patologi_foto_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_patologi_foto: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_rad_detail')) {
            try {
                Schema::table('hasil_rad_detail', function (Blueprint $table) {
                    $table->primary(['hasil_rad_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_rad_detail', function (Blueprint $table) {
                    $table->index(['hasil_rad_detail_id', 'hasil_rad_id', 'tindakan_id'], 'hasil_rad_detail_hasil_rad_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_rad_foto')) {
            try {
                Schema::table('hasil_rad_foto', function (Blueprint $table) {
                    $table->primary(['hasil_rad_foto_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad_foto: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_rad_foto', function (Blueprint $table) {
                    $table->index(['hasil_rad_foto_id', 'pasien_id', 'registrasi_detail_id', 'tindakan_id'], 'hasil_rad_foto_hasil_rad_foto_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad_foto: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('hasil_rad')) {
            try {
                Schema::table('hasil_rad', function (Blueprint $table) {
                    $table->primary(['hasil_rad_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('hasil_rad', function (Blueprint $table) {
                    $table->index(['hasil_rad_id', 'registrasi_detail_id', 'pasien_id', 'user_konfirmasi_id', 'tgl_hasil', 'tgl_konfirmasi'], 'hasil_rad_hasil_rad_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on hasil_rad: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('i_care_log')) {
            try {
                Schema::table('i_care_log', function (Blueprint $table) {
                    $table->primary(['i_care_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on i_care_log: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('icd')) {
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->primary(['icd_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->index(['kode_diagnosa']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->index(['nama_diagnosa']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->index(['kategori']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->index(['icd_id', 'kode_diagnosa'], 'icd_icd_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('icd', function (Blueprint $table) {
                    $table->index(['icd_id', 'status_batal', 'kode_diagnosa'], 'idx_icd01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on icd: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('identitas_pasien')) {
            try {
                Schema::table('identitas_pasien', function (Blueprint $table) {
                    $table->primary(['identitas_pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on identitas_pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('identitas_pasien', function (Blueprint $table) {
                    $table->index(['identitas_pasien_id', 'pasien_id'], 'identitas_pasien_identitas_pasien_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on identitas_pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('identitas_pasien', function (Blueprint $table) {
                    $table->index(['status_batal', 'jenis_file'], 'idx_identitas_pasien');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on identitas_pasien: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('implant')) {
            try {
                Schema::table('implant', function (Blueprint $table) {
                    $table->primary(['implant_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on implant: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('implant', function (Blueprint $table) {
                    $table->index(['implant_id', 'kode_implant', 'nomor_kartu', 'status_batal'], 'implant_implant_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on implant: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('implementasi_tindakan')) {
            try {
                Schema::table('implementasi_tindakan', function (Blueprint $table) {
                    $table->primary(['implementasi_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on implementasi_tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('implementasi_tindakan', function (Blueprint $table) {
                    $table->index(['implementasi_tindakan_id', 'tindakan_id'], 'implementasi_tindakan_implementasi_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on implementasi_tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('index_remunerasi_detail')) {
            try {
                Schema::table('index_remunerasi_detail', function (Blueprint $table) {
                    $table->primary(['index_remunerasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('index_remunerasi_detail', function (Blueprint $table) {
                    $table->index(['index_remunerasi_detail_id', 'index_remunerasi_id', 'profesi_id', 'bagian_id', 'jabatan_id', 'relation_id'], 'index_remunerasi_detail_index_remunerasi_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('index_remunerasi_pegawai')) {
            try {
                Schema::table('index_remunerasi_pegawai', function (Blueprint $table) {
                    $table->primary(['index_remunerasi_pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi_pegawai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('index_remunerasi_pegawai', function (Blueprint $table) {
                    $table->index(['index_remunerasi_pegawai_id', 'pegawai_id', 'bagian_id', 'jabatan_id'], 'index_remunerasi_pegawai_index_remunerasi_pegawai_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi_pegawai: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('index_remunerasi')) {
            try {
                Schema::table('index_remunerasi', function (Blueprint $table) {
                    $table->primary(['index_remunerasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('index_remunerasi', function (Blueprint $table) {
                    $table->index(['index_remunerasi_id', 'schema_index', 'index_interfensi', 'index_interfensi_rules'], 'index_remunerasi_index_remunerasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on index_remunerasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('instruksi_rawat')) {
            try {
                Schema::table('instruksi_rawat', function (Blueprint $table) {
                    $table->primary(['instruksi_rawat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on instruksi_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('instruksi_rawat', function (Blueprint $table) {
                    $table->index(['instruksi_rawat_id', 'registrasi_id', 'user_id'], 'instruksi_rawat_instruksi_rawat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on instruksi_rawat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('integrasi_gabung_bill_simrs_inacbg')) {
            try {
                Schema::table('integrasi_gabung_bill_simrs_inacbg', function (Blueprint $table) {
                    $table->primary(['integrasi_gabung_bill_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on integrasi_gabung_bill_simrs_inacbg: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('integrasi_gabung_bill_simrs_inacbg', function (Blueprint $table) {
                    $table->index(['integrasi_gabung_bill_id', 'registrasi_id_head'], 'integrasi_gabung_bill_simrs_inacbg_integrasi_gabung_bill_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on integrasi_gabung_bill_simrs_inacbg: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('integrasi_simrs_inacbg')) {
            try {
                Schema::table('integrasi_simrs_inacbg', function (Blueprint $table) {
                    $table->primary(['integrasi_simrs_inacbg_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on integrasi_simrs_inacbg: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('integrasi_simrs_inacbg', function (Blueprint $table) {
                    $table->index(['integrasi_simrs_inacbg_id', 'registrasi_id_head', 'jenis_rawat', 'tgl_masuk'], 'integrasi_simrs_inacbg_integrasi_simrs_inacbg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on integrasi_simrs_inacbg: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('intervensi')) {
            try {
                Schema::table('intervensi', function (Blueprint $table) {
                    $table->primary(['intervensi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on intervensi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('intervensi', function (Blueprint $table) {
                    $table->index(['intervensi_id', 'kode_intervensi', 'diagnosa_keperawatan_rj_id'], 'intervensi_intervensi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on intervensi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jabatan')) {
            try {
                Schema::table('jabatan', function (Blueprint $table) {
                    $table->primary(['jabatan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jabatan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jabatan', function (Blueprint $table) {
                    $table->index(['jabatan_id', 'nama_jabatan', 'status_batal'], 'jabatan_jabatan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jabatan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jadwal_dokter_igd')) {
            try {
                Schema::table('jadwal_dokter_igd', function (Blueprint $table) {
                    $table->primary(['jadwal_dokter_igd_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_dokter_igd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jadwal_dokter_igd', function (Blueprint $table) {
                    $table->index(['jadwal_dokter_igd_id', 'pegawai_id', 'bagian_id', 'spesialisasi_id', 'hari'], 'jadwal_dokter_igd_jadwal_dokter_igd_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_dokter_igd: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jadwal_dokter')) {
            try {
                Schema::table('jadwal_dokter', function (Blueprint $table) {
                    $table->primary(['jadwal_dokter_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_dokter: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jadwal_dokter', function (Blueprint $table) {
                    $table->index(['status_batal', 'hari'], 'idx_jadwal_dokter01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_dokter: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jadwal_dokter_temp')) {
            try {
                Schema::table('jadwal_dokter_temp', function (Blueprint $table) {
                    $table->primary(['jadwal_dokter_temp_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_dokter_temp: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jadwal_rehab_medik')) {
            try {
                Schema::table('jadwal_rehab_medik', function (Blueprint $table) {
                    $table->primary(['jadwal_rehab_medik_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_rehab_medik: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jadwal_rehab_medik', function (Blueprint $table) {
                    $table->index(['jadwal_rehab_medik_id', 'bagian_id', 'jenis_terapi', 'tanggal_slot'], 'jadwal_rehab_medik_jadwal_rehab_medik_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jadwal_rehab_medik: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jenis_diagnosa_diit')) {
            try {
                Schema::table('jenis_diagnosa_diit', function (Blueprint $table) {
                    $table->primary(['jenis_diagnosa_diit_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_diagnosa_diit: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jenis_diagnosa_diit', function (Blueprint $table) {
                    $table->index(['jenis_diagnosa_diit_id', 'status_batal', 'nama_diagnosa_diit'], 'jenis_diagnosa_diit_jenis_diagnosa_diit_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_diagnosa_diit: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jenis_diit')) {
            try {
                Schema::table('jenis_diit', function (Blueprint $table) {
                    $table->primary(['jenis_diit_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_diit: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jenis_diit', function (Blueprint $table) {
                    $table->index(['jenis_diit_id', 'nama_diit', 'status_batal'], 'jenis_diit_jenis_diit_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_diit: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('jenis_tindakan')) {
            try {
                Schema::table('jenis_tindakan', function (Blueprint $table) {
                    $table->primary(['jenis_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('jenis_tindakan', function (Blueprint $table) {
                    $table->index(['jenis_tindakan_id', 'status_batal', 'jenis_tindakan'], 'jenis_tindakan_jenis_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on jenis_tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kabupaten')) {
            try {
                Schema::table('kabupaten', function (Blueprint $table) {
                    $table->primary(['kabupaten_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kabupaten: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kabupaten', function (Blueprint $table) {
                    $table->index(['kabupaten_id', 'provinsi_id'], 'kabupaten_kabupaten_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kabupaten: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kartu_stock_batch')) {
            try {
                Schema::table('kartu_stock_batch', function (Blueprint $table) {
                    $table->primary(['kartu_stock_batch_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock_batch: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kartu_stock_global')) {
            try {
                Schema::table('kartu_stock_global', function (Blueprint $table) {
                    $table->primary(['kartu_stock_global_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock_global: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock_global', function (Blueprint $table) {
                    $table->index(['kartu_stock_global_id', 'barang_id', 'tgl_jam', 'bagian_id', 'bagian_id_transaksi', 'rekap_stock_opname_id'], 'kartu_stock_global_kartu_stock_global_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock_global: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kartu_stock')) {
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->primary(['kartu_stock_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['tgl_jam']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['no_bukti']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['nomor_batch']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kartu_stock', function (Blueprint $table) {
                    $table->index(['kartu_stock_id', 'barang_id', 'tgl_jam', 'bagian_id', 'bagian_id_transaksi', 'rekap_stock_opname_id'], 'kartu_stock_kartu_stock_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kartu_stock: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kecamatan')) {
            try {
                Schema::table('kecamatan', function (Blueprint $table) {
                    $table->primary(['kecamatan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kecamatan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kecamatan', function (Blueprint $table) {
                    $table->index(['kecamatan_id', 'kabupaten_id'], 'kecamatan_kecamatan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kecamatan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kelas_ruang')) {
            try {
                Schema::table('kelas_ruang', function (Blueprint $table) {
                    $table->primary(['kelas_ruang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kelas_ruang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kelas_ruang', function (Blueprint $table) {
                    $table->index(['kelas_ruang_id', 'kelas_bpjs'], 'idx_kelas_ruang01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kelas_ruang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kelas_ruang', function (Blueprint $table) {
                    $table->index(['kelas_ruang_id', 'kelas_bpjs', 'kelas_khusus', 'nama_kelas_ruang'], 'kelas_ruang_kelas_ruang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kelas_ruang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kelurahan')) {
            try {
                Schema::table('kelurahan', function (Blueprint $table) {
                    $table->primary(['kelurahan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kelurahan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kelurahan', function (Blueprint $table) {
                    $table->index(['kelurahan_id', 'kecamatan_id'], 'kelurahan_kelurahan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kelurahan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('komposisi')) {
            try {
                Schema::table('komposisi', function (Blueprint $table) {
                    $table->primary(['komposisi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on komposisi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('komposisi', function (Blueprint $table) {
                    $table->index(['komposisi_id'], 'komposisi_komposisi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on komposisi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_biaya_administrasi_detail')) {
            try {
                Schema::table('konfigurasi_biaya_administrasi_detail', function (Blueprint $table) {
                    $table->primary(['konfigurasi_biaya_administrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_biaya_administrasi_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_biaya_administrasi_nasabah')) {
            try {
                Schema::table('konfigurasi_biaya_administrasi_nasabah', function (Blueprint $table) {
                    $table->primary(['konfigurasi_biaya_administrasi_nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_biaya_administrasi_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_biaya_administrasi_nasabah', function (Blueprint $table) {
                    $table->index(['konfigurasi_biaya_administrasi_nasabah_id', 'nasabah_id'], 'konfigurasi_biaya_administrasi_nasabah_konfigurasi_biaya_admini');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_biaya_administrasi_nasabah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_biaya_administrasi')) {
            try {
                Schema::table('konfigurasi_biaya_administrasi', function (Blueprint $table) {
                    $table->primary(['konfigurasi_biaya_administrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_biaya_administrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_biaya_administrasi', function (Blueprint $table) {
                    $table->index(['konfigurasi_biaya_administrasi_id', 'status_batal', 'nama_konfigurasi_biaya_administrasi'], 'konfigurasi_biaya_administrasi_konfigurasi_biaya_administrasi_i');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_biaya_administrasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_integrasi')) {
            try {
                Schema::table('konfigurasi_integrasi', function (Blueprint $table) {
                    $table->primary(['konfigurasi_integrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_integrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_integrasi', function (Blueprint $table) {
                    $table->index(['konfigurasi_integrasi_id', 'consumer_id', 'tipe', 'kode_rs', 'kelas_rs'], 'konfigurasi_integrasi_konfigurasi_integrasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_integrasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_jasa_medis_detail')) {
            try {
                Schema::table('konfigurasi_jasa_medis_detail', function (Blueprint $table) {
                    $table->primary(['konfigurasi_jasa_medis_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_medis_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_jasa_medis_detail', function (Blueprint $table) {
                    $table->index(['konfigurasi_jasa_medis_id', 'konfigurasi_jasa_medis_detail_id', 'bagian_id', 'status_batal'], 'konfigurasi_jasa_medis_detail_konfigurasi_jasa_medis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_medis_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_jasa_medis')) {
            try {
                Schema::table('konfigurasi_jasa_medis', function (Blueprint $table) {
                    $table->primary(['konfigurasi_jasa_medis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_medis: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_jasa_medis', function (Blueprint $table) {
                    $table->index(['konfigurasi_jasa_medis_id', 'layanan_id', 'nasabah_id', 'jenis_tindakan'], 'konfigurasi_jasa_medis_konfigurasi_jasa_medis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_medis: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_jasa_non_medis_detail')) {
            try {
                Schema::table('konfigurasi_jasa_non_medis_detail', function (Blueprint $table) {
                    $table->primary(['konfigurasi_jasa_non_medis_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_non_medis_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_jasa_non_medis_detail', function (Blueprint $table) {
                    $table->index(['nasabah_id', 'konfigurasi_jasa_non_medis_id', 'konfigurasi_jasa_non_medis_detail_id', 'status_batal'], 'konfigurasi_jasa_non_medis_detail_nasabah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_non_medis_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_jasa_non_medis')) {
            try {
                Schema::table('konfigurasi_jasa_non_medis', function (Blueprint $table) {
                    $table->primary(['konfigurasi_jasa_non_medis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_non_medis: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_jasa_non_medis', function (Blueprint $table) {
                    $table->index(['konfigurasi_jasa_non_medis_id', 'persentase', 'nama_jasa', 'status_batal'], 'konfigurasi_jasa_non_medis_konfigurasi_jasa_non_medis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_jasa_non_medis: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_pembagi_jasmed_2')) {
            try {
                Schema::table('konfigurasi_pembagi_jasmed_2', function (Blueprint $table) {
                    $table->primary(['konfigurasi_pembagi_jasmed_2_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed_2: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_pembagi_jasmed_2', function (Blueprint $table) {
                    $table->index(['konfigurasi_pembagi_jasmed_id', 'konfigurasi_pembagi_jasmed_2_id', 'status_batal'], 'konfigurasi_pembagi_jasmed_2_konfigurasi_pembagi_jasmed_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed_2: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_pembagi_jasmed_3')) {
            try {
                Schema::table('konfigurasi_pembagi_jasmed_3', function (Blueprint $table) {
                    $table->primary(['konfigurasi_pembagi_jasmed_3_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed_3: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_pembagi_jasmed_3', function (Blueprint $table) {
                    $table->index(['konfigurasi_pembagi_jasmed_3_id', 'konfigurasi_pembagi_jasmed_2_id', 'status_batal'], 'konfigurasi_pembagi_jasmed_3_konfigurasi_pembagi_jasmed_3_id_id');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed_3: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_pembagi_jasmed')) {
            try {
                Schema::table('konfigurasi_pembagi_jasmed', function (Blueprint $table) {
                    $table->primary(['konfigurasi_pembagi_jasmed_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_pembagi_jasmed', function (Blueprint $table) {
                    $table->index(['bagian_id', 'jenis_rawat', 'referensi_bagian', 'konfigurasi_pembagi_jasmed_id', 'status_batal'], 'konfigurasi_pembagi_jasmed_bagian_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_pembagi_jasmed: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_satu_sehat')) {
            try {
                Schema::table('konfigurasi_satu_sehat', function (Blueprint $table) {
                    $table->primary(['konfigurasi_satu_sehat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_satu_sehat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_satu_sehat', function (Blueprint $table) {
                    $table->index(['konfigurasi_satu_sehat_id', 'client_id', 'kode_provinsi', 'kode_kabupaten', 'kode_kecamatan', 'kode_kelurahan'], 'konfigurasi_satu_sehat_konfigurasi_satu_sehat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_satu_sehat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfigurasi_tim_dokter')) {
            try {
                Schema::table('konfigurasi_tim_dokter', function (Blueprint $table) {
                    $table->primary(['konfigurasi_tim_dokter_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_tim_dokter: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfigurasi_tim_dokter', function (Blueprint $table) {
                    $table->index(['konfigurasi_tim_dokter_id', 'dpjp_user_id', 'status_batal'], 'konfigurasi_tim_dokter_konfigurasi_tim_dokter_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfigurasi_tim_dokter: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('konfirmasi_prb')) {
            try {
                Schema::table('konfirmasi_prb', function (Blueprint $table) {
                    $table->primary(['konfirmasi_prb_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfirmasi_prb: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('konfirmasi_prb', function (Blueprint $table) {
                    $table->index(['konfirmasi_prb_id', 'emr_id'], 'konfirmasi_prb_konfirmasi_prb_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on konfirmasi_prb: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kuitansi_kolektif')) {
            try {
                Schema::table('kuitansi_kolektif', function (Blueprint $table) {
                    $table->primary(['kuitansi_kolektif_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi_kolektif: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('kuitansi')) {
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->primary(['kuitansi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['input_time']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['kuitansi_no']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['kuitansi_tipe']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['bill_kasir_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['kuitansi_id', 'bill_kasir_id', 'registrasi_id', 'nasabah_id', 'bagian_id', 'tipe_bayar_id', 'pasien_id'], 'idx_kuitansi01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('kuitansi', function (Blueprint $table) {
                    $table->index(['kuitansi_id', 'kuitansi_tipe', 'tanggal_kuitansi', 'bill_kasir_id', 'registrasi_id', 'nasabah_id', 'tipe_bayar_id', 'bagian_id', 'bank_id', 'uang_muka_id'], 'kuitansi_kuitansi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on kuitansi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('lab_hasil')) {
            try {
                Schema::table('lab_hasil', function (Blueprint $table) {
                    $table->index(['no_lab']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('lab_hasil', function (Blueprint $table) {
                    $table->index(['order_lab_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('lab_hasil', function (Blueprint $table) {
                    $table->index(['lab_hasil_id', 'order_lab_id', 'bagian_id', 'user_id_dokter'], 'idx_lab_hasil01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('lab_hasil', function (Blueprint $table) {
                    $table->index(['lab_hasil_id', 'no_lab', 'order_lab_id', 'no_kunjungan', 'kode_sir', 'no_mr', 'bagian_id', 'user_id_dokter'], 'lab_hasil_lab_hasil_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('lab_hasil', function (Blueprint $table) {
                    $table->index(['no_lab'], 'lab_hasil_no_lab_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('lab_hasil_teks')) {
            try {
                Schema::table('lab_hasil_teks', function (Blueprint $table) {
                    $table->index(['no_lab', 'kode_test'], 'lab_hasil_teks_no_lab_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil_teks: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('lab_hasil_teks', function (Blueprint $table) {
                    $table->index(['no_lab', 'kode_test'], 'lab_hasil_teks_no_lab_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on lab_hasil_teks: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('level_detail')) {
            try {
                Schema::table('level_detail', function (Blueprint $table) {
                    $table->primary(['level_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on level_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('level_detail', function (Blueprint $table) {
                    $table->index(['level_detail_id', 'level_id', 'user_id', 'sub_menu_id'], 'level_detail_level_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on level_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('level')) {
            try {
                Schema::table('level', function (Blueprint $table) {
                    $table->primary(['level_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on level: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('level', function (Blueprint $table) {
                    $table->index(['level_id', 'status_batal', 'nama_level'], 'level_level_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on level: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('log_nasabah')) {
            try {
                Schema::table('log_nasabah', function (Blueprint $table) {
                    $table->primary(['log_nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on log_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('log_nasabah', function (Blueprint $table) {
                    $table->index(['log_nasabah_id', 'pasien_id', 'nasabah_id_lama', 'nasabah_id_baru', 'registrasi_id'], 'log_nasabah_log_nasabah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on log_nasabah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('luaran')) {
            try {
                Schema::table('luaran', function (Blueprint $table) {
                    $table->primary(['luaran_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on luaran: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('luaran', function (Blueprint $table) {
                    $table->index(['luaran_id', 'diagnosa_keperawatan_rj_id', 'kode_luaran'], 'luaran_luaran_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on luaran: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('makanan')) {
            try {
                Schema::table('makanan', function (Blueprint $table) {
                    $table->primary(['makanan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on makanan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('makanan', function (Blueprint $table) {
                    $table->index(['makanan_id', 'status_batal', 'nama_makanan'], 'makanan_makanan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on makanan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('manage_barang_aset')) {
            try {
                Schema::table('manage_barang_aset', function (Blueprint $table) {
                    $table->primary(['manage_barang_aset_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on manage_barang_aset: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('manage_barang_aset', function (Blueprint $table) {
                    $table->index(['manage_barang_aset_id', 'aset_detail_id', 'barang_id', 'kirim_user_id', 'terima_user_id', 'kirim_balik_user_id', 'terima_balik_user_id'], 'manage_barang_aset_manage_barang_aset_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on manage_barang_aset: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('mapping_farmasi')) {
            try {
                Schema::table('mapping_farmasi', function (Blueprint $table) {
                    $table->primary(['mapping_farmasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mapping_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('mapping_farmasi', function (Blueprint $table) {
                    $table->index(['mapping_farmasi_id', 'bagian_id', 'bagian_farmasi_id'], 'mapping_farmasi_mapping_farmasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mapping_farmasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('mapping_poli_bpjs')) {
            try {
                Schema::table('mapping_poli_bpjs', function (Blueprint $table) {
                    $table->primary(['mapping_poli_bpjs_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mapping_poli_bpjs: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('mapping_poli_bpjs', function (Blueprint $table) {
                    $table->index(['mapping_poli_bpjs_id', 'bagian_id', 'kode_poli_bpjs'], 'mapping_poli_bpjs_mapping_poli_bpjs_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mapping_poli_bpjs: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('master_antrian_penunjang')) {
            try {
                Schema::table('master_antrian_penunjang', function (Blueprint $table) {
                    $table->primary(['master_antrian_penunjang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on master_antrian_penunjang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('master_antrian_penunjang', function (Blueprint $table) {
                    $table->index(['master_antrian_penunjang_id', 'head_bagian_id', 'input_time'], 'master_antrian_penunjang_master_antrian_penunjang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on master_antrian_penunjang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_racik_detail')) {
            try {
                Schema::table('material_racik_detail', function (Blueprint $table) {
                    $table->primary(['material_racik_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_racik_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_racik_detail', function (Blueprint $table) {
                    $table->index(['material_racik_detail_id', 'material_racik_id', 'peresepan_obat_id', 'barang_id', 'bagian_id'], 'material_racik_detail_material_racik_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_racik_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_racik')) {
            try {
                Schema::table('material_racik', function (Blueprint $table) {
                    $table->primary(['material_racik_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_racik: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_racik', function (Blueprint $table) {
                    $table->index(['material_racik_id', 'peresepan_obat_id', 'peresepan_obat_detail_id'], 'material_racik_material_racik_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_racik: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_request_approval')) {
            try {
                Schema::table('material_request_approval', function (Blueprint $table) {
                    $table->primary(['material_request_approval_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_approval: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_request_approval', function (Blueprint $table) {
                    $table->index(['material_request_approval_id', 'kategori_barang_spesifik', 'user_id'], 'material_request_approval_material_request_approval_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_approval: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_request_detail_partisi')) {
            try {
                Schema::table('material_request_detail_partisi', function (Blueprint $table) {
                    $table->primary(['material_request_detail_partisi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_detail_partisi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_request_detail_partisi', function (Blueprint $table) {
                    $table->index(['material_request_detail_partisi_id', 'pemesanan_brg_id', 'principal_id', 'supplier_id'], 'material_request_detail_partisi_material_request_detail_partisi');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_detail_partisi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_request_detail')) {
            try {
                Schema::table('material_request_detail', function (Blueprint $table) {
                    $table->primary(['material_request_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_request_detail', function (Blueprint $table) {
                    $table->index(['material_request_detail_id', 'material_request_id', 'principal_id', 'supplier_id', 'barang_id', 'pemesanan_brg_id'], 'material_request_detail_material_request_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_request')) {
            try {
                Schema::table('material_request', function (Blueprint $table) {
                    $table->primary(['material_request_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_request', function (Blueprint $table) {
                    $table->index(['material_request_id', 'acc_wadir_user_id', 'acc_ppk_user_id', 'draft_material_request_id'], 'material_request_material_request_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('material_request_template_approved')) {
            try {
                Schema::table('material_request_template_approved', function (Blueprint $table) {
                    $table->primary(['material_request_template_approved_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_template_approved: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('material_request_template_approved', function (Blueprint $table) {
                    $table->index(['material_request_template_approved_id', 'client_id', 'jenis_konfigurasi', 'level_jabatan', 'level_approval'], 'material_request_template_approved_material_request_template_ap');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on material_request_template_approved: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('menu')) {
            try {
                Schema::table('menu', function (Blueprint $table) {
                    $table->primary(['menu_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on menu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('menu', function (Blueprint $table) {
                    $table->index(['menu_id', 'modul_id', 'status_batal'], 'idx_menu01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on menu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('menu', function (Blueprint $table) {
                    $table->index(['menu_id', 'modul_id', 'urutan_menu'], 'menu_menu_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on menu: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('migrate_tindakan')) {
            try {
                Schema::table('migrate_tindakan', function (Blueprint $table) {
                    $table->primary(['migrate_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on migrate_tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('migrate_tindakan', function (Blueprint $table) {
                    $table->index(['migrate_tindakan_id', 'bagian', 'tindakan'], 'migrate_tindakan_migrate_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on migrate_tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('modul')) {
            try {
                Schema::table('modul', function (Blueprint $table) {
                    $table->primary(['modul_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on modul: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('modul', function (Blueprint $table) {
                    $table->index(['modul_id', 'urutan_modul'], 'modul_modul_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on modul: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('mortalitas')) {
            try {
                Schema::table('mortalitas', function (Blueprint $table) {
                    $table->primary(['mortalitas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mortalitas: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('mortalitas', function (Blueprint $table) {
                    $table->index(['mortalitas_id', 'pasien_id', 'bagian_id', 'pegawai_id', 'registrasi_detail_id', 'icd_id'], 'mortalitas_mortalitas_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on mortalitas: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('nasabah')) {
            try {
                Schema::table('nasabah', function (Blueprint $table) {
                    $table->primary(['nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('nasabah', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('nasabah', function (Blueprint $table) {
                    $table->index(['nama_nasabah']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('nasabah', function (Blueprint $table) {
                    $table->index(['nasabah_id', 'jenis_nasabah', 'tipe_biaya'], 'idx_nasabah01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('nasabah', function (Blueprint $table) {
                    $table->index(['nasabah_id', 'jenis_nasabah', 'email_nasabah'], 'nasabah_nasabah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on nasabah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('notif_penyakit')) {
            try {
                Schema::table('notif_penyakit', function (Blueprint $table) {
                    $table->primary(['notif_penyakit_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on notif_penyakit: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('notif_penyakit', function (Blueprint $table) {
                    $table->index(['notif_penyakit_id', 'order_lab_id', 'pasien_id', 'start_user_id', 'finish_user_id'], 'notif_penyakit_notif_penyakit_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on notif_penyakit: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('obat_bpjs')) {
            try {
                Schema::table('obat_bpjs', function (Blueprint $table) {
                    $table->primary(['kodeobat']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on obat_bpjs: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('objek_form_control')) {
            try {
                Schema::table('objek_form_control', function (Blueprint $table) {
                    $table->primary(['objek_form_control_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on objek_form_control: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('objek_form_control', function (Blueprint $table) {
                    $table->index(['objek_form_control_id', 'bagian_id', 'form_id', 'objek_id'], 'objek_form_control_objek_form_control_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on objek_form_control: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('objek')) {
            try {
                Schema::table('objek', function (Blueprint $table) {
                    $table->primary(['objek_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on objek: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('objek', function (Blueprint $table) {
                    $table->index(['objek_id', 'status_batal'], 'objek_objek_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on objek: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_lab_detail')) {
            try {
                Schema::table('order_lab_detail', function (Blueprint $table) {
                    $table->primary(['order_lab_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_lab_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('order_lab_detail', function (Blueprint $table) {
                    $table->index(['order_lab_detail_id', 'order_lab_id', 'tindakan_id', 'tindakan_group_id'], 'order_lab_detail_order_lab_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_lab_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_lab')) {
            try {
                Schema::table('order_lab', function (Blueprint $table) {
                    $table->primary(['order_lab_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_lab: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('order_lab', function (Blueprint $table) {
                    $table->index(['order_lab_id', 'registrasi_detail_id', 'pasien_id', 'kirim_user_id', 'bagian_id'], 'order_lab_order_lab_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_lab: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_mikrobiologi_detail')) {
            try {
                Schema::table('order_mikrobiologi_detail', function (Blueprint $table) {
                    $table->primary(['order_mikrobiologi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_mikrobiologi_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_mikrobiologi')) {
            try {
                Schema::table('order_mikrobiologi', function (Blueprint $table) {
                    $table->primary(['order_mikrobiologi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_mikrobiologi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_rad_detail')) {
            try {
                Schema::table('order_rad_detail', function (Blueprint $table) {
                    $table->primary(['order_rad_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rad_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('order_rad_detail', function (Blueprint $table) {
                    $table->index(['order_rad_detail_id', 'order_rad_id', 'tindakan_id'], 'order_rad_detail_order_rad_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rad_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_rad')) {
            try {
                Schema::table('order_rad', function (Blueprint $table) {
                    $table->primary(['order_rad_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rad: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('order_rad', function (Blueprint $table) {
                    $table->index(['order_rad_id', 'registrasi_detail_id', 'pasien_id', 'bagian_id', 'kirim_user_id'], 'order_rad_order_rad_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rad: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('order_rehab_medik')) {
            try {
                Schema::table('order_rehab_medik', function (Blueprint $table) {
                    $table->primary(['order_rehab_medik_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rehab_medik: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('order_rehab_medik', function (Blueprint $table) {
                    $table->index(['registrasi_id', 'order_rehab_medik_id', 'bagian_id_asal', 'bagian_id_slot', 'status_batal'], 'order_rehab_medik_registrasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on order_rehab_medik: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('paket_mcu_detail')) {
            try {
                Schema::table('paket_mcu_detail', function (Blueprint $table) {
                    $table->primary(['paket_mcu_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_mcu_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('paket_mcu_detail', function (Blueprint $table) {
                    $table->index(['paket_mcu_detail_id', 'mod_user_id', 'tindakan_id', 'paket_mcu_id', 'bagian_id'], 'paket_mcu_detail_paket_mcu_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_mcu_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('paket_mcu')) {
            try {
                Schema::table('paket_mcu', function (Blueprint $table) {
                    $table->primary(['paket_mcu_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_mcu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('paket_mcu', function (Blueprint $table) {
                    $table->index(['paket_mcu_id', 'nasabah_id'], 'paket_mcu_paket_mcu_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_mcu: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('paket_peresepan')) {
            try {
                Schema::table('paket_peresepan', function (Blueprint $table) {
                    $table->primary(['paket_peresepan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_peresepan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('paket_peresepan', function (Blueprint $table) {
                    $table->index(['paket_peresepan_id', 'nama_paket_peresepan', 'jenis_paket'], 'paket_peresepan_paket_peresepan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_peresepan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('paket_tindakan')) {
            try {
                Schema::table('paket_tindakan', function (Blueprint $table) {
                    $table->primary(['paket_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('paket_tindakan', function (Blueprint $table) {
                    $table->index(['paket_tindakan_id', 'tindakan_id', 'bagian_id', 'ref_tindakan_id', 'tindakan_group_id'], 'paket_tindakan_paket_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on paket_tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pasien_activity')) {
            try {
                Schema::table('pasien_activity', function (Blueprint $table) {
                    $table->primary(['pasien_activity_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_activity: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_activity', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_activity: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_activity', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_activity: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_activity', function (Blueprint $table) {
                    $table->index(['pasien_activity_id', 'registrasi_id', 'registrasi_detail_id', 'identity_tabel_id', 'user_id'], 'pasien_activity_pasien_activity_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_activity: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pasien_gabung')) {
            try {
                Schema::table('pasien_gabung', function (Blueprint $table) {
                    $table->primary(['pasien_gabung_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_gabung: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_gabung', function (Blueprint $table) {
                    $table->index(['pasien_gabung_id', 'pasien_id', 'pasien_id_gabung'], 'pasien_gabung_pasien_gabung_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_gabung: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pasien_nasabah')) {
            try {
                Schema::table('pasien_nasabah', function (Blueprint $table) {
                    $table->primary(['pasien_nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_nasabah', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_nasabah', function (Blueprint $table) {
                    $table->index(['nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_nasabah', function (Blueprint $table) {
                    $table->index(['pasien_nasabah_id', 'pasien_id', 'nasabah_id'], 'idx_pasien_nasabah01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_nasabah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien_nasabah', function (Blueprint $table) {
                    $table->index(['pasien_nasabah_id', 'pasien_id', 'nasabah_id', 'hak_kelas_id', 'no_peserta'], 'pasien_nasabah_pasien_nasabah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien_nasabah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pasien')) {
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->primary(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->index(['nama_pasien']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->index(['no_mr']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->index(['tgl_lahir']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->index(['ktp']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->index(['pasien_id', 'no_mr', 'tgl_lahir', 'ktp', 'no_rfid'], 'pasien_pasien_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pasien', function (Blueprint $table) {
                    $table->unique(['no_mr'], 'unique_no_mr');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pasien: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pegawai')) {
            try {
                Schema::table('pegawai', function (Blueprint $table) {
                    $table->primary(['pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pegawai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pegawai', function (Blueprint $table) {
                    $table->index(['pegawai_id', 'status_batal', 'profesi_id'], 'idx_pegawai01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pegawai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pegawai', function (Blueprint $table) {
                    $table->index(['profesi_id', 'status_batal'], 'pegawai_de_idx_profesi');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pegawai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pegawai', function (Blueprint $table) {
                    $table->index(['pegawai_id', 'profesi_id', 'bagian_id', 'jabatan_id', 'nik'], 'pegawai_pegawai_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pegawai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pegawai', function (Blueprint $table) {
                    $table->index(['sub_id', 'karu_id', 'katim_id'], 'pegawai_sub_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pegawai: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pelaporan')) {
            try {
                Schema::table('pelaporan', function (Blueprint $table) {
                    $table->primary(['laporan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pelaporan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pemakaian_obat_farmasi')) {
            try {
                Schema::table('pemakaian_obat_farmasi', function (Blueprint $table) {
                    $table->primary(['pemakaian_obat_farmasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemakaian_obat_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pemakaian_obat_farmasi', function (Blueprint $table) {
                    $table->index(['pemakaian_obat_farmasi_id', 'peresepan_obat_detail_id', 'barang_id'], 'pemakaian_obat_farmasi_pemakaian_obat_farmasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemakaian_obat_farmasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pemeriksaan_rad')) {
            try {
                Schema::table('pemeriksaan_rad', function (Blueprint $table) {
                    $table->primary(['pemeriksaan_rad_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemeriksaan_rad: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pemeriksaan_rad', function (Blueprint $table) {
                    $table->index(['pemeriksaan_rad_id', 'order_rad_id', 'alat_id', 'user_id_radiografer'], 'pemeriksaan_rad_pemeriksaan_rad_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemeriksaan_rad: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pemesanan_brg_detail')) {
            try {
                Schema::table('pemesanan_brg_detail', function (Blueprint $table) {
                    $table->primary(['pemesanan_brg_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemesanan_brg_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pemesanan_brg_detail', function (Blueprint $table) {
                    $table->index(['pemesanan_brg_id', 'barang_id', 'principal_id', 'bagian_id', 'pemesanan_brg_detail_id', 'status_batal'], 'pemesanan_brg_detail_pemesanan_brg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemesanan_brg_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pemesanan_brg')) {
            try {
                Schema::table('pemesanan_brg', function (Blueprint $table) {
                    $table->primary(['pemesanan_brg_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemesanan_brg: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pemesanan_brg', function (Blueprint $table) {
                    $table->index(['pemesanan_brg_id', 'kode_pesanan', 'supplier_id', 'acc_user_id', 'tgl_revisi', 'revisi_user_id'], 'pemesanan_brg_pemesanan_brg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemesanan_brg: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pemusnahan_barang')) {
            try {
                Schema::table('pemusnahan_barang', function (Blueprint $table) {
                    $table->primary(['pemusnahan_barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemusnahan_barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pemusnahan_barang', function (Blueprint $table) {
                    $table->index(['pemusnahan_barang_id', 'stock_depo_real_id', 'bagian_id', 'barang_id', 'nomor_batch', 'tgl_expired'], 'pemusnahan_barang_pemusnahan_barang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pemusnahan_barang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('penanggung_rawat')) {
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->primary(['penanggung_rawat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['kirim_user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['rawat_user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['rawat_user_id', 'status_batal'], 'idx_penanggung_rawat01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penanggung_rawat', function (Blueprint $table) {
                    $table->index(['penanggung_rawat_id', 'registrasi_id', 'kirim_user_id', 'rawat_user_id'], 'penanggung_rawat_penanggung_rawat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penanggung_rawat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('penerimaan_brg_detail')) {
            try {
                Schema::table('penerimaan_brg_detail', function (Blueprint $table) {
                    $table->primary(['penerimaan_brg_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penerimaan_brg_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penerimaan_brg_detail', function (Blueprint $table) {
                    $table->index(['penerimaan_brg_id', 'barang_id', 'penerimaan_brg_detail_id', 'status_batal'], 'penerimaan_brg_detail_penerimaan_brg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penerimaan_brg_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('penerimaan_brg')) {
            try {
                Schema::table('penerimaan_brg', function (Blueprint $table) {
                    $table->primary(['penerimaan_brg_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penerimaan_brg: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('penerimaan_brg', function (Blueprint $table) {
                    $table->index(['penerimaan_brg_id', 'pemesanan_brg_id', 'kirim_bagian_id', 'terima_bagian_id', 'permintaan_brg_id'], 'penerimaan_brg_penerimaan_brg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penerimaan_brg: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('penyakit')) {
            try {
                Schema::table('penyakit', function (Blueprint $table) {
                    $table->primary(['penyakit_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on penyakit: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('perencanaan_pembelian')) {
            try {
                Schema::table('perencanaan_pembelian', function (Blueprint $table) {
                    $table->primary(['perencanaan_pembelian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on perencanaan_pembelian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('perencanaan_pembelian', function (Blueprint $table) {
                    $table->index(['perencanaan_pembelian_id', 'nasabah_id', 'barang_id', 'bagian_id'], 'perencanaan_pembelian_perencanaan_pembelian_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on perencanaan_pembelian: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat_detail')) {
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->index(['peresepan_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->index(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->index(['barang_jenis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_detail', function (Blueprint $table) {
                    $table->index(['peresepan_obat_detail_id', 'peresepan_obat_id', 'barang_id', 'substitusi_barang_id', 'barang_jenis_id', 'stop_user_id'], 'peresepan_obat_detail_peresepan_obat_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat_dispense_apotek')) {
            try {
                Schema::table('peresepan_obat_dispense_apotek', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_dispense_apotek_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense_apotek: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat_dispense_head')) {
            try {
                Schema::table('peresepan_obat_dispense_head', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_dispense_head_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense_head: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense_head', function (Blueprint $table) {
                    $table->index(['peresepan_obat_dispense_head_id', 'peresepan_obat_id', 'bagian_id_penanggung', 'dokter_id_kirim', 'bagian_id', 'pasien_id'], 'peresepan_obat_dispense_head_peresepan_obat_dispense_head_id_id');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense_head: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat_dispense')) {
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_dispense_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->index(['input_time']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->index(['peresepan_obat_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->index(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->index(['nomor_batch']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_dispense', function (Blueprint $table) {
                    $table->index(['peresepan_obat_dispense_id', 'peresepan_obat_detail_id', 'barang_id', 'barang_jenis_id', 'bagian_id_dispense'], 'peresepan_obat_dispense_peresepan_obat_dispense_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_dispense: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat_retur')) {
            try {
                Schema::table('peresepan_obat_retur', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_retur_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_retur: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat_retur', function (Blueprint $table) {
                    $table->index(['peresepan_obat_retur_id', 'peresepan_obat_dispense_id', 'peresepan_obat_detail_id', 'peresepan_obat_id', 'barang_id'], 'peresepan_obat_retur_peresepan_obat_retur_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat_retur: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('peresepan_obat')) {
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->primary(['peresepan_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['tgl_resep']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['referensi_peresepan_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['tgl_resep'], 'peresepan_obat_de_idx_year_2024');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['tgl_resep'], 'peresepan_obat_de_idx_year_2025');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['peresepan_obat_id', 'user_id', 'pasien_id', 'registrasi_detail_id', 'pegawai_id', 'user_id_start', 'user_id_end'], 'peresepan_obat_peresepan_obat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('peresepan_obat', function (Blueprint $table) {
                    $table->index(['tgl_resep'], 'peresepan_obat_tgl_resep_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on peresepan_obat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('perjanjian_kerjasama')) {
            try {
                Schema::table('perjanjian_kerjasama', function (Blueprint $table) {
                    $table->primary(['perjanjian_kerjasama_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on perjanjian_kerjasama: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('perjanjian_kerjasama', function (Blueprint $table) {
                    $table->index(['perjanjian_kerjasama_id', 'nasabah_id', 'supplier_id', 'tgl_pks_awal', 'jenis_pks', 'tgl_pks_akhir'], 'perjanjian_kerjasama_perjanjian_kerjasama_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on perjanjian_kerjasama: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('permintaan_brg_detail')) {
            try {
                Schema::table('permintaan_brg_detail', function (Blueprint $table) {
                    $table->primary(['permintaan_brg_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on permintaan_brg_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('permintaan_brg_detail', function (Blueprint $table) {
                    $table->index(['permintaan_brg_detail_id', 'permintaan_brg_id', 'barang_id', 'acc_user_id', 'terima_user_id', 'tgl_terima', 'tgl_acc', 'tgl_expired'], 'permintaan_brg_detail_permintaan_brg_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on permintaan_brg_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('permintaan_brg')) {
            try {
                Schema::table('permintaan_brg', function (Blueprint $table) {
                    $table->primary(['permintaan_brg_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on permintaan_brg: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('permintaan_brg', function (Blueprint $table) {
                    $table->index(['permintaan_brg_id', 'urutan_mutasi', 'tgl_mutasi', 'minta_bagian_id', 'kirim_bagian_id', 'tgl_acc', 'acc_user_id', 'tgl_terima', 'terima_user_id'], 'permintaan_brg_permintaan_brg_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on permintaan_brg: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pesan_bedah')) {
            try {
                Schema::table('pesan_bedah', function (Blueprint $table) {
                    $table->primary(['pesan_bedah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_bedah', function (Blueprint $table) {
                    $table->index(['pesan_bedah_id', 'pasien_id', 'nasabah_id', 'bagian_id', 'kelas_id', 'pegawai_id', 'registrasi_detail_id', 'tindakan_id'], 'pesan_bedah_pesan_bedah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_bedah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pesan_makanan_monitoring_asupan')) {
            try {
                Schema::table('pesan_makanan_monitoring_asupan', function (Blueprint $table) {
                    $table->primary(['pesan_makanan_monitoring_asupan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_makanan_monitoring_asupan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_makanan_monitoring_asupan', function (Blueprint $table) {
                    $table->index(['pesan_makanan_monitoring_asupan_id', 'registrasi_id', 'makanan_id'], 'pesan_makanan_monitoring_asupan_pesan_makanan_monitoring_asupan');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_makanan_monitoring_asupan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pesan_slot_bedah')) {
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->primary(['pesan_slot_bedah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['emr_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['tgl_rencana_operasi']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['tgl_jam_bedah']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['slot_kamar_bedah']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['pesan_slot_bedah_id', 'emr_id', 'registrasi_detail_id', 'tgl_rencana_operasi', 'tim_bedah_id', 'slot_kamar_bedah', 'kelas_id', 'bagian_id'], 'pesan_slot_bedah_pesan_slot_bedah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pesan_slot_bedah', function (Blueprint $table) {
                    $table->index(['tgl_jam_bedah'], 'pesan_slot_bedah_tgl_jam_bedah_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pesan_slot_bedah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pindah_dpjp')) {
            try {
                Schema::table('pindah_dpjp', function (Blueprint $table) {
                    $table->primary(['pindah_dpjp_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pindah_dpjp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pindah_dpjp', function (Blueprint $table) {
                    $table->index(['pindah_dpjp_id', 'registrasi_detail_id', 'dokter_awal_dpjp', 'dokter_pindah_dpjp', 'tgl_masuk'], 'pindah_dpjp_pindah_dpjp_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pindah_dpjp: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('pola_diskon')) {
            try {
                Schema::table('pola_diskon', function (Blueprint $table) {
                    $table->primary(['pola_diskon_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pola_diskon: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('pola_diskon', function (Blueprint $table) {
                    $table->index(['pola_diskon_id', 'nasabah_id', 'tindakan_detail_id'], 'pola_diskon_pola_diskon_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on pola_diskon: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('principal')) {
            try {
                Schema::table('principal', function (Blueprint $table) {
                    $table->primary(['principal_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on principal: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('principal', function (Blueprint $table) {
                    $table->index(['principal_id', 'nama_principal'], 'principal_principal_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on principal: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('profesi')) {
            try {
                Schema::table('profesi', function (Blueprint $table) {
                    $table->primary(['profesi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on profesi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('profesi', function (Blueprint $table) {
                    $table->index(['profesi_id', 'nama_profesi'], 'profesi_profesi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on profesi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('provinsi')) {
            try {
                Schema::table('provinsi', function (Blueprint $table) {
                    $table->primary(['provinsi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on provinsi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('provinsi', function (Blueprint $table) {
                    $table->index(['provinsi_id', 'nama_provinsi'], 'provinsi_provinsi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on provinsi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('registrasi_detail')) {
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->primary(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['tgl_daftar']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['kelas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['asal_daftar']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id', 'registrasi_id', 'bagian_id', 'status_batal'], 'idx_registrasi_detail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id'], 'idx_registrasi_detail_status');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_detail', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id', 'registrasi_id', 'status_batal', 'bagian_id', 'kelas_id', 'hak_kelas_id', 'bagian_asal_id'], 'registrasi_detail_registrasi_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('registrasi_igd')) {
            try {
                Schema::table('registrasi_igd', function (Blueprint $table) {
                    $table->primary(['registrasi_igd_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_igd: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_igd', function (Blueprint $table) {
                    $table->index(['registrasi_igd_id', 'registrasi_id'], 'registrasi_igd_registrasi_igd_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_igd: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('registrasi')) {
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->primary(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['pasien_nasabah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['referensi_registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['tgl_masuk']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['tgl_keluar']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['jenis_rawat']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['pasien_nasabah_id_2']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['pasien_nasabah_id_3']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['registrasi_id', 'pasien_id', 'pasien_nasabah_id', 'jenis_rawat'], 'idx_registrasi03');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['jenis_rawat', 'status_batal'], 'registrasi_de_idx_jenis_rawat');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['registrasi_id', 'pasien_id', 'pasien_nasabah_id', 'referensi_registrasi_id', 'tgl_masuk', 'tgl_keluar', 'status_batal'], 'registrasi_registrasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi', function (Blueprint $table) {
                    $table->index(['tgl_masuk']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('registrasi_urut')) {
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->primary(['registrasi_urut_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['pegawai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['urutan']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['tgl_urut']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['status_batal', 'bagian_id', 'pegawai_id'], 'idx_registrasi_urut01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['registrasi_detail_id', 'registrasi_urut_id', 'status_batal', 'pegawai_id', 'bagian_id', 'urutan'], 'registrasi_urut_registrasi_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('registrasi_urut', function (Blueprint $table) {
                    $table->index(['registrasi_urut_id', 'registrasi_detail_id', 'pegawai_id', 'bagian_id'], 'registrasi_urut_registrasi_urut_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on registrasi_urut: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('rekap_gizi')) {
            try {
                Schema::table('rekap_gizi', function (Blueprint $table) {
                    $table->primary(['rekap_gizi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rekap_gizi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rekap_gizi', function (Blueprint $table) {
                    $table->index(['rekap_gizi_id', 'bagian_id'], 'rekap_gizi_rekap_gizi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rekap_gizi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('rekap_stock_opname')) {
            try {
                Schema::table('rekap_stock_opname', function (Blueprint $table) {
                    $table->primary(['rekap_stock_opname_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rekap_stock_opname: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rekap_stock_opname', function (Blueprint $table) {
                    $table->index(['rekap_stock_opname_id', 'bagian_id', 'barang_id', 'stock_depo_real_id'], 'rekap_stock_opname_rekap_stock_opname_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rekap_stock_opname: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('rekapitulasi_jkn')) {
            try {
                Schema::table('rekapitulasi_jkn', function (Blueprint $table) {
                    $table->primary(['rekapitulasi_jkn_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rekapitulasi_jkn: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('restriksi_obat')) {
            try {
                Schema::table('restriksi_obat', function (Blueprint $table) {
                    $table->primary(['restriksi_obat_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on restriksi_obat: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('restriksi_obat', function (Blueprint $table) {
                    $table->index(['restriksi_obat_id', 'barang_id'], 'restriksi_obat_restriksi_obat_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on restriksi_obat: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('retur_obat_dispense')) {
            try {
                Schema::table('retur_obat_dispense', function (Blueprint $table) {
                    $table->primary(['retur_obat_dispense_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on retur_obat_dispense: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('riwayat_kll')) {
            try {
                Schema::table('riwayat_kll', function (Blueprint $table) {
                    $table->primary(['riwayat_kll_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on riwayat_kll: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('riwayat_kll', function (Blueprint $table) {
                    $table->index(['riwayat_kll_id', 'registrasi_id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id'], 'riwayat_kll_riwayat_kll_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on riwayat_kll: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('riwayat_tutup_billing')) {
            try {
                Schema::table('riwayat_tutup_billing', function (Blueprint $table) {
                    $table->primary(['riwayat_tutup_billing_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on riwayat_tutup_billing: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('riwayat_tutup_billing', function (Blueprint $table) {
                    $table->index(['registrasi_id', 'riwayat_tutup_billing_id', 'status_batal'], 'riwayat_tutup_billing_registrasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on riwayat_tutup_billing: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('ruang_poli')) {
            try {
                Schema::table('ruang_poli', function (Blueprint $table) {
                    $table->primary(['ruang_poli_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on ruang_poli: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('rujukan_pasien')) {
            try {
                Schema::table('rujukan_pasien', function (Blueprint $table) {
                    $table->primary(['rujukan_pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_pasien', function (Blueprint $table) {
                    $table->index(['rujukan_pasien_id', 'pasien_id', 'no_rujukan', 'kode_poli_bpjs'], 'idx_rujukan_pasien01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_pasien: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_pasien', function (Blueprint $table) {
                    $table->index(['rujukan_pasien_id', 'pasien_id', 'no_peserta', 'no_rujukan', 'tgl_rujukan', 'kode_poli_bpjs'], 'rujukan_pasien_rujukan_pasien_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_pasien: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('rujukan_sep')) {
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->primary(['rujukan_sep_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->index(['registrasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->index(['sep']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->index(['no_rujukan']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->index(['sep', 'no_rujukan'], 'idx_rujukan_sep01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('rujukan_sep', function (Blueprint $table) {
                    $table->index(['rujukan_sep_id', 'registrasi_id', 'no_rujukan', 'sep', 'tgl_cetakan'], 'rujukan_sep_rujukan_sep_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on rujukan_sep: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('satu_sehat_encounter')) {
            try {
                Schema::table('satu_sehat_encounter', function (Blueprint $table) {
                    $table->primary(['no_mr']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on satu_sehat_encounter: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('satu_sehat_response')) {
            try {
                Schema::table('satu_sehat_response', function (Blueprint $table) {
                    $table->primary(['satu_sehat_response_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on satu_sehat_response: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('satu_sehat_response', function (Blueprint $table) {
                    $table->index(['satu_sehat_response_id', 'id'], 'satu_sehat_response_satu_sehat_response_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on satu_sehat_response: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('selisih_kelas')) {
            try {
                Schema::table('selisih_kelas', function (Blueprint $table) {
                    $table->primary(['selisih_kelas_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on selisih_kelas: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('selisih_kelas_vip')) {
            try {
                Schema::table('selisih_kelas_vip', function (Blueprint $table) {
                    $table->primary(['selisih_kelas_vip_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on selisih_kelas_vip: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('sep_apotek')) {
            try {
                Schema::table('sep_apotek', function (Blueprint $table) {
                    $table->primary(['sep_apotek_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on sep_apotek: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('skdp')) {
            try {
                Schema::table('skdp', function (Blueprint $table) {
                    $table->primary(['skdp_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on skdp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('skdp', function (Blueprint $table) {
                    $table->index(['skdp_id', 'registrasi_detail_id', 'input_user_id'], 'idx_skdp01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on skdp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('skdp', function (Blueprint $table) {
                    $table->index(['skdp_id', 'registrasi_detail_id', 'no_skdp'], 'skdp_skdp_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on skdp: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('skrining_covid')) {
            try {
                Schema::table('skrining_covid', function (Blueprint $table) {
                    $table->primary(['skrining_covid_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on skrining_covid: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('skrining_covid', function (Blueprint $table) {
                    $table->index(['skrining_covid_id', 'input_time', 'pasien_id'], 'skrining_covid_skrining_covid_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on skrining_covid: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('slot')) {
            try {
                Schema::table('slot', function (Blueprint $table) {
                    $table->primary(['slot_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on slot: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('slot', function (Blueprint $table) {
                    $table->index(['slot_id', 'tim_bedah_id', 'pesan_slot_bedah_id'], 'idx_slot01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on slot: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('slot', function (Blueprint $table) {
                    $table->index(['slot_id', 'tim_bedah_id', 'pesan_slot_bedah_id', 'slot_kamar_bedah'], 'slot_slot_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on slot: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('spesial_person')) {
            try {
                Schema::table('spesial_person', function (Blueprint $table) {
                    $table->primary(['spesial_person_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesial_person: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('spesial_person', function (Blueprint $table) {
                    $table->index(['spesial_person_id', 'pasien_id'], 'idx_spesial_person01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesial_person: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('spesial_person', function (Blueprint $table) {
                    $table->index(['spesial_person_id', 'pasien_id'], 'spesial_person_spesial_person_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesial_person: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('spesialisasi_dokter')) {
            try {
                Schema::table('spesialisasi_dokter', function (Blueprint $table) {
                    $table->primary(['spesialisasi_dokter_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesialisasi_dokter: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('spesialisasi_dokter', function (Blueprint $table) {
                    $table->index(['spesialisasi_dokter_id', 'spesialisasi_id', 'pegawai_id'], 'spesialisasi_dokter_spesialisasi_dokter_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesialisasi_dokter: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('spesialisasi')) {
            try {
                Schema::table('spesialisasi', function (Blueprint $table) {
                    $table->primary(['spesialisasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesialisasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('spesialisasi', function (Blueprint $table) {
                    $table->index(['spesialisasi_id', 'nama_spesialisasi'], 'spesialisasi_spesialisasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on spesialisasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('status_kepegawaian')) {
            try {
                Schema::table('status_kepegawaian', function (Blueprint $table) {
                    $table->primary(['status_kepegawaian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on status_kepegawaian: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('status_kepegawaian', function (Blueprint $table) {
                    $table->index(['status_kepegawaian_id', 'nama_status_kepegawaian'], 'status_kepegawaian_status_kepegawaian_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on status_kepegawaian: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('stock_depo_pakai')) {
            try {
                Schema::table('stock_depo_pakai', function (Blueprint $table) {
                    $table->primary(['stock_depo_pakai_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_pakai: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_pakai', function (Blueprint $table) {
                    $table->index(['stock_depo_pakai_id', 'bagian_id', 'barang_id', 'nomor_batch'], 'stock_depo_pakai_stock_depo_pakai_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_pakai: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('stock_depo_real')) {
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->primary(['stock_depo_real_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['mod_user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['nomor_batch']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['tgl_expired']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['harga_jual']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_real', function (Blueprint $table) {
                    $table->index(['stock_depo_real_id', 'bagian_id', 'barang_id', 'nomor_batch', 'tgl_expired'], 'stock_depo_real_stock_depo_real_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_real: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('stock_depo_temp')) {
            try {
                Schema::table('stock_depo_temp', function (Blueprint $table) {
                    $table->primary(['stock_depo_temp_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_temp: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('stock_depo_temp', function (Blueprint $table) {
                    $table->index(['stock_depo_temp_id', 'bagian_id', 'barang_id', 'tgl_expired', 'nomor_batch'], 'stock_depo_temp_stock_depo_temp_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on stock_depo_temp: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('sub_menu')) {
            try {
                Schema::table('sub_menu', function (Blueprint $table) {
                    $table->primary(['sub_menu_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on sub_menu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('sub_menu', function (Blueprint $table) {
                    $table->index(['sub_menu_id', 'menu_id', 'status_batal', 'urutan_sub_menu'], 'idx_sub_menu01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on sub_menu: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('sub_menu', function (Blueprint $table) {
                    $table->index(['sub_menu_id', 'menu_id', 'urutan_sub_menu'], 'sub_menu_sub_menu_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on sub_menu: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('supplier_barang')) {
            try {
                Schema::table('supplier_barang', function (Blueprint $table) {
                    $table->primary(['supplier_barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on supplier_barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('supplier_barang', function (Blueprint $table) {
                    $table->index(['supplier_barang_id', 'supplier_id', 'barang_id', 'principal_id'], 'supplier_barang_supplier_barang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on supplier_barang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('supplier')) {
            try {
                Schema::table('supplier', function (Blueprint $table) {
                    $table->primary(['supplier_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on supplier: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('supplier', function (Blueprint $table) {
                    $table->index(['supplier_id', 'nama_supplier', 'email_supplier'], 'supplier_supplier_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on supplier: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('surat_kontrol')) {
            try {
                Schema::table('surat_kontrol', function (Blueprint $table) {
                    $table->primary(['surat_kontrol_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on surat_kontrol: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('surat_kontrol', function (Blueprint $table) {
                    $table->index(['surat_kontrol_id', 'emr_id', 'registrasi_id_layanan', 'registrasi_id_kontrol', 'sep_kontrol'], 'surat_kontrol_surat_kontrol_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on surat_kontrol: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('surat_pemusnahan_barang')) {
            try {
                Schema::table('surat_pemusnahan_barang', function (Blueprint $table) {
                    $table->primary(['surat_pemusnahan_barang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on surat_pemusnahan_barang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('surat_pemusnahan_barang', function (Blueprint $table) {
                    $table->index(['surat_pemusnahan_barang_id', 'no_urut_surat', 'no_surat_pemusnahan_barang'], 'surat_pemusnahan_barang_surat_pemusnahan_barang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on surat_pemusnahan_barang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tanda_vital_system')) {
            try {
                Schema::table('tanda_vital_system', function (Blueprint $table) {
                    $table->primary(['tanda_vital_system_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tanda_vital_system: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tanggal_merah')) {
            try {
                Schema::table('tanggal_merah', function (Blueprint $table) {
                    $table->primary(['tanggal_merah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tanggal_merah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tanggal_merah', function (Blueprint $table) {
                    $table->index(['tanggal_merah_id', 'tgl_libur'], 'tanggal_merah_tanggal_merah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tanggal_merah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tarif_default')) {
            try {
                Schema::table('tarif_default', function (Blueprint $table) {
                    $table->primary(['tarif_default_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tarif_default: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tarif_default', function (Blueprint $table) {
                    $table->index(['tarif_default_id', 'bagian_id', 'jenis_tindakan_id', 'tindakan_id'], 'tarif_default_tarif_default_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tarif_default: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tarif')) {
            try {
                Schema::table('tarif', function (Blueprint $table) {
                    $table->primary(['tarif_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tarif: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tarif', function (Blueprint $table) {
                    $table->index(['tarif_id', 'status_batal', 'tindakan_detail_id'], 'idx_tarif');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tarif: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tarif', function (Blueprint $table) {
                    $table->index(['tarif_id', 'tindakan_detail_id', 'kelas_ruang_id'], 'tarif_tarif_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tarif: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('task_bpjs_log')) {
            try {
                Schema::table('task_bpjs_log', function (Blueprint $table) {
                    $table->primary(['task_bpjs_log_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on task_bpjs_log: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('task_bpjs_log', function (Blueprint $table) {
                    $table->index(['task_bpjs_log_id', 'task_id', 'registrasi_id'], 'task_bpjs_log_task_bpjs_log_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on task_bpjs_log: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('telaah_resep')) {
            try {
                Schema::table('telaah_resep', function (Blueprint $table) {
                    $table->primary(['telaah_resep_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on telaah_resep: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('telaah_resep', function (Blueprint $table) {
                    $table->index(['telaah_resep_id', 'peresepan_obat_id'], 'telaah_resep_telaah_resep_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on telaah_resep: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('template_penunjang')) {
            try {
                Schema::table('template_penunjang', function (Blueprint $table) {
                    $table->primary(['template_penunjang_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on template_penunjang: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('template_penunjang', function (Blueprint $table) {
                    $table->index(['template_penunjang_id', 'tindakan_id', 'req_template_user_id'], 'template_penunjang_template_penunjang_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on template_penunjang: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('terapi_obat_dosis')) {
            try {
                Schema::table('terapi_obat_dosis', function (Blueprint $table) {
                    $table->primary(['terapi_obat_dosis_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on terapi_obat_dosis: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('terapi_obat_dosis', function (Blueprint $table) {
                    $table->index(['terapi_obat_dosis_id', 'registrasi_detail_id', 'barang_id', 'user_id_pengedit', 'user_id_dokter_instruksi'], 'terapi_obat_dosis_terapi_obat_dosis_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on terapi_obat_dosis: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tim_bedah_detail')) {
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->primary(['tim_bedah_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->index(['tim_bedah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->index(['tanggal_tugas']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->index(['user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->index(['tim_bedah_detail_id', 'tim_bedah_id', 'user_id', 'tanggal_tugas'], 'idx_tim_bedah_detail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah_detail', function (Blueprint $table) {
                    $table->index(['tim_bedah_detail_id', 'tim_bedah_id', 'tanggal_tugas', 'user_id'], 'tim_bedah_detail_tim_bedah_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tim_bedah')) {
            try {
                Schema::table('tim_bedah', function (Blueprint $table) {
                    $table->primary(['tim_bedah_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah', function (Blueprint $table) {
                    $table->index(['shift']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tim_bedah', function (Blueprint $table) {
                    $table->index(['tim_bedah_id', 'shift', 'nama_tim_bedah', 'jenis_tindakan'], 'tim_bedah_tim_bedah_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tim_bedah: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tindakan_detail')) {
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->primary(['tindakan_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['bagian_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['profesi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['tindakan_detail_id', 'tindakan_id', 'bagian_id', 'profesi_id'], 'idx_tindakan_detail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_detail', function (Blueprint $table) {
                    $table->index(['tindakan_detail_id', 'tindakan_id', 'bagian_id', 'profesi_id'], 'tindakan_detail_tindakan_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tindakan_group_detail_sub')) {
            try {
                Schema::table('tindakan_group_detail_sub', function (Blueprint $table) {
                    $table->primary(['tindakan_group_detail_sub_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail_sub: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_group_detail_sub', function (Blueprint $table) {
                    $table->index(['tindakan_group_detail_sub_id', 'tindakan_group_detail_id'], 'idx_tindakan_group_detail_sub01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail_sub: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_group_detail_sub', function (Blueprint $table) {
                    $table->index(['tindakan_group_detail_sub_id', 'tindakan_group_detail_id'], 'tindakan_group_detail_sub_tindakan_group_detail_sub_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail_sub: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tindakan_group_detail')) {
            try {
                Schema::table('tindakan_group_detail', function (Blueprint $table) {
                    $table->primary(['tindakan_group_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_group_detail', function (Blueprint $table) {
                    $table->index(['tindakan_group_detail_id', 'tindakan_group_id', 'tindakan_id'], 'idx_tindakan_group_detail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_group_detail', function (Blueprint $table) {
                    $table->index(['tindakan_group_detail_id', 'tindakan_group_id', 'tindakan_id'], 'tindakan_group_detail_tindakan_group_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tindakan_group')) {
            try {
                Schema::table('tindakan_group', function (Blueprint $table) {
                    $table->primary(['tindakan_group_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan_group', function (Blueprint $table) {
                    $table->index(['tindakan_group_id', 'referensi_tindakan_group', 'bagian_id'], 'tindakan_group_tindakan_group_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan_group: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tindakan')) {
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->primary(['tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->index(['nama_tindakan']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->index(['jenis_tindakan_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->index(['tindakan_id', 'status_batal'], 'idx_tindakan01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tindakan', function (Blueprint $table) {
                    $table->index(['tindakan_id', 'nama_tindakan', 'jenis_tindakan_id', 'tindakan_id_old'], 'tindakan_tindakan_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tindakan: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('tipe_bayar')) {
            try {
                Schema::table('tipe_bayar', function (Blueprint $table) {
                    $table->primary(['tipe_bayar_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tipe_bayar: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('tipe_bayar', function (Blueprint $table) {
                    $table->index(['tipe_bayar_id', 'nama_tipe_bayar', 'status_batal'], 'tipe_bayar_tipe_bayar_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on tipe_bayar: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('uang_muka')) {
            try {
                Schema::table('uang_muka', function (Blueprint $table) {
                    $table->primary(['uang_muka_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on uang_muka: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('uang_muka', function (Blueprint $table) {
                    $table->index(['uang_muka_id', 'registrasi_id', 'pasien_id', 'tipe_bayar_id'], 'uang_muka_uang_muka_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on uang_muka: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('upload_fpk_pending')) {
            try {
                Schema::table('upload_fpk_pending', function (Blueprint $table) {
                    $table->primary(['id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on upload_fpk_pending: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('upload_fpk')) {
            try {
                Schema::table('upload_fpk', function (Blueprint $table) {
                    $table->primary(['upload_fpk_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on upload_fpk: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('upload_fpk', function (Blueprint $table) {
                    $table->index(['upload_fpk_id', 'jenis_rawat', 'no_verifikasi'], 'upload_fpk_upload_fpk_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on upload_fpk: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('urut_visum')) {
            try {
                Schema::table('urut_visum', function (Blueprint $table) {
                    $table->primary(['urut_visum_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urut_visum: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urut_visum', function (Blueprint $table) {
                    $table->index(['urut_visum_id', 'emr_id', 'kode_visum'], 'urut_visum_urut_visum_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urut_visum: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('urutan_antrian_farmasi')) {
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->primary(['urutan_antrian_farmasi_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->index(['status_batal']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->index(['pasien_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->index(['tgl_antrian']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->index(['jenis_antrian']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('urutan_antrian_farmasi', function (Blueprint $table) {
                    $table->index(['urutan_antrian_farmasi_id'], 'urutan_antrian_farmasi_urutan_antrian_farmasi_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_farmasi: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('urutan_antrian_poli')) {
            try {
                Schema::table('urutan_antrian_poli', function (Blueprint $table) {
                    $table->primary(['urutan_antrian_poli_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on urutan_antrian_poli: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('user_akses')) {
            try {
                Schema::table('user_akses', function (Blueprint $table) {
                    $table->primary(['user_akses_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_akses: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('user_akses', function (Blueprint $table) {
                    $table->index(['user_akses_id', 'user_id', 'sub_menu_id'], 'idx_user_akses01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_akses: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('user_akses', function (Blueprint $table) {
                    $table->index(['user_akses_id', 'user_id', 'sub_menu_id'], 'user_akses_user_akses_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_akses: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('user_apm')) {
            try {
                Schema::table('user_apm', function (Blueprint $table) {
                    $table->primary(['user_apm_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_apm: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('user_group_detail')) {
            try {
                Schema::table('user_group_detail', function (Blueprint $table) {
                    $table->primary(['user_group_detail_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_group_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('user_group_detail', function (Blueprint $table) {
                    $table->index(['user_group_detail_id', 'user_group_id', 'sub_menu_id'], 'idx_user_group_dfetail01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_group_detail: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('user_group_detail', function (Blueprint $table) {
                    $table->index(['user_group_detail_id', 'user_group_id', 'sub_menu_id'], 'user_group_detail_user_group_detail_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_group_detail: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('user_group')) {
            try {
                Schema::table('user_group', function (Blueprint $table) {
                    $table->primary(['user_group_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_group: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('user_group', function (Blueprint $table) {
                    $table->index(['user_group_id', 'nama_user_group', 'status_batal'], 'user_group_user_group_id_idx');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on user_group: " . $e->getMessage() . "\n";
            }
        }

        if (Schema::hasTable('users')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->primary(['user_id']);
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on users: " . $e->getMessage() . "\n";
            }
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->index(['user_id', 'pegawai_id', 'status_batal'], 'idx_users01');
                });
            } catch (\Exception $e) {
                echo "Failed to apply index on users: " . $e->getMessage() . "\n";
            }
        }

    }

    public function down(): void
    {
    }
};
