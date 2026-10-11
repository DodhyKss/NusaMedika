<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterDiet;

use App\Http\Controllers\Controller;
use App\Models\MasterDiet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * CRUD Master Jenis Diet (prasyarat PRE-05, dipakai form 43 "Diet Pasien").
 *
 * Mengikuti pola `MasterImplementasiController` persis:
 * - soft delete lewat `status_batal = 1` (bukan `deleted_at`),
 * - seluruh write di dalam `DB::beginTransaction()`,
 * - `kode_diet` unik dengan `Rule::unique(...)->ignore($id, '<pk>')`.
 *
 * `nama_diet` adalah nilai yang BENAR-BENAR disimpan di `emr_detail` pada
 * variabel `jenis_diet` (form 43 memvalidasinya `exists:master_diet,nama_diet`).
 * **Mengubah `nama_diet` membuat riwayat diet lama tidak lagi cocok** — karena
 * itu halaman edit menampilkan peringatan di bawah form.
 */
class MasterDietController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = MasterDiet::aktif();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_diet', 'like', "%{$search}%")
                    ->orWhere('nama_diet', 'like', "%{$search}%");
            });
        }

        $dietList = $query->orderBy('kode_diet')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.MasterDiet.master_diet', compact(
            'dietList',
            'search'
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterDiet.master_diet_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $diet = new MasterDiet;
            $diet->kode_diet = strtoupper(trim($data['kode_diet']));
            $diet->nama_diet = $data['nama_diet'];
            $diet->keterangan = $data['keterangan'] ?? null;
            $diet->input_time = now();
            $diet->input_user_id = Auth::id();
            $diet->status_batal = 0;
            $diet->save();

            DB::commit();

            return redirect()->route('admin.master_diet.index')
                ->with('success', 'Diet berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan diet: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterDiet)
    {
        $diet = MasterDiet::findOrFail($masterDiet);

        return view('moduls.Administrator.ManajemenMaster.MasterDiet.master_diet_edit', compact('diet'));
    }

    public function update(Request $request, $masterDiet)
    {
        $data = $this->validated($request, $masterDiet);

        DB::beginTransaction();
        try {
            $diet = MasterDiet::findOrFail($masterDiet);
            $diet->kode_diet = strtoupper(trim($data['kode_diet']));
            $diet->nama_diet = $data['nama_diet'];
            $diet->keterangan = $data['keterangan'] ?? null;
            $diet->mod_time = now();
            $diet->mod_user_id = Auth::id();
            $diet->save();

            DB::commit();

            return redirect()->route('admin.master_diet.index')
                ->with('success', 'Diet berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui diet: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterDiet)
    {
        DB::beginTransaction();
        try {
            $diet = MasterDiet::findOrFail($masterDiet);
            $diet->status_batal = 1;
            $diet->mod_time = now();
            $diet->mod_user_id = Auth::id();
            $diet->save();

            DB::commit();

            return redirect()->route('admin.master_diet.index')
                ->with('success', 'Diet berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus diet: '.$e->getMessage());
        }
    }

    private function validated(Request $request, $masterDiet = null)
    {
        // Tabel `master_diet` tidak punya kolom `id` (PK custom), jadi nama kolom
        // PK wajib ditulis eksplisit pada Rule::ignore().
        $ignore = $masterDiet ? (int) $masterDiet : null;

        return $request->validate([
            'kode_diet' => [
                'required',
                'string',
                'max:20',
                Rule::unique('master_diet', 'kode_diet')->ignore($ignore, 'master_diet_id'),
            ],
            'nama_diet' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);
    }
}
