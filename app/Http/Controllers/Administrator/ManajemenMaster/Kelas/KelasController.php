<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Kelas;

use App\Http\Controllers\Controller;
use App\Models\KelasRuang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = KelasRuang::aktif();

        if ($search !== '') {
            $query->where('nama_kelas_ruang', 'ilike', "%{$search}%");
        }

        $kelasList = $query->orderBy('kelas_ruang_id')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Kelas.index', compact('kelasList', 'search'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Kelas.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $kelas = new KelasRuang;
            $kelas->nama_kelas_ruang = $data['nama_kelas_ruang'];
            $kelas->kelas_khusus = $data['kelas_khusus'];
            $kelas->kelas_bpjs = $data['kelas_bpjs'];
            $kelas->input_time = now();
            $kelas->input_user_id = Auth::id();
            $kelas->status_batal = 0;
            $kelas->save();

            DB::commit();

            return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan kelas: '.$e->getMessage())->withInput();
        }
    }

    public function edit($kelas)
    {
        $kelas = KelasRuang::findOrFail($kelas);

        return view('moduls.Administrator.ManajemenMaster.Kelas.edit', compact('kelas'));
    }

    public function update(Request $request, $kelas)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $kelas = KelasRuang::findOrFail($kelas);
            $kelas->nama_kelas_ruang = $data['nama_kelas_ruang'];
            $kelas->kelas_khusus = $data['kelas_khusus'];
            $kelas->kelas_bpjs = $data['kelas_bpjs'];
            $kelas->mod_time = now();
            $kelas->mod_user_id = Auth::id();
            $kelas->save();

            DB::commit();

            return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui kelas: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($kelas)
    {
        DB::beginTransaction();
        try {
            $kelas = KelasRuang::findOrFail($kelas);
            $kelas->status_batal = 1;
            $kelas->mod_time = now();
            $kelas->mod_user_id = Auth::id();
            $kelas->save();

            DB::commit();

            return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus kelas: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'nama_kelas_ruang' => 'required|string|max:255',
            'kelas_khusus' => 'nullable|string|max:10',
            'kelas_bpjs' => 'nullable|integer',
        ]);

        return array_merge([
            'kelas_khusus' => null,
            'kelas_bpjs' => null,
        ], $data);
    }
}
