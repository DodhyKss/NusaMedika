<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterKategoriTindakan;

use App\Http\Controllers\Controller;
use App\Models\KategoriTindakan;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterKategoriTindakanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = KategoriTindakan::aktif()->withCount('tindakan');

        if ($search !== '') {
            $query->where('nama_kategori_tindakan', 'like', "%{$search}%");
        }

        $kategoriList = $query->orderBy('nama_kategori_tindakan')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.MasterKategoriTindakan.master_kategori_tindakan', compact(
            'kategoriList',
            'search'
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterKategoriTindakan.master_kategori_tindakan_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $kategori = new KategoriTindakan;
            $kategori->nama_kategori_tindakan = $data['nama_kategori_tindakan'];
            $kategori->input_time = now();
            $kategori->input_user_id = Auth::id();
            $kategori->status_batal = 0;
            $kategori->save();

            DB::commit();

            return redirect()->route('admin.master_kategori_tindakan.index')
                ->with('success', 'Kategori Tindakan berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan kategori tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterKategoriTindakan)
    {
        $kategori = KategoriTindakan::findOrFail($masterKategoriTindakan);

        return view('moduls.Administrator.ManajemenMaster.MasterKategoriTindakan.master_kategori_tindakan_edit', compact(
            'kategori'
        ));
    }

    public function update(Request $request, $masterKategoriTindakan)
    {
        $data = $this->validated($request, $masterKategoriTindakan);

        DB::beginTransaction();
        try {
            $kategori = KategoriTindakan::findOrFail($masterKategoriTindakan);
            $kategori->nama_kategori_tindakan = $data['nama_kategori_tindakan'];
            $kategori->mod_time = now();
            $kategori->mod_user_id = Auth::id();
            $kategori->save();

            DB::commit();

            return redirect()->route('admin.master_kategori_tindakan.index')
                ->with('success', 'Kategori Tindakan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui kategori tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterKategoriTindakan)
    {
        DB::beginTransaction();
        try {
            $kategori = KategoriTindakan::findOrFail($masterKategoriTindakan);
            $user = Auth::id();
            $now = now();

            $kategori->status_batal = 1;
            $kategori->mod_time = $now;
            $kategori->mod_user_id = $user;
            $kategori->save();

            // Lepaskan kategori dari tindakan agar tidak "yatim" (aksi berantai).
            Tindakan::where('kategori_tindakan_id', $kategori->kategori_tindakan_id)
                ->update([
                    'kategori_tindakan_id' => null,
                    'mod_time' => $now,
                    'mod_user_id' => $user,
                ]);

            DB::commit();

            return redirect()->route('admin.master_kategori_tindakan.index')
                ->with('success', 'Kategori Tindakan berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus kategori tindakan: '.$e->getMessage());
        }
    }

    private function validated(Request $request, $masterKategoriTindakan = null)
    {
        $ignore = $masterKategoriTindakan ? (int) $masterKategoriTindakan : null;

        return $request->validate([
            'nama_kategori_tindakan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_tindakan', 'nama_kategori_tindakan')->ignore($ignore, 'kategori_tindakan_id'),
            ],
        ]);
    }
}
