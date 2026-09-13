<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Barang;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\BarangJenis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Barang::aktif()->with('jenis');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%")
                    ->orWhereHas('jenis', fn ($jenis) => $jenis->where('nama_jenis_barang', 'ilike', "%{$search}%"));
            });
        }

        $barangList = $query->orderBy('barang_id')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Barang.barang', compact('barangList', 'search'));
    }

    public function create()
    {
        $barangJenisList = $this->jenisOptions();

        return view('moduls.Administrator.ManajemenMaster.Barang.barang_create', compact('barangJenisList'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $barang = new Barang;
            $barang->nama_barang = $data['nama_barang'];
            $barang->jenis_barang_id = $data['jenis_barang_id'];
            $barang->input_time = now();
            $barang->input_user_id = Auth::id();
            $barang->status_batal = 0;
            $barang->save();

            DB::commit();

            return redirect()->route('admin.barang.index')->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan barang: '.$e->getMessage())->withInput();
        }
    }

    public function edit($barang)
    {
        $barang = Barang::findOrFail($barang);

        $barangJenisList = $this->jenisOptions();

        return view('moduls.Administrator.ManajemenMaster.Barang.barang_edit', compact('barang', 'barangJenisList'));
    }

    public function update(Request $request, $barang)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($barang);
            $barang->nama_barang = $data['nama_barang'];
            $barang->jenis_barang_id = $data['jenis_barang_id'];
            $barang->mod_time = now();
            $barang->mod_user_id = Auth::id();
            $barang->save();

            DB::commit();

            return redirect()->route('admin.barang.index')->with('success', 'Barang berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui barang: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($barang)
    {
        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($barang);
            $barang->status_batal = 1;
            $barang->mod_time = now();
            $barang->mod_user_id = Auth::id();
            $barang->save();

            DB::commit();

            return redirect()->route('admin.barang.index')->with('success', 'Barang berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus barang: '.$e->getMessage());
        }
    }

    private function jenisOptions()
    {
        return BarangJenis::aktif()->orderBy('nama_jenis_barang')->get();
    }

    private function validated(Request $request)
    {
        return $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jenis_barang_id' => 'required|integer|exists:barang_jenis,barang_jenis_id',
        ]);
    }
}
