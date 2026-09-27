<?php

namespace App\Http\Controllers\Administrator\ManajemenAdmin\Notifikasi;

use App\Helpers\NotifikasiHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
    /**
     * Riwayat notifikasi yang pernah dikirim.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Notifikasi::aktif()
            ->with('pengirim')
            ->withCount(['penerima' => fn ($q) => $q->aktif()]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('pesan', 'like', "%{$search}%");
            });
        }

        $notifikasis = $query->orderByDesc('input_time')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenAdmin.Notifikasi.notifikasi', compact('notifikasis', 'search'));
    }

    /**
     * Form kirim notifikasi.
     */
    public function create()
    {
        return view('moduls.Administrator.ManajemenAdmin.Notifikasi.notifikasi_create', [
            'users' => $this->daftarUser(),
            'bagians' => $this->daftarBagian(),
        ]);
    }

    /**
     * Simpan notifikasi + baris penerima (fan-out sesuai tipe target).
     */
    public function store(Request $request)
    {
        $data = array_merge([
            'user_ids' => [],
            'bagian_id' => null,
            'prioritas' => 'INFO',
        ], $request->validate([
            'judul' => 'required|string|max:150',
            'pesan' => 'required|string',
            'tipe_target' => 'required|in:'.Notifikasi::TIPE_SEMUA.','.Notifikasi::TIPE_USER.','.Notifikasi::TIPE_BAGIAN,
            'user_ids' => 'required_if:tipe_target,'.Notifikasi::TIPE_USER.'|array|min:1',
            'user_ids.*' => 'integer|exists:users,user_id',
            'bagian_id' => 'required_if:tipe_target,'.Notifikasi::TIPE_BAGIAN.'|nullable|integer|exists:bagian,bagian_id',
            'prioritas' => 'nullable|in:'.implode(',', Notifikasi::PRIORITAS),
        ]));

        $tipe = $data['tipe_target'];

        // target_id menyimpan identitas tunggal dari target: user_id (tipe USER) atau
        // bagian_id (tipe BAGIAN). Untuk SEMUA tidak ada target.
        $targetId = match ($tipe) {
            Notifikasi::TIPE_BAGIAN => $data['bagian_id'],
            Notifikasi::TIPE_USER => (int) ($data['user_ids'][array_key_first($data['user_ids'])] ?? 0) ?: null,
            default => null,
        };

        DB::beginTransaction();
        try {
            $jumlah = NotifikasiHelper::kirim([
                'judul' => $data['judul'],
                'pesan' => $data['pesan'],
                'tipe_target' => $tipe,
                'target_id' => $targetId,
                'prioritas' => $data['prioritas'] ?? 'INFO',
                // Tipe USER boleh menyasar banyak user sekaligus lewat multi-select.
                'penerima_ids' => $tipe === Notifikasi::TIPE_USER ? $data['user_ids'] : null,
            ], Auth::id());

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengirim notifikasi: '.$e->getMessage())->withInput();
        }

        if ($jumlah === 0) {
            return redirect()->route('admin.notifikasi.index')
                ->with('error', 'Notifikasi tersimpan, tetapi tidak ada user aktif yang cocok dengan target tersebut.');
        }

        return redirect()->route('admin.notifikasi.index')
            ->with('success', 'Notifikasi berhasil dikirim ke '.$jumlah.' user.');
    }

    /**
     * Hapus notifikasi (soft delete header + penerima).
     */
    public function destroy($notifikasi)
    {
        DB::beginTransaction();
        try {
            $notifikasi = Notifikasi::aktif()->findOrFail($notifikasi);

            $notifikasi->status_batal = 1;
            $notifikasi->mod_time = now();
            $notifikasi->mod_user_id = Auth::id();
            $notifikasi->save();

            DB::table('notifikasi_penerima')
                ->where('notifikasi_id', $notifikasi->notifikasi_id)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => Auth::id(),
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus notifikasi: '.$e->getMessage());
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    private function daftarUser()
    {
        return User::where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        })
            ->orderBy('nama_pegawai')
            ->get(['user_id', 'nama_pegawai', 'user_name']);
    }

    private function daftarBagian()
    {
        return Bagian::aktif()
            ->orderBy('nama_bagian')
            ->get(['bagian_id', 'nama_bagian', 'referensi_bagian_id']);
    }
}
