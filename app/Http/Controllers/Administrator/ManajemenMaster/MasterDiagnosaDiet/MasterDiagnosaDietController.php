<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterDiagnosaDiet;

use App\Http\Controllers\Controller;
use App\Models\MasterDiagnosaDiet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * CRUD Master Diagnosa Diet (prasyarat PRE-05, dipakai form 43 "Diet Pasien").
 *
 * Faktor penyakit penyerta yang mendasari prescribe diet. Nilainya dipakai
 * sebagai opsi multi-pilih `jenis_diagnosa_diet` yang disimpan comma-separated
 * dalam SATU baris `emr_detail` (lihat `DietPasienController::filteredData()`).
 *
 * Mengikuti pola `MasterImplementasiController` persis: soft delete lewat
 * `status_batal`, write dalam transaksi, `kode_diagnosa_diet` unik dengan
 * `Rule::unique(...)->ignore($id, '<pk>')`.
 *
 * **Nama diagnosis juga tersimpan sebagai nilai di `emr_detail`** (bukan id),
 * jadi mengubah `nama_diagnosa_diet` membuat riwayat lama tidak lagi cocok.
 */
class MasterDiagnosaDietController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = MasterDiagnosaDiet::aktif();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_diagnosa_diet', 'like', "%{$search}%")
                    ->orWhere('nama_diagnosa_diet', 'like', "%{$search}%");
            });
        }

        $diagnosaList = $query->orderBy('kode_diagnosa_diet')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.MasterDiagnosaDiet.master_diagnosa_diet', compact(
            'diagnosaList',
            'search'
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterDiagnosaDiet.master_diagnosa_diet_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $row = new MasterDiagnosaDiet;
            $row->kode_diagnosa_diet = strtoupper(trim($data['kode_diagnosa_diet']));
            $row->nama_diagnosa_diet = $data['nama_diagnosa_diet'];
            $row->keterangan = $data['keterangan'] ?? null;
            $row->input_time = now();
            $row->input_user_id = Auth::id();
            $row->status_batal = 0;
            $row->save();

            DB::commit();

            return redirect()->route('admin.master_diagnosa_diet.index')
                ->with('success', 'Diagnosa Diet berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan diagnosis diet: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterDiagnosaDiet)
    {
        $diagnosa = MasterDiagnosaDiet::findOrFail($masterDiagnosaDiet);

        return view('moduls.Administrator.ManajemenMaster.MasterDiagnosaDiet.master_diagnosa_diet_edit', compact('diagnosa'));
    }

    public function update(Request $request, $masterDiagnosaDiet)
    {
        $data = $this->validated($request, $masterDiagnosaDiet);

        DB::beginTransaction();
        try {
            $row = MasterDiagnosaDiet::findOrFail($masterDiagnosaDiet);
            $row->kode_diagnosa_diet = strtoupper(trim($data['kode_diagnosa_diet']));
            $row->nama_diagnosa_diet = $data['nama_diagnosa_diet'];
            $row->keterangan = $data['keterangan'] ?? null;
            $row->mod_time = now();
            $row->mod_user_id = Auth::id();
            $row->save();

            DB::commit();

            return redirect()->route('admin.master_diagnosa_diet.index')
                ->with('success', 'Diagnosa Diet berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui diagnosis diet: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterDiagnosaDiet)
    {
        DB::beginTransaction();
        try {
            $row = MasterDiagnosaDiet::findOrFail($masterDiagnosaDiet);
            $row->status_batal = 1;
            $row->mod_time = now();
            $row->mod_user_id = Auth::id();
            $row->save();

            DB::commit();

            return redirect()->route('admin.master_diagnosa_diet.index')
                ->with('success', 'Diagnosa Diet berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus diagnosis diet: '.$e->getMessage());
        }
    }

    private function validated(Request $request, $masterDiagnosaDiet = null)
    {
        // Tabel `master_diagnosa_diet` tidak punya kolom `id` (PK custom), jadi
        // nama kolom PK wajib ditulis eksplisit pada Rule::ignore().
        $ignore = $masterDiagnosaDiet ? (int) $masterDiagnosaDiet : null;

        return $request->validate([
            'kode_diagnosa_diet' => [
                'required',
                'string',
                'max:20',
                Rule::unique('master_diagnosa_diet', 'kode_diagnosa_diet')->ignore($ignore, 'master_diagnosa_diet_id'),
            ],
            'nama_diagnosa_diet' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);
    }
}
