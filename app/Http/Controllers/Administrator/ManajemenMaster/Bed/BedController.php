<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Bed;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Bed;
use App\Models\KelasRuang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $bagians = $this->ruangPerawatan();

        $bagianId = (int) $request->input('bagian_id');
        if (! $bagians->contains('bagian_id', $bagianId)) {
            $bagianId = $bagians->first()?->bagian_id ?? 0;
        }

        $search = trim((string) $request->input('search'));

        $query = Bed::aktif()->where('bagian_id', $bagianId);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_kamar', 'like', "%{$search}%")
                    ->orWhere('nama_bed', 'like', "%{$search}%");
            });
        }

        $beds = $query->orderBy('no_kamar')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Bed.bed', compact('beds', 'bagians', 'bagianId', 'search'));
    }

    public function create()
    {
        $bagians = $this->ruangPerawatan();
        $kelasList = KelasRuang::aktif()->orderBy('kelas_ruang_id')->get();

        return view('moduls.Administrator.ManajemenMaster.Bed.bed_create', compact('bagians', 'kelasList'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $bed = new Bed;
            $this->fill($bed, $data);
            $bed->input_time = now();
            $bed->input_user_id = Auth::id();
            $bed->status_batal = 0;
            $bed->save();

            DB::commit();

            return redirect()->route('admin.bed.index')->with('success', 'Bed berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan bed: '.$e->getMessage())->withInput();
        }
    }

    public function edit($bed)
    {
        $bed = Bed::aktif()->findOrFail($bed);
        $bagians = $this->ruangPerawatan();
        $kelasList = KelasRuang::aktif()->orderBy('kelas_ruang_id')->get();

        return view('moduls.Administrator.ManajemenMaster.Bed.bed_edit', compact('bed', 'bagians', 'kelasList'));
    }

    public function update(Request $request, $bed)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->findOrFail($bed);
            $this->fill($bed, $data);
            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            DB::commit();

            return redirect()->route('admin.bed.index')->with('success', 'Bed berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui bed: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($bed)
    {
        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->findOrFail($bed);
            $bed->status_batal = 1;
            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            DB::commit();

            return redirect()->route('admin.bed.index')->with('success', 'Bed berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus bed: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'bagian_id' => 'required|integer|exists:bagian,bagian_id',
            'no_kamar' => 'required|string|max:50',
            'nama_bed' => 'required|string|max:30',
            'kelas_id' => 'required|integer|exists:kelas_ruang,kelas_ruang_id',
            'flag_isolasi' => 'nullable|integer',
            'flag_isolasi_pressure' => 'nullable|integer',
            'flag_ventilator' => 'nullable|integer',
            'flag_neonatus' => 'nullable|integer',
            'siap_kirim' => 'nullable|integer',
            'keterangan' => 'nullable|string|max:250',
        ]);

        return array_merge([
            'flag_isolasi' => 2,
            'flag_isolasi_pressure' => 2,
            'flag_ventilator' => 2,
            'flag_neonatus' => 0,
            'siap_kirim' => null,
            'keterangan' => null,
        ], $data);
    }

    private function fill(Bed $bed, array $data): void
    {
        $kelas = KelasRuang::aktif()->find($data['kelas_id']);

        $bed->bagian_id = $data['bagian_id'];
        $bed->no_kamar = $data['no_kamar'];
        $bed->nama_bed = $data['nama_bed'];
        $bed->kelas_id = $kelas?->kelas_ruang_id;
        $bed->kodekelas = $kelas?->kelas_bpjs;
        $bed->namakelas = $kelas?->nama_kelas_ruang;
        $bed->flag_isolasi = $data['flag_isolasi'];
        $bed->flag_isolasi_pressure = $data['flag_isolasi_pressure'];
        $bed->flag_ventilator = $data['flag_ventilator'];
        $bed->flag_neonatus = $data['flag_neonatus'];
        $bed->siap_kirim = $data['siap_kirim'];
        $bed->keterangan = $data['keterangan'];
    }

    private function ruangPerawatan()
    {
        return Bagian::where('referensi_bagian_id', env('REF_BAGIAN_RANAP', 2))
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('bagian_id')
            ->get(['bagian_id', 'nama_bagian']);
    }
}
