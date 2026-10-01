<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterImplementasi;

use App\Http\Controllers\Controller;
use App\Models\Implementasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterImplementasiController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Implementasi::aktif();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_implementasi', 'like', "%{$search}%")
                    ->orWhere('nama_implementasi', 'like', "%{$search}%");
            });
        }

        $implementasiList = $query->orderBy('kode_implementasi')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.MasterImplementasi.master_implementasi', compact(
            'implementasiList',
            'search'
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterImplementasi.master_implementasi_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $implementasi = new Implementasi;
            $implementasi->kode_implementasi = strtoupper(trim($data['kode_implementasi']));
            $implementasi->nama_implementasi = $data['nama_implementasi'];
            $implementasi->input_time = now();
            $implementasi->input_user_id = Auth::id();
            $implementasi->status_batal = 0;
            $implementasi->save();

            DB::commit();

            return redirect()->route('admin.master_implementasi.index')
                ->with('success', 'Implementasi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan implementasi: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterImplementasi)
    {
        $implementasi = Implementasi::findOrFail($masterImplementasi);

        return view('moduls.Administrator.ManajemenMaster.MasterImplementasi.master_implementasi_edit', compact(
            'implementasi'
        ));
    }

    public function update(Request $request, $masterImplementasi)
    {
        $data = $this->validated($request, $masterImplementasi);

        DB::beginTransaction();
        try {
            $implementasi = Implementasi::findOrFail($masterImplementasi);
            $implementasi->kode_implementasi = strtoupper(trim($data['kode_implementasi']));
            $implementasi->nama_implementasi = $data['nama_implementasi'];
            $implementasi->mod_time = now();
            $implementasi->mod_user_id = Auth::id();
            $implementasi->save();

            DB::commit();

            return redirect()->route('admin.master_implementasi.index')
                ->with('success', 'Implementasi berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui implementasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterImplementasi)
    {
        DB::beginTransaction();
        try {
            $implementasi = Implementasi::findOrFail($masterImplementasi);
            $implementasi->status_batal = 1;
            $implementasi->mod_time = now();
            $implementasi->mod_user_id = Auth::id();
            $implementasi->save();

            DB::commit();

            return redirect()->route('admin.master_implementasi.index')
                ->with('success', 'Implementasi berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus implementasi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, $masterImplementasi = null)
    {
        // Tabel `implementasi` tidak punya kolom `id` (PK custom), jadi nama kolom
        // PK wajib ditulis eksplisit pada Rule::ignore().
        $ignore = $masterImplementasi ? (int) $masterImplementasi : null;

        return $request->validate([
            'kode_implementasi' => [
                'required',
                'string',
                'max:20',
                Rule::unique('implementasi', 'kode_implementasi')->ignore($ignore, 'implementasi_id'),
            ],
            'nama_implementasi' => 'required|string|max:255',
        ]);
    }
}
