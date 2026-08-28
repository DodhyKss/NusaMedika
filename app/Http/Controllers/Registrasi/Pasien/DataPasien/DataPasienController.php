<?php

namespace App\Http\Controllers\Registrasi\Pasien\DataPasien;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\Kelurahan;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DataPasienController extends Controller
{
    public function index(Request $request)
    {
        $pasienId = (int) $request->input('pasien_id');
        $jenisKelamin = $request->input('jenis_kelamin');

        $query = Pasien::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });

        if ($pasienId > 0) {
            $query->where('pasien_id', $pasienId);
        }

        if (! empty($jenisKelamin)) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        $pasiens = $query->orderBy('pasien_id', 'desc')->paginate(10)->withQueryString();

        $selectedPasien = $pasienId > 0 ? Pasien::where('pasien_id', $pasienId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first() : null;

        return view('moduls.registrasi.pasien.data_pasien.daftar_pasien', compact('pasiens', 'pasienId', 'jenisKelamin', 'selectedPasien'));
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
            $pasien->no_mr = GenerateHelper::generateNoMr();
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

        $kelurahan = $pasien->kelurahan_id ? Kelurahan::aktif()->find($pasien->kelurahan_id) : null;
        $kecamatan = $kelurahan?->kecamatan;
        $kabupaten = $kecamatan?->kabupaten;
        $provinsi = $kabupaten?->provinsi;

        $prefill = [
            'provinsi_id' => $provinsi?->provinsi_id,
            'provinsi_nama' => $provinsi?->nama_provinsi,
            'kabupaten_id' => $kabupaten?->kabupaten_id,
            'kabupaten_nama' => $kabupaten?->nama_kabupaten,
            'kecamatan_id' => $kecamatan?->kecamatan_id,
            'kecamatan_nama' => $kecamatan?->nama_kecamatan,
            'kelurahan_id' => $kelurahan?->kelurahan_id,
            'kelurahan_nama' => $kelurahan?->nama_kelurahan,
        ];

        return view('moduls.registrasi.pasien.data_pasien.edit_pasien', compact('pasien', 'prefill'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedData($request, (int) $id);

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

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate(
            [
                'ktp' => [
                    'required',
                    'digits:16',
                    Rule::unique('pasien', 'ktp')
                        ->where(function ($q) {
                            $q->where(fn ($q2) => $q2->where('status_batal', '!=', 1)->orWhereNull('status_batal'));
                        })
                        ->ignore($ignoreId, 'pasien_id'),
                ],
                'nama_pasien' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tgl_lahir' => 'required|date',
                'jenis_kelamin' => 'required|in:L,P',
                'agama' => 'required|string|max:100',
                'no_hp' => 'nullable|string|max:15',
                'nama_ibu_kandung' => 'nullable|string|max:100',
                'nama_ayah_kandung' => 'nullable|string|max:100',
                'gol_darah' => 'required|string|max:10',
                'status_perkawinan' => 'required|string|max:20',
                'kebangsaan' => 'required|string|max:10',
                'suku' => 'required|string|max:100',
                'pendidikan' => 'required|string|max:100',
                'pekerjaan' => 'required|string|max:100',
                'disabilitas' => 'required|string|max:100',
                'alamat' => 'required|string',
                'wilayah_provinsi_id' => 'required|integer|exists:provinsi,provinsi_id',
                'wilayah_kabupaten_id' => 'required|integer|exists:kabupaten,kabupaten_id',
                'wilayah_kecamatan_id' => 'required|integer|exists:kecamatan,kecamatan_id',
                'kelurahan_id' => 'required|integer|exists:kelurahan,kelurahan_id',
            ],
            [
                'ktp.unique' => 'No. KTP / NIK sudah terdaftar pada pasien lain.',
            ]
        );
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
        $pasien->nama_ayah_kandung = $data['nama_ayah_kandung'] ?? null;
        $pasien->gol_darah = $data['gol_darah'] ?? null;
        $pasien->status_perkawinan = $data['status_perkawinan'] ?? null;
        $pasien->kebangsaan = $data['kebangsaan'] ?? null;
        $pasien->suku = $data['suku'] ?? null;
        $pasien->pendidikan = $data['pendidikan'] ?? null;
        $pasien->pekerjaan = $data['pekerjaan'] ?? null;
        $pasien->disabilitas = $data['disabilitas'] ?? null;
        $pasien->alamat = $data['alamat'] ?? null;
        $pasien->kelurahan_id = $data['kelurahan_id'] ?? null;
    }
}
