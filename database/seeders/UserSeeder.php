<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = [
            ['user_id' => 1, 'user_name' => 'superadmin', 'user_password' => 'admin', 'pegawai_id' => 1],
            ['user_id' => 2, 'user_name' => 'perawat', 'user_password' => 'perawat', 'pegawai_id' => 2],
            ['user_id' => 3, 'user_name' => 'dokter', 'user_password' => 'dokter', 'pegawai_id' => 3],
            ['user_id' => 4, 'user_name' => 'bidan', 'user_password' => 'bidan', 'pegawai_id' => 4],
            ['user_id' => 5, 'user_name' => 'apoteker', 'user_password' => 'apoteker', 'pegawai_id' => 5],
            ['user_id' => 6, 'user_name' => 'radiografer', 'user_password' => 'radiografer', 'pegawai_id' => 6],
            ['user_id' => 7, 'user_name' => 'perekam_medis', 'user_password' => 'perekam_medis', 'pegawai_id' => 7],
            ['user_id' => 8, 'user_name' => 'fisioterapis', 'user_password' => 'fisioterapis', 'pegawai_id' => 8],
            ['user_id' => 9, 'user_name' => 'sanitarian', 'user_password' => 'sanitarian', 'pegawai_id' => 9],
            ['user_id' => 10, 'user_name' => 'ahli_gizi', 'user_password' => 'ahli_gizi', 'pegawai_id' => 10],
            ['user_id' => 11, 'user_name' => 'security', 'user_password' => 'security', 'pegawai_id' => 11],
            ['user_id' => 12, 'user_name' => 'teknisi', 'user_password' => 'teknisi', 'pegawai_id' => 12],
        ];

        foreach ($users as $user) {
            $pegawai = DB::table('pegawai')->where('pegawai_id', $user['pegawai_id'])->first();

            DB::table('users')->updateOrInsert(
                ['user_id' => $user['user_id']],
                array_merge($user, [
                    'nama_pegawai' => $pegawai ? $pegawai->nama_pegawai : null,
                    'input_time' => $now,
                    'input_user_id' => 1,
                    'last_update_pass' => $now,
                    'status_batal' => 0,
                ])
            );
        }

        // Hanya berikan akses ke sub_menu yang benar-benar ada (hindari referensi id tak ada, mis. 8-11)
        $validSubMenuIds = DB::table('sub_menu')->pluck('sub_menu_id')->all();

        $validAktifSubMenuIds = DB::table('sub_menu')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('sub_menu_id')
            ->all();

        $akses = [
            1 => $validAktifSubMenuIds,
            2 => $validAktifSubMenuIds,
            3 => $validAktifSubMenuIds,
            4 => $validAktifSubMenuIds,
            5 => $validAktifSubMenuIds,
            6 => $validAktifSubMenuIds,
            7 => $validAktifSubMenuIds,
            8 => $validAktifSubMenuIds,
            9 => $validAktifSubMenuIds,
            10 => $validAktifSubMenuIds,
            11 => $validAktifSubMenuIds,
            12 => $validAktifSubMenuIds,
        ];

        foreach ($akses as $userId => $subMenuIds) {
            foreach ($subMenuIds as $subMenuId) {
                if (! in_array($subMenuId, $validSubMenuIds)) {
                    continue;
                }

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
                        'user_id' => $userId,
                        'sub_menu_id' => $subMenuId,
                        'input_time' => $now,
                        'input_user_id' => 1,
                        'status_batal' => 0,
                    ]);
                }
            }
        }

        // Soft-delete user_akses yang merujuk sub_menu yang sudah tidak ada (mis. 8-11)
        $missingIds = array_values(array_diff(
            collect($akses)->flatten()->unique()->all(),
            $validSubMenuIds
        ));

        if ($missingIds) {
            DB::table('user_akses')
                ->whereIn('sub_menu_id', $missingIds)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => 1,
                ]);
        }
    }
}
