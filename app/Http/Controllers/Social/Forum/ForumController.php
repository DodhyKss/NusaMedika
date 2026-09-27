<?php

namespace App\Http\Controllers\Social\Forum;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\ForumBalasan;
use App\Models\ForumTopik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
    /**
     * Daftar topik yang boleh dilihat user tersebut (publik + profesi sendiri + bagian sendiri).
     *
     * CATATAN: method create/store sengaja TIDAK ada di sini. Pembuatan topik hidup di
     * sub_menu terpisah (Social/BuatTopik) supaya hak membuat topik diatur lewat
     * user_akses, bukan hardcoded. Jangan dikembalikan ke sini.
     */
    public function index(Request $request)
    {
        $userId = (int) $request->user()->user_id;
        $search = trim((string) $request->input('search'));
        $filterTipe = (string) $request->input('tipe', '');

        $query = ForumTopik::terlihatUntuk($userId)
            ->with('pembuat')
            ->withCount(['balasan' => fn ($q) => $q->aktif()]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        // Filter tipe hanya untuk tipe yang memang terlihat oleh user ini,
        // supaya filter tidak membuka topik yang tidak berhak dilihat.
        if (in_array($filterTipe, [ForumTopik::TIPE_PUBLIK, ForumTopik::TIPE_PROFESI, ForumTopik::TIPE_BAGIAN], true)) {
            $query->where('tipe', $filterTipe);
        }

        $topiks = $query->orderByDesc('terakhir_aktif_at')->orderByDesc('topik_id')->paginate(10)->withQueryString();

        return view('moduls.Social.Forum.forum', compact('topiks', 'search', 'filterTipe'));
    }

    /**
     * Buka topik + seluruh balasannya.
     */
    public function show(Request $request, $topik)
    {
        $userId = (int) $request->user()->user_id;

        // WAJIB lewat scopeTerlihatUntuk: user tidak boleh membuka topik di luar haknya
        // hanya dengan mengubah URL.
        $topik = ForumTopik::terlihatUntuk($userId)->with('pembuat')->findOrFail($topik);

        $balasan = ForumBalasan::aktif()
            ->where('topik_id', $topik->topik_id)
            ->with('penulis')
            ->orderBy('input_time')
            ->orderBy('balasan_id')
            ->get();

        return view('moduls.Social.Forum.forum_show', [
            'topik' => $topik,
            'balasan' => $balasan,
        ]);
    }

    /**
     * Balas sebuah topik yang terlihat oleh user tersebut.
     */
    public function balas(Request $request, $topik)
    {
        $userId = (int) $request->user()->user_id;

        $topik = ForumTopik::terlihatUntuk($userId)->findOrFail($topik);

        $data = $request->validate([
            'isi' => 'required|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $balasan = new ForumBalasan;
            $balasan->topik_id = $topik->topik_id;
            $balasan->user_id = $userId;
            $balasan->isi = $data['isi'];
            $balasan->input_time = now();
            $balasan->input_user_id = $userId;
            $balasan->status_batal = 0;
            $balasan->save();

            // Topik dengan balasan terbaru naik ke atas.
            $topik->terakhir_aktif_at = now();
            $topik->mod_time = now();
            $topik->mod_user_id = $userId;
            $topik->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengirim balasan: '.$e->getMessage())->withInput();
        }

        return back();
    }

    /**
     * Hapus topik milik sendiri (balasannya ikut terhapus).
     */
    public function destroy($topik)
    {
        $userId = (int) auth()->id();

        $topik = ForumTopik::aktif()->findOrFail($topik);

        if ((int) $topik->user_id !== $userId) {
            return back()->with('error', 'Topik ini bukan milik Anda, tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $topik->status_batal = 1;
            $topik->mod_time = now();
            $topik->mod_user_id = $userId;
            $topik->save();

            DB::table('forum_balasan')
                ->where('topik_id', $topik->topik_id)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => $userId,
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus topik: '.$e->getMessage());
        }

        return back()->with('success', 'Topik berhasil dihapus beserta balasannya.');
    }
}
