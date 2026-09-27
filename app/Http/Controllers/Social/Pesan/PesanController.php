<?php

namespace App\Http\Controllers\Social\Pesan;

use App\Http\Controllers\Controller;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PesanController extends Controller
{
    /**
     * Daftar percakapan milik user + form memulai percakapan baru.
     */
    public function index(Request $request)
    {
        $userId = (int) $request->user()->user_id;

        $percakapans = Percakapan::milikUser($userId)
            ->orderByDesc('pesan_terakhir_at')
            ->orderByDesc('percakapan_id')
            ->paginate(20);

        // Jumlah pesan belum dibaca per percakapan (satu query, bukan N+1 di blade).
        $belumDibaca = Pesan::belumDibaca()
            ->where('pengirim_id', '!=', $userId)
            ->whereIn('percakapan_id', $percakapans->pluck('percakapan_id')->all() ?: [0])
            ->selectRaw('percakapan_id, COUNT(*) as jumlah')
            ->groupBy('percakapan_id')
            ->pluck('jumlah', 'percakapan_id');

        $teman = $this->daftarTeman($userId);

        return view('moduls.Social.Pesan.pesan', compact('percakapans', 'belumDibaca', 'teman'));
    }

    /**
     * Mulai percakapan baru dengan seorang user. Bila percakapan sudah ada,
     * yang dibuka adalah yang lama — bukan membuat dobel.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,user_id',
        ]);

        $userId = (int) $request->user()->user_id;
        $tujuan = (int) $data['user_id'];

        if ($tujuan === $userId) {
            return back()->with('error', 'Tidak dapat memulai percakapan dengan diri sendiri.');
        }

        $percakapan = Percakapan::cariAntara($userId, $tujuan);

        if (! $percakapan) {
            DB::beginTransaction();
            try {
                [$a, $b] = $userId < $tujuan ? [$userId, $tujuan] : [$tujuan, $userId];

                $percakapan = new Percakapan;
                $percakapan->user_a_id = $a;
                $percakapan->user_b_id = $b;
                $percakapan->input_time = now();
                $percakapan->input_user_id = $userId;
                $percakapan->status_batal = 0;
                $percakapan->save();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return back()->with('error', 'Gagal membuat percakapan: '.$e->getMessage());
            }
        }

        return redirect()->route('pesan.show', $percakapan->percakapan_id);
    }

    /**
     * Buka percakapan + tandai pesan masuk sebagai sudah dibaca.
     */
    public function show(Request $request, $percakapan)
    {
        $userId = (int) $request->user()->user_id;

        // WAJIB lewat scopeMilikUser: mencegah user membuka percakapan milik orang lain.
        $percakapan = Percakapan::milikUser($userId)->findOrFail($percakapan);

        Pesan::belumDibaca()
            ->where('percakapan_id', $percakapan->percakapan_id)
            ->where('pengirim_id', '!=', $userId)
            ->update([
                'dibaca_at' => now(),
                'mod_time' => now(),
                'mod_user_id' => $userId,
            ]);

        $pesan = Pesan::aktif()
            ->where('percakapan_id', $percakapan->percakapan_id)
            ->orderBy('input_time')
            ->orderBy('pesan_id')
            ->get();

        return view('moduls.Social.Pesan.pesan_show', [
            'percakapan' => $percakapan,
            'pesan' => $pesan,
            'lawan' => $percakapan->lawan($userId),
        ]);
    }

    /**
     * Kirim pesan ke dalam percakapan milik user tersebut.
     */
    public function kirim(Request $request, $percakapan)
    {
        $userId = (int) $request->user()->user_id;

        $percakapan = Percakapan::milikUser($userId)->findOrFail($percakapan);

        $data = $request->validate([
            'isi' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $pesan = new Pesan;
            $pesan->percakapan_id = $percakapan->percakapan_id;
            $pesan->pengirim_id = $userId;
            $pesan->isi = $data['isi'];
            $pesan->input_time = now();
            $pesan->input_user_id = $userId;
            $pesan->status_batal = 0;
            $pesan->save();

            // Ringkasan percakapan ikut diperbarui agar urutannya benar di daftar.
            $percakapan->pesan_terakhir = $data['isi'];
            $percakapan->pesan_terakhir_at = now();
            $percakapan->mod_time = now();
            $percakapan->mod_user_id = $userId;
            $percakapan->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengirim pesan: '.$e->getMessage())->withInput();
        }

        return back();
    }

    /**
     * User aktif lain yang belum punya percakapan dengan user ini.
     */
    private function daftarTeman(int $userId): Collection
    {
        $sudah = DB::table('percakapan')
            ->where(function ($q) use ($userId) {
                $q->where('user_a_id', $userId)->orWhere('user_b_id', $userId);
            })
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->get(['user_a_id', 'user_b_id'])
            ->map(fn ($p) => (int) ($p->user_a_id == $userId ? $p->user_b_id : $p->user_a_id))
            ->all();

        return User::where('user_id', '!=', $userId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_pegawai')
            ->get(['user_id', 'nama_pegawai', 'user_name'])
            ->reject(fn ($u) => in_array((int) $u->user_id, $sudah, true))
            ->values();
    }
}
