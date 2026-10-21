<?php

namespace App\Http\Controllers\Social\BuatTopik;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\ForumTopik;
use App\Models\Profesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Halaman pembuatan topik forum.
 *
 * Sengaja dipisah dari ForumController agar hak membuat topik diatur lewat
 * user_akses sub_menu "Buat Topik" (middleware submenu.access) — bukan lewat
 * penamaan user/grup di dalam kode. Reader forum (sub_menu "Forum") tidak
 * otomatis punya hak membuat topik.
 */
class BuatTopikController extends Controller
{
    /**
     * Form membuat topik baru. Method ini juga menjadi landing page sub_menu,
     * karena sidebar memakai route {nama}.index.
     */
    public function index()
    {
        return view('moduls.Social.BuatTopik.buat_topik', [
            'profesi' => Profesi::where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })->orderBy('nama_profesi')->get(['profesi_id', 'nama_profesi']),
            'bagian' => Bagian::aktif()->orderBy('nama_bagian')->get(['bagian_id', 'nama_bagian']),
        ]);
    }

    /**
     * Simpan topik baru.
     */
    public function store(Request $request)
    {
        $data = array_merge([
            'target_id' => null,
        ], $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'tipe' => 'required|in:'.ForumTopik::TIPE_PUBLIK.','.ForumTopik::TIPE_PROFESI.','.ForumTopik::TIPE_BAGIAN,
            'target_id' => 'nullable|integer|min:1',
        ]));

        $tipe = $data['tipe'];

        // target_id wajib ada untuk topik PROFESI / BAGIAN, dan harus null untuk PUBLIK.
        if ($tipe !== ForumTopik::TIPE_PUBLIK && empty($data['target_id'])) {
            return back()->with('error', 'Pilih target untuk topik '.strtolower($tipe).'.')->withInput();
        }

        $targetId = $tipe === ForumTopik::TIPE_PUBLIK ? null : (int) $data['target_id'];

        // Validasi target benar-benar ada di tabel referensinya.
        if ($tipe === ForumTopik::TIPE_PROFESI && ! Profesi::where('profesi_id', $targetId)->exists()) {
            return back()->with('error', 'Profesi yang dipilih tidak ditemukan.')->withInput();
        }

        if ($tipe === ForumTopik::TIPE_BAGIAN && ! Bagian::aktif()->where('bagian_id', $targetId)->exists()) {
            return back()->with('error', 'Bagian yang dipilih tidak ditemukan.')->withInput();
        }

        DB::beginTransaction();
        try {
            $topik = new ForumTopik;
            $topik->judul = $data['judul'];
            $topik->isi = $data['isi'];
            $topik->tipe = $tipe;
            $topik->target_id = $targetId;
            $topik->user_id = $request->user()->user_id;
            $topik->terakhir_aktif_at = now();
            $topik->input_time = now();
            $topik->input_user_id = $request->user()->user_id;
            $topik->status_batal = 0;
            $topik->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat topik: '.$e->getMessage())->withInput();
        }

        return redirect()->route('forum.show', $topik->topik_id)->with('success', 'Topik berhasil dibuat.');
    }
}
