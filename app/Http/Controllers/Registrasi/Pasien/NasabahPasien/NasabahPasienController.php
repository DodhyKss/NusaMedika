<?php

namespace App\Http\Controllers\Registrasi\Pasien\NasabahPasien;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\KelasRuang;
use App\Models\Nasabah;
use App\Models\PasienNasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NasabahPasienController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = PasienNasabah::query()
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->with(['pasien', 'nasabah']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_peserta', 'ilike', "%{$search}%")
                    ->orWhereHas('pasien', function ($q2) use ($search) {
                        $q2->where('nama_pasien', 'ilike', "%{$search}%")
                            ->orWhere('no_mr', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('nasabah', function ($q2) use ($search) {
                        $q2->where('nama_nasabah', 'ilike', "%{$search}%");
                    });
            });
        }

        $nasabahPasiens = $query->orderByDesc('pasien_nasabah_id')->paginate(10)->withQueryString();

        $kelasMap = KelasRuang::aktif()
            ->orderBy('kelas_ruang_id')
            ->pluck('nama_kelas_ruang', 'kelas_ruang_id')
            ->map(fn ($nama) => (string) $nama)
            ->all();

        return view('moduls.registrasi.pasien.data_nasabah_pasien.nasabah_pasien', compact('nasabahPasiens', 'search', 'kelasMap'));
    }

    public function create()
    {
        $nasabahs = $this->nasabahAktif();
        $kelas = KelasRuang::aktif()->orderBy('kelas_ruang_id')->get();

        return view('moduls.registrasi.pasien.data_nasabah_pasien.tambah_nasabah_pasien', compact('nasabahs', 'kelas'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $nasabah = $this->resolveNasabah($data['nasabah_id']);

            $pasienNasabah = new PasienNasabah;
            $pasienNasabah->pasien_nasabah_id = GenerateHelper::getNextId('pasien_nasabah');
            $pasienNasabah->pasien_id = $data['pasien_id'];
            $pasienNasabah->nasabah_id = $nasabah->nasabah_id;
            $pasienNasabah->no_peserta = $data['nomor_kartu'];
            $pasienNasabah->hak_kelas_id = $data['kelas_perawatan'];
            $pasienNasabah->catatan = $data['catatan'];
            $pasienNasabah->input_time = now();
            $pasienNasabah->input_user_id = Auth::id();
            $pasienNasabah->status_batal = 0;
            $pasienNasabah->save();

            DB::commit();

            return redirect()->route('nasabah_pasien.index')->with('success', 'Data nasabah pasien berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data nasabah pasien: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $pasienNasabah = PasienNasabah::with(['pasien', 'nasabah'])->findOrFail($id);
        $nasabahs = $this->nasabahAktif();
        $kelas = KelasRuang::aktif()->orderBy('kelas_ruang_id')->get();

        return view('moduls.registrasi.pasien.data_nasabah_pasien.edit_nasabah_pasien', compact('pasienNasabah', 'nasabahs', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $pasienNasabah = PasienNasabah::findOrFail($id);
            $nasabah = $this->resolveNasabah($data['nasabah_id']);

            $pasienNasabah->pasien_id = $data['pasien_id'];
            $pasienNasabah->nasabah_id = $nasabah->nasabah_id;
            $pasienNasabah->no_peserta = $data['nomor_kartu'];
            $pasienNasabah->hak_kelas_id = $data['kelas_perawatan'];
            $pasienNasabah->catatan = $data['catatan'];
            $pasienNasabah->mod_time = now();
            $pasienNasabah->mod_user_id = Auth::id();
            $pasienNasabah->save();

            DB::commit();

            return redirect()->route('nasabah_pasien.index')->with('success', 'Data nasabah pasien berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui data nasabah pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pasienNasabah = PasienNasabah::findOrFail($id);
            $pasienNasabah->status_batal = 1;
            $pasienNasabah->mod_time = now();
            $pasienNasabah->mod_user_id = Auth::id();
            $pasienNasabah->save();

            DB::commit();

            return redirect()->route('nasabah_pasien.index')->with('success', 'Data nasabah pasien berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data nasabah pasien: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'pasien_id' => 'required|integer|exists:pasien,pasien_id',
            'nasabah_id' => 'nullable|integer|exists:nasabah,nasabah_id',
            'nomor_kartu' => 'nullable|string|max:20',
            'kelas_perawatan' => 'nullable|integer|exists:kelas_ruang,kelas_ruang_id',
            'catatan' => 'nullable|string|max:500',
        ]);

        return array_merge([
            'nasabah_id' => null,
            'nomor_kartu' => null,
            'kelas_perawatan' => null,
            'catatan' => null,
        ], $data);
    }

    private function nasabahAktif()
    {
        return Nasabah::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_nasabah')->get();
    }

    private function resolveNasabah($nasabahId)
    {
        if (! empty($nasabahId)) {
            return Nasabah::where('nasabah_id', $nasabahId)
                ->where(function ($q) {
                    $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
                })
                ->firstOrFail();
        }

        return $this->findOrCreateNasabah('');
    }

    private function findOrCreateNasabah($nama)
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            $nama = 'Umum / Mandiri';
        }

        $nasabah = Nasabah::where('nama_nasabah', $nama)
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->first();

        if ($nasabah) {
            return $nasabah;
        }

        $nasabah = new Nasabah;
        $nasabah->nasabah_id = GenerateHelper::getNextId('nasabah');
        $nasabah->nama_nasabah = $nama;
        $nasabah->input_time = now();
        $nasabah->input_user_id = Auth::id();
        $nasabah->status_batal = 0;
        $nasabah->save();

        return $nasabah;
    }
}
