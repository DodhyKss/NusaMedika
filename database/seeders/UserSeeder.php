<?php

namespace Database\Seeders;

use App\Helpers\SequenceHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = [
            [
                'user_id' => 1,
                'user_name' => 'admin',
                'user_password' => 'admin',
                'nama_pegawai' => 'Administrator Sistem',
                'pegawai_id' => null,
            ],
            [
                'user_id' => 2,
                'user_name' => 'perawat',
                'user_password' => 'perawat',
                'nama_pegawai' => 'Perawat Jaga',
                'pegawai_id' => null,
            ],
            [
                'user_id' => 3,
                'user_name' => 'dokter',
                'user_password' => 'dokter',
                'nama_pegawai' => 'Dokter Jaga',
                'pegawai_id' => null,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['user_id' => $user['user_id']],
                array_merge($user, [
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'last_update_pass' => $now,
                    'status_batal' => 0,
                ])
            );
        }

        $akses = [
            // Admin: semua sub menu
            1 => range(1, 22),
            // Perawat: registrasi, dashboard pasien, pengkajian, list pasien
            2 => [1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14],
            // Dokter: daftar pasien, dashboard pasien, SOAP, pengkajian, list pasien
            3 => [1, 8, 9, 10, 11, 12, 13, 14],
        ];

        foreach ($akses as $userId => $subMenuIds) {
            foreach ($subMenuIds as $subMenuId) {
                $existing = DB::table('user_akses')
                    ->where('user_id', $userId)
                    ->where('sub_menu_id', $subMenuId)
                    ->first();

                if ($existing) {
                    DB::table('user_akses')
                        ->where('user_akses_id', $existing->user_akses_id)
                        ->update([
                            'input_time' => $now,
                            'input_user_id' => 1,
                            'status_batal' => 0,
                        ]);
                } else {
                    DB::table('user_akses')->insert([
                        'user_akses_id' => SequenceHelper::getNextId('user_akses'),
                        'user_id' => $userId,
                        'sub_menu_id' => $subMenuId,
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]);
                }
            }
        }
    }
}
