<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Icd;

use App\Http\Controllers\Controller;
use App\Models\ICD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IcdController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = ICD::aktif();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_diagnosa', 'ilike', "%{$search}%")
                    ->orWhere('nama_diagnosa', 'ilike', "%{$search}%");
            });
        }

        $icdList = $query->orderBy('kode_diagnosa')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Icd.icd', compact('icdList', 'search'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Icd.icd_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $icd = new ICD;
            $icd->kode_diagnosa = $data['kode_diagnosa'];
            $icd->nama_diagnosa = $data['nama_diagnosa'];
            $icd->kategori = $data['kategori'];
            $icd->jenis_diagnosa = $data['jenis_diagnosa'];
            $icd->penyakit_id = $data['penyakit_id'];
            $icd->input_time = now();
            $icd->input_user_id = Auth::id();
            $icd->status_batal = 0;
            $icd->save();

            DB::commit();

            return redirect()->route('admin.icd.index')->with('success', 'Data ICD berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data ICD: '.$e->getMessage())->withInput();
        }
    }

    public function edit($icd)
    {
        $icd = ICD::findOrFail($icd);

        return view('moduls.Administrator.ManajemenMaster.Icd.icd_edit', compact('icd'));
    }

    public function update(Request $request, $icd)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $icd = ICD::findOrFail($icd);
            $icd->kode_diagnosa = $data['kode_diagnosa'];
            $icd->nama_diagnosa = $data['nama_diagnosa'];
            $icd->kategori = $data['kategori'];
            $icd->jenis_diagnosa = $data['jenis_diagnosa'];
            $icd->penyakit_id = $data['penyakit_id'];
            $icd->mod_time = now();
            $icd->mod_user_id = Auth::id();
            $icd->save();

            DB::commit();

            return redirect()->route('admin.icd.index')->with('success', 'Data ICD berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui data ICD: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($icd)
    {
        DB::beginTransaction();
        try {
            $icd = ICD::findOrFail($icd);
            $icd->status_batal = 1;
            $icd->mod_time = now();
            $icd->mod_user_id = Auth::id();
            $icd->save();

            DB::commit();

            return redirect()->route('admin.icd.index')->with('success', 'Data ICD berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data ICD: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'kode_diagnosa' => 'required|string|max:10',
            'nama_diagnosa' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:10',
            'jenis_diagnosa' => 'nullable|integer',
            'penyakit_id' => 'nullable|integer',
        ]);

        return array_merge([
            'kategori' => null,
            'jenis_diagnosa' => null,
            'penyakit_id' => null,
        ], $data);
    }
}
