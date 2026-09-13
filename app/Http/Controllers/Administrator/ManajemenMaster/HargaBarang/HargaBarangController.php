<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\HargaBarang;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\HargaBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HargaBarangController extends Controller
{
    public function index(Request $request)
    {
        $barangId = $request->input('barang_id');
        $search = trim((string) $request->input('search'));

        $query = HargaBarang::aktif()->with('barang.satuan');

        if ($barangId !== null && $barangId !== '') {
            $query->where('barang_id', (int) $barangId);
        }

        if ($search !== '') {
            $query->whereHas('barang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        $hargaList = $query->orderBy('barang_id')->orderByDesc('harga_id')->paginate(10)->withQueryString();

        $barangList = Barang::aktif()->orderBy('nama_barang')->get();

        return view('moduls.Administrator.ManajemenMaster.HargaBarang.harga_barang', compact('hargaList', 'barangId', 'search', 'barangList'));
    }

    public function create()
    {
        $barangList = Barang::aktif()->with('satuan')->orderBy('nama_barang')->get();

        return view('moduls.Administrator.ManajemenMaster.HargaBarang.harga_barang_create', compact('barangList'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $ada = HargaBarang::aktif()
                ->where('barang_id', $data['barang_id'])
                ->where('no_batch', $data['no_batch'])
                ->exists();

            if ($ada) {
                throw new \RuntimeException('Harga untuk barang + no. batch tersebut sudah ada.');
            }

            $harga = new HargaBarang;
            $harga->barang_id = $data['barang_id'];
            $harga->no_batch = $data['no_batch'];
            $harga->harga_beli = $data['harga_beli'];
            $harga->harga_jual = $data['harga_jual'];
            $harga->input_time = now();
            $harga->input_user_id = Auth::id();
            $harga->status_batal = 0;
            $harga->save();

            DB::commit();

            return redirect()->route('admin.harga_barang.index')->with('success', 'Harga barang berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan harga: '.$e->getMessage())->withInput();
        }
    }

    public function edit($harga)
    {
        $harga = HargaBarang::aktif()->findOrFail($harga);
        $barangList = Barang::aktif()->with('satuan')->orderBy('nama_barang')->get();

        return view('moduls.Administrator.ManajemenMaster.HargaBarang.harga_barang_edit', compact('harga', 'barangList'));
    }

    public function update(Request $request, $harga)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $harga = HargaBarang::aktif()->findOrFail($harga);

            $duplikat = HargaBarang::aktif()
                ->where('barang_id', $data['barang_id'])
                ->where('no_batch', $data['no_batch'])
                ->where('harga_id', '!=', $harga->harga_id)
                ->exists();

            if ($duplikat) {
                throw new \RuntimeException('Harga untuk barang + no. batch tersebut sudah ada.');
            }

            $harga->barang_id = $data['barang_id'];
            $harga->no_batch = $data['no_batch'];
            $harga->harga_beli = $data['harga_beli'];
            $harga->harga_jual = $data['harga_jual'];
            $harga->mod_time = now();
            $harga->mod_user_id = Auth::id();
            $harga->save();

            DB::commit();

            return redirect()->route('admin.harga_barang.index')->with('success', 'Harga barang berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui harga: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($harga)
    {
        DB::beginTransaction();
        try {
            $harga = HargaBarang::aktif()->findOrFail($harga);
            $harga->status_batal = 1;
            $harga->mod_time = now();
            $harga->mod_user_id = Auth::id();
            $harga->save();

            DB::commit();

            return redirect()->route('admin.harga_barang.index')->with('success', 'Harga barang berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus harga: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        return array_merge([
            'harga_beli' => null,
            'harga_jual' => null,
        ], $request->validate([
            'barang_id' => 'required|integer|exists:barang,barang_id',
            'no_batch' => 'required|string|max:50',
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
        ]));
    }
}
