<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterTindakan;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\KelasRuang;
use App\Models\Tindakan;
use App\Models\TindakanHarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterTindakanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $bagianId = (int) $request->input('bagian_id');
        $kategori = trim((string) $request->input('kategori'));

        $query = Tindakan::aktif()->with('bagian');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_tindakan', 'like', "%{$search}%")
                    ->orWhere('nama_tindakan', 'like', "%{$search}%");
            });
        }

        if ($bagianId) {
            $query->where('bagian_id', $bagianId);
        }

        if ($kategori !== '') {
            $query->where('kategori', $kategori);
        }

        $tindakanList = $query->orderBy('kode_tindakan')->paginate(10)->withQueryString();

        $bagianList = Bagian::aktif()
            ->where('referensi_bagian_id', 6)
            ->orderBy('nama_bagian')
            ->get();

        return view('moduls.Administrator.ManajemenMaster.MasterTindakan.master_tindakan', compact(
            'tindakanList',
            'search',
            'bagianId',
            'kategori',
            'bagianList'
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterTindakan.master_tindakan_create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $tindakan = new Tindakan;
            $tindakan->kode_tindakan = strtoupper(trim($data['kode_tindakan']));
            $tindakan->nama_tindakan = $data['nama_tindakan'];
            $tindakan->bagian_id = $data['bagian_id'];
            $tindakan->kategori = $data['kategori'];
            $tindakan->satuan_hasil = $data['satuan_hasil'];
            $tindakan->nilai_normal = $data['nilai_normal'];
            $tindakan->kode_bpjs = $data['kode_bpjs'];
            $tindakan->kode_inacbg = $data['kode_inacbg'];
            $tindakan->kode_loinc = $data['kode_loinc'];
            $tindakan->keterangan = $data['keterangan'];
            $tindakan->input_time = now();
            $tindakan->input_user_id = Auth::id();
            $tindakan->status_batal = 0;
            $tindakan->save();

            $this->syncHarga((int) $tindakan->tindakan_id, $request);

            DB::commit();

            return redirect()->route('admin.master_tindakan.index')->with('success', 'Tindakan berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterTindakan)
    {
        $tindakan = Tindakan::aktif()->with('harga')->findOrFail($masterTindakan);

        $hargaByKelas = $tindakan->harga
            ->mapWithKeys(fn ($h) => [(string) ($h->kelas_ruang_id ?? 'default') => $h])
            ->all();

        return view('moduls.Administrator.ManajemenMaster.MasterTindakan.master_tindakan_edit', array_merge(
            $this->formData(),
            compact('tindakan', 'hargaByKelas')
        ));
    }

    public function update(Request $request, $masterTindakan)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $tindakan = Tindakan::aktif()->findOrFail($masterTindakan);
            $tindakan->kode_tindakan = strtoupper(trim($data['kode_tindakan']));
            $tindakan->nama_tindakan = $data['nama_tindakan'];
            $tindakan->bagian_id = $data['bagian_id'];
            $tindakan->kategori = $data['kategori'];
            $tindakan->satuan_hasil = $data['satuan_hasil'];
            $tindakan->nilai_normal = $data['nilai_normal'];
            $tindakan->kode_bpjs = $data['kode_bpjs'];
            $tindakan->kode_inacbg = $data['kode_inacbg'];
            $tindakan->kode_loinc = $data['kode_loinc'];
            $tindakan->keterangan = $data['keterangan'];
            $tindakan->mod_time = now();
            $tindakan->mod_user_id = Auth::id();
            $tindakan->save();

            $this->syncHarga((int) $tindakan->tindakan_id, $request);

            DB::commit();

            return redirect()->route('admin.master_tindakan.index')->with('success', 'Tindakan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterTindakan)
    {
        DB::beginTransaction();
        try {
            $tindakan = Tindakan::aktif()->findOrFail($masterTindakan);

            Tindakan::where('tindakan_id', $tindakan->tindakan_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => Auth::id(),
                ]);

            TindakanHarga::where('tindakan_id', $tindakan->tindakan_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => Auth::id(),
                ]);

            DB::commit();

            return redirect()->route('admin.master_tindakan.index')->with('success', 'Tindakan berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus tindakan: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'kode_tindakan' => 'required|string|max:20',
            'nama_tindakan' => 'required|string|max:255',
            'bagian_id' => 'required|integer|exists:bagian,bagian_id',
            'kategori' => 'required|in:LAB,RAD,LAIN',
            'satuan_hasil' => 'nullable|string|max:20',
            'nilai_normal' => 'nullable|string|max:100',
            'kode_bpjs' => 'nullable|string|max:20',
            'kode_inacbg' => 'nullable|string|max:20',
            'kode_loinc' => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
        ]);

        return array_merge([
            'satuan_hasil' => null,
            'nilai_normal' => null,
            'kode_bpjs' => null,
            'kode_inacbg' => null,
            'kode_loinc' => null,
            'keterangan' => null,
        ], $data);
    }

    /**
     * Sinkronkan tarif per kelas (tindakan_harga): soft-delete tarif aktif lama,
     * lalu insert ulang dari form (baris default kelas_ruang_id NULL + per kelas).
     */
    private function syncHarga(int $tindakanId, Request $request): void
    {
        $user = Auth::id();
        $now = now();

        TindakanHarga::where('tindakan_id', $tindakanId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->update([
                'status_batal' => 1,
                'mod_time' => $now,
                'mod_user_id' => $user,
            ]);

        $tarifs = (array) $request->input('tarif', []);
        $tarifBpjs = (array) $request->input('tarif_bpjs', []);
        $kelasIds = array_keys($tarifs);

        foreach ($kelasIds as $key) {
            $tarif = is_numeric($tarifs[$key] ?? null) ? (float) $tarifs[$key] : null;

            // Selalu simpan tarif: baris default (kelas NULL) & kelas spesifik.
            $harga = new TindakanHarga;
            $harga->tindakan_id = $tindakanId;
            $harga->kelas_ruang_id = $key === 'default' ? null : (int) $key;
            $harga->tarif = $tarif;
            $harga->tarif_bpjs = array_key_exists($key, $tarifBpjs) && is_numeric($tarifBpjs[$key])
                ? (float) $tarifBpjs[$key]
                : null;
            $harga->input_time = $now;
            $harga->input_user_id = $user;
            $harga->status_batal = 0;
            $harga->save();
        }
    }

    private function formData(): array
    {
        return [
            'bagianList' => Bagian::aktif()
                ->where('referensi_bagian_id', 6)
                ->orderBy('nama_bagian')
                ->get(),
            'kelasList' => KelasRuang::aktif()->orderBy('kelas_ruang_id')->get(),
        ];
    }
}
