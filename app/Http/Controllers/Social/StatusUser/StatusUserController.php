<?php

namespace App\Http\Controllers\Social\StatusUser;

use App\Http\Controllers\Controller;
use App\Models\StatusSosial;
use App\Models\User;
use Illuminate\Http\Request;

class StatusUserController extends Controller
{
    /**
     * Daftar status milik user lain (hanya baca — tidak ada store/update/destroy di sini).
     *
     * Status dibuat dari sub_menu "Status"; halaman ini khusus untuk melihat
     * status milik seluruh pengguna beserta sisa masa berlakunya.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $userId = (int) $request->user()->user_id;

        $users = User::where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        })
            ->orderBy('nama_pegawai')
            ->get(['user_id', 'nama_pegawai', 'user_name']);

        if ($search !== '') {
            $users = $users->filter(function ($u) use ($search) {
                return str_contains(mb_strtolower((string) $u->nama_pegawai), mb_strtolower($search))
                    || str_contains(mb_strtolower((string) $u->user_name), mb_strtolower($search));
            })->values();
        }

        // Status terbaru milik tiap user — diambil dengan 1 query, bukan N+1 per baris.
        $userIds = $users->pluck('user_id')->all() ?: [0];

        $terbaru = StatusSosial::belumKedaluwarsa()
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, MAX(status_sosial_id) as status_sosial_id')
            ->groupBy('user_id')
            ->pluck('status_sosial_id', 'user_id');

        $statusTerbaru = StatusSosial::aktif()
            ->whereIn('status_sosial_id', $terbaru->all() ?: [0])
            ->get()
            ->keyBy('user_id');

        // Jumlah status aktif (belum kedaluwarsa) per user — untuk badge di daftar.
        $jumlahAktif = StatusSosial::belumKedaluwarsa()
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, COUNT(*) as jumlah')
            ->groupBy('user_id')
            ->pluck('jumlah', 'user_id');

        // Riwayat status milik user lain saja (tanpa status sendiri), untuk panel riwayat.
        $riwayat = StatusSosial::belumKedaluwarsa()
            ->where('user_id', '!=', $userId)
            ->whereIn('user_id', $userIds)
            ->orderByDesc('input_time')
            ->limit(20)
            ->get();

        $pengguna = User::whereIn('user_id', $riwayat->pluck('user_id')->all() ?: [0])
            ->pluck('nama_pegawai', 'user_id');

        return view('moduls.Social.StatusUser.status_user', [
            'users' => $users,
            'statusTerbaru' => $statusTerbaru,
            'jumlahAktif' => $jumlahAktif,
            'riwayat' => $riwayat,
            'namaPengguna' => $pengguna,
            'search' => $search,
            'saya' => $userId,
        ]);
    }
}
