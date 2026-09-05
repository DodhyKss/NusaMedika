<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ========= Seeder Jabatan =========
        $jabatans = [
            ['jabatan_id' => 1, 'nama_jabatan' => 'Direktur'],
            ['jabatan_id' => 2, 'nama_jabatan' => 'Wakil Direktur Pelayanan'],
            ['jabatan_id' => 3, 'nama_jabatan' => 'Wakil Direktur Keperawatan'],
            ['jabatan_id' => 4, 'nama_jabatan' => 'Wakil Direktur Penunjang'],
            ['jabatan_id' => 5, 'nama_jabatan' => 'Wakil Direktur Umum & Keuangan'],
            ['jabatan_id' => 6, 'nama_jabatan' => 'Komite Medik'],
            ['jabatan_id' => 7, 'nama_jabatan' => 'Komite Keperawatan'],
            ['jabatan_id' => 8, 'nama_jabatan' => 'Komite Mutu'],
            ['jabatan_id' => 9, 'nama_jabatan' => 'Kepala Bagian'],
            ['jabatan_id' => 10, 'nama_jabatan' => 'Kepala Bidan'],
            ['jabatan_id' => 11, 'nama_jabatan' => 'Kepala Seksi'],
            ['jabatan_id' => 12, 'nama_jabatan' => 'Kepala Instalasi'],
            ['jabatan_id' => 13, 'nama_jabatan' => 'Pegawai'],
        ];

        foreach ($jabatans as $jabatan) {
            DB::table('jabatan')->updateOrInsert(
                ['jabatan_id' => $jabatan['jabatan_id']],
                array_merge($jabatan, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ========== Seeder Profesi =============
        $profesis = [
            ['profesi_id' => 1, 'nama_profesi' => 'Dokter'],
            ['profesi_id' => 2, 'nama_profesi' => 'Perawat'],
            ['profesi_id' => 3, 'nama_profesi' => 'Bidan'],
            ['profesi_id' => 4, 'nama_profesi' => 'Apoteker'],
            ['profesi_id' => 5, 'nama_profesi' => 'Radiografer'],
            ['profesi_id' => 6, 'nama_profesi' => 'IT Support'],
            ['profesi_id' => 7, 'nama_profesi' => 'Perekam Medis'],
            ['profesi_id' => 8, 'nama_profesi' => 'Fisioterapis'],
            ['profesi_id' => 9, 'nama_profesi' => 'Sanitarian'],
            ['profesi_id' => 10, 'nama_profesi' => 'Ahli Gizi'],
            ['profesi_id' => 11, 'nama_profesi' => 'Security'],
            ['profesi_id' => 12, 'nama_profesi' => 'Teknisi'],
        ];

        foreach ($profesis as $profesi) {
            DB::table('profesi')->updateOrInsert(
                ['profesi_id' => $profesi['profesi_id']],
                array_merge($profesi, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ========== Seeder Status Kepegawaian =============
        $statuses = [
            ['status_kepegawaian_id' => 1, 'nama_status_kepegawaian' => 'PNS'],
            ['status_kepegawaian_id' => 2, 'nama_status_kepegawaian' => 'PPPK'],
            ['status_kepegawaian_id' => 3, 'nama_status_kepegawaian' => 'PPPK PW'],
            ['status_kepegawaian_id' => 4, 'nama_status_kepegawaian' => 'Kontrak'],
            ['status_kepegawaian_id' => 5, 'nama_status_kepegawaian' => 'Honorer'],
        ];

        foreach ($statuses as $status) {
            DB::table('status_kepegawaian')->updateOrInsert(
                ['status_kepegawaian_id' => $status['status_kepegawaian_id']],
                array_merge($status, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }

        // ======== Seeder Pegawai ==========
        $pegawais = [
            [
                'pegawai_id' => 1,
                'nama_pegawai' => 'Superadmin',
                'nip' => 'ADM001',
                'bagian_id' => 1,
                'profesi_id' => 6,
                'jabatan_id' => 8,
                'status_kepegawaian_id' => 3,
            ],
            [
                'pegawai_id' => 2,
                'nama_pegawai' => 'Perawat Jaga',
                'nip' => 'PRW001',
                'bagian_id' => 1,
                'profesi_id' => 2,
                'jabatan_id' => 6,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 3,
                'nama_pegawai' => 'Dokter Jaga',
                'nip' => 'DKT001',
                'bagian_id' => 1,
                'profesi_id' => 1,
                'jabatan_id' => 5,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 4,
                'nama_pegawai' => 'Bidan Jaga',
                'nip' => 'BDN001',
                'bagian_id' => 1,
                'profesi_id' => 3,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 5,
                'nama_pegawai' => 'Apoteker',
                'nip' => 'APT001',
                'bagian_id' => 1,
                'profesi_id' => 4,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 6,
                'nama_pegawai' => 'Radiografer',
                'nip' => 'RDG001',
                'bagian_id' => 1,
                'profesi_id' => 5,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 7,
                'nama_pegawai' => 'Perekam Medis',
                'nip' => 'PRM001',
                'bagian_id' => 1,
                'profesi_id' => 7,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 8,
                'nama_pegawai' => 'Fisioterapis',
                'nip' => 'FSI001',
                'bagian_id' => 1,
                'profesi_id' => 8,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 9,
                'nama_pegawai' => 'Sanitarian',
                'nip' => 'SNT001',
                'bagian_id' => 1,
                'profesi_id' => 9,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 10,
                'nama_pegawai' => 'Ahli Gizi',
                'nip' => 'GZI001',
                'bagian_id' => 1,
                'profesi_id' => 10,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 11,
                'nama_pegawai' => 'Security',
                'nip' => 'SEC001',
                'bagian_id' => 1,
                'profesi_id' => 11,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
            [
                'pegawai_id' => 12,
                'nama_pegawai' => 'Teknisi',
                'nip' => 'TKN001',
                'bagian_id' => 1,
                'profesi_id' => 12,
                'jabatan_id' => 13,
                'status_kepegawaian_id' => 1,
            ],
        ];

        foreach ($pegawais as $pegawai) {
            DB::table('pegawai')->updateOrInsert(
                ['pegawai_id' => $pegawai['pegawai_id']],
                array_merge($pegawai, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'status_batal' => 0,
                ])
            );
        }
    }
}
