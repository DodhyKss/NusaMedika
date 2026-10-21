<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Update data pegawai milik user yang sedang login.
     * users.nama_pegawai ikut disinkronkan (kolom itu salinan dari pegawai).
     */
    public function updatePegawai(Request $request)
    {
        $user = $request->user();
        $pegawai = $user->pegawai;

        if (! $pegawai) {
            return back()->with('error', 'Akun ini belum terhubung dengan data pegawai.');
        }

        // validate() hanya mengembalikan key yang terkirim; key opsional yang tidak
        // dikirim harus digabung manual agar tidak "Undefined array key".
        //
        // CATATAN: bagian_id, profesi_id, jabatan_id, dan status_kepegawaian_id
        // SENGAJA TIDAK divalidasi/diubah di sini — field tersebut terkunci (disabled)
        // di modal dan hanya boleh diubah Administrator lewat menu Master Pegawai.
        // Mesmo bila ada request yang memalsukan field ini, nilainya diabaikan.
        $validated = array_merge([
            'nip' => null,
            'nik' => null,
            'no_rfid' => null,
            'id_satu_sehat' => null,
            'inacbg_id' => null,
            'sip' => null,
            'tgl_awal_sip' => null,
            'tgl_akhir_sip' => null,
            'str' => null,
            'tgl_awal_str' => null,
            'tgl_akhir_str' => null,
            'ttd' => null,
        ], $request->validate([
            'nama_pegawai' => 'required|string|max:255',
            'nip' => 'nullable|string|max:20',
            'nik' => 'nullable|string|max:20',
            'no_rfid' => 'nullable|integer',
            'id_satu_sehat' => 'nullable|string|max:20',
            'inacbg_id' => 'nullable|string|max:100',
            'sip' => 'nullable|string|max:100',
            'tgl_awal_sip' => 'nullable|date',
            'tgl_akhir_sip' => 'nullable|date',
            'str' => 'nullable|string|max:100',
            'tgl_awal_str' => 'nullable|date',
            'tgl_akhir_str' => 'nullable|date',
            'ttd' => 'nullable|string|max:100',
        ]));

        $tanggal = static function ($value) {
            return $value ? Carbon::parse($value)->startOfDay() : null;
        };

        DB::beginTransaction();
        try {
            $pegawai->nama_pegawai = $validated['nama_pegawai'];
            $pegawai->nip = $validated['nip'];
            $pegawai->nik = $validated['nik'];
            $pegawai->no_rfid = $validated['no_rfid'];
            $pegawai->id_satu_sehat = $validated['id_satu_sehat'];
            $pegawai->inacbg_id = $validated['inacbg_id'];
            $pegawai->sip = $validated['sip'];
            $pegawai->tgl_awal_sip = $tanggal($validated['tgl_awal_sip']);
            $pegawai->tgl_akhir_sip = $tanggal($validated['tgl_akhir_sip']);
            $pegawai->str = $validated['str'];
            $pegawai->tgl_awal_str = $tanggal($validated['tgl_awal_str']);
            $pegawai->tgl_akhir_str = $tanggal($validated['tgl_akhir_str']);
            $pegawai->ttd = $validated['ttd'];
            $pegawai->mod_time = now();
            $pegawai->mod_user_id = $user->user_id;
            $pegawai->save();

            // users.nama_pegawai adalah salinan dari pegawai, harus selalu sinkron.
            $user->nama_pegawai = $pegawai->nama_pegawai;
            $user->mod_time = now();
            $user->mod_user_id = $user->user_id;
            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data pegawai: '.$e->getMessage());
        }

        return back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Update akun login milik user yang sedang login (username & password plain-text).
     */
    public function updateAkun(Request $request)
    {
        $user = $request->user();

        $validated = array_merge([
            'user_password' => null,
            'user_password_konfirmasi' => null,
        ], $request->validate([
            'user_name' => 'required|string|max:50|unique:users,user_name,'.$user->user_id.',user_id',
            'user_password' => 'nullable|string|min:3|max:30',
            'user_password_konfirmasi' => 'nullable|same:user_password',
        ]));

        DB::beginTransaction();
        try {
            $user->user_name = $validated['user_name'];

            // Password hanya diubah bila field diisi (kosong = tidak berubah).
            if (! empty($validated['user_password'])) {
                $user->user_password = $validated['user_password'];
                $user->last_update_pass = now();
            }

            $user->mod_time = now();
            $user->mod_user_id = $user->user_id;
            $user->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan akun: '.$e->getMessage());
        }

        return back()->with('success', 'Akun berhasil diperbarui.');
    }
}
