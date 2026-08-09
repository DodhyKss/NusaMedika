<?php

namespace App\Http\Controllers\Registrasi\Pasien\DataPasien;

use App\Helpers\SequenceHelper;
use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DataPasienController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $jenisKelamin = $request->input('jenis_kelamin');

        $query = Pasien::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pasien', 'ilike', "%{$search}%")
                    ->orWhere('no_mr', 'ilike', "%{$search}%")
                    ->orWhere('ktp', 'ilike', "%{$search}%");
            });
        }

        if (! empty($jenisKelamin)) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $pasiens = $query->orderBy('pasien_id', 'desc')->paginate(10)->withQueryString();

        return view('moduls.registrasi.pasien.data_pasien.daftar_pasien', compact('pasiens', 'search', 'jenisKelamin'));
    }

    public function create()
    {
        return view('moduls.registrasi.pasien.data_pasien.tambah_pasien');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::beginTransaction();
        try {
            $pasien = new Pasien;
            $pasien->pasien_id = SequenceHelper::getNextId('pasien');
            $pasien->no_mr = $this->generateNoMr();
            $this->fillData($pasien, $data);
            $pasien->input_time = now();
            $pasien->input_user_id = Auth::id();
            $pasien->status_batal = 0;
            $pasien->save();

            DB::commit();

            return redirect()->route('daftar_pasien.index')->with('success', 'Pasien baru berhasil didaftarkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan pasien: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $pasien = Pasien::findOrFail($id);

        $kelurahan = $pasien->kelurahan_id ? Kelurahan::find($pasien->kelurahan_id) : null;
        $kecamatan = $kelurahan?->kecamatan;
        $kabupaten = $kecamatan?->kabupaten;
        $provinsi = $kabupaten?->provinsi;

        $prefill = [
            'provinsi_id' => $provinsi?->provinsi_id,
            'kabupaten_id' => $kabupaten?->kabupaten_id,
            'kecamatan_id' => $kecamatan?->kecamatan_id,
            'kelurahan_id' => $kelurahan?->kelurahan_id,
        ];

        return view('moduls.registrasi.pasien.data_pasien.edit_pasien', compact('pasien', 'prefill'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedData($request);

        DB::beginTransaction();
        try {
            $pasien = Pasien::findOrFail($id);
            $this->fillData($pasien, $data);
            $pasien->mod_time = now();
            $pasien->mod_user_id = Auth::id();
            $pasien->save();

            DB::commit();

            return redirect()->route('daftar_pasien.index')->with('success', 'Data pasien berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pasien = Pasien::findOrFail($id);
            $pasien->status_batal = 1;
            $pasien->mod_time = now();
            $pasien->mod_user_id = Auth::id();
            $pasien->save();

            DB::commit();

            return redirect()->route('daftar_pasien.index')->with('success', 'Pasien berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus pasien: '.$e->getMessage());
        }
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'ktp' => 'nullable|string|max:20',
            'nama_pasien' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:15',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'gol_darah' => 'nullable|string|max:10',
            'status_perkawinan' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'kelurahan_id' => 'nullable|integer|exists:kelurahan,kelurahan_id',
        ]);
    }

    private function fillData(Pasien $pasien, array $data): void
    {
        $pasien->nama_pasien = $data['nama_pasien'];
        $pasien->ktp = $data['ktp'] ?? null;
        $pasien->tempat_lahir = $data['tempat_lahir'] ?? null;
        $pasien->tgl_lahir = $data['tgl_lahir'] ?? null;
        $pasien->jenis_kelamin = $data['jenis_kelamin'];
        $pasien->agama = $data['agama'] ?? null;
        $pasien->no_hp = $data['no_hp'] ?? null;
        $pasien->nama_ibu_kandung = $data['nama_ibu_kandung'] ?? null;
        $pasien->gol_darah = $data['gol_darah'] ?? null;
        $pasien->status_perkawinan = $data['status_perkawinan'] ?? null;
        $pasien->alamat = $data['alamat'] ?? null;
        $pasien->kelurahan_id = $data['kelurahan_id'] ?? null;
    }

    private function generateNoMr(): string
    {
        $max = (int) DB::table('pasien')
            ->where('no_mr', '~', '^[0-9]+$')
            ->max('no_mr');

        return str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }
}
