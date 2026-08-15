<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarRajal;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\DiagnosaRawat;
use App\Models\JadwalDokter;
use App\Models\Nasabah;
use App\Models\PasienNasabah;
use App\Models\PenanggungRawat;
use App\Models\Registrasi;
use App\Models\RegistrasiDetail;
use App\Models\RegistrasiUrut;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaftarRajalController extends Controller
{
    public function index()
    {
        $jadwals = JadwalDokter::aktif()
            ->with(['pegawai', 'bagian'])
            ->orderBy('hari')
            ->orderBy('waktu_mulai')
            ->get();

        $polikliniks = $jadwals->map(fn ($jd) => $jd->bagian)
            ->filter()
            ->unique('bagian_id')
            ->sortBy('nama_bagian')
            ->values();

        $jadwalsByPoli = $jadwals->groupBy('bagian_id')->map(function ($group) {
            $hariMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

            return $group->map(function ($j) use ($hariMap) {
                $waktuMulai = Carbon::parse($j->waktu_mulai)->format('H:i');
                $waktuSelesai = Carbon::parse($j->waktu_selesai)->format('H:i');

                return [
                    'id' => $j->jadwal_dokter_id,
                    'dokter' => $j->pegawai->nama_pegawai ?? 'Unknown',
                    'hari' => $j->hari,
                    'nama_hari' => $hariMap[$j->hari] ?? '',
                    'waktu' => $waktuMulai.' - '.$waktuSelesai,
                    'kuota' => $j->kuota,
                ];
            })->values();
        })->toArray();

        $nasabahs = Nasabah::orderBy('nama_nasabah')->get();

        return view('moduls.registrasi.pendaftaran.daftar_rj.daftar_rajal', compact('polikliniks', 'nasabahs', 'jadwalsByPoli'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pasien_id' => 'required',
            'tgl_kunjungan' => 'required|date',
            'poliklinik' => 'required',
            'jadwal_dokter_id' => 'required|exists:jadwal_dokter,jadwal_dokter_id',
            'nasabah_id' => 'required',
            'cara_masuk' => 'required',
            'icd_id' => 'required',
            'keluhan' => 'required',
        ]);

        $aktif = Registrasi::where('pasien_id', $request->pasien_id)->whereDate('tgl_masuk', $request->tgl_kunjungan)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->exists();

        if ($aktif) {
            return back()->withInput()->with('error', 'Pasien telah memiliki pendaftaran aktif pada periode kunjungan ' . $request->tgl_kunjungan);
        }

        try {
            DB::beginTransaction();

            $pasienNasabah = PasienNasabah::where('pasien_id', $request->pasien_id)
                ->where('nasabah_id', $request->nasabah_id)
                ->first();

            if (! $pasienNasabah) {
                $pasienNasabahId = GenerateHelper::getNextId('pasien_nasabah');
                $pasienNasabah = new PasienNasabah;
                $pasienNasabah->pasien_nasabah_id = $pasienNasabahId;
                $pasienNasabah->pasien_id = $request->pasien_id;
                $pasienNasabah->nasabah_id = $request->nasabah_id;
                $pasienNasabah->save();
            }

            $registrasiId = GenerateHelper::getNextId('registrasi');
            $registrasi = new Registrasi;
            $registrasi->registrasi_id = $registrasiId;
            $registrasi->pasien_id = $request->pasien_id;
            $registrasi->tgl_masuk = $request->tgl_kunjungan.' '.date('H:i:s');
            $registrasi->jenis_rawat = 'RJ';
            $registrasi->pasien_nasabah_id = $pasienNasabah->pasien_nasabah_id;
            $registrasi->memo = $request->keluhan;
            $registrasi->status_batal = 0;
            $registrasi->input_time = now();
            $registrasi->input_user_id = auth()->id();
            $registrasi->save();

            $jadwal = JadwalDokter::find($request->jadwal_dokter_id);
            $dokter_id = $jadwal->pegawai_id;

            // Cari user_id dari dokter berdasarkan pegawai_id
            $userDokter = User::where('pegawai_id', $dokter_id)->first();
            $rawatUserId = $userDokter ? $userDokter->user_id : auth()->id();

            $registrasiDetailId = GenerateHelper::getNextId('registrasi_detail');
            $registrasiDetail = new RegistrasiDetail;
            $registrasiDetail->registrasi_detail_id = $registrasiDetailId;
            $registrasiDetail->registrasi_id = $registrasiId;
            $registrasiDetail->bagian_id = $request->poliklinik;
            $registrasiDetail->terima_dari = $request->cara_masuk;
            $registrasiDetail->tgl_daftar = now();
            $registrasiDetail->status_batal = 0;
            $registrasiDetail->input_time = now();
            $registrasiDetail->input_user_id = auth()->id();
            $registrasiDetail->save();

            $tglKunjungan = Carbon::parse($request->tgl_kunjungan)->toDateString();

            $urutan = GenerateHelper::generateNoUrut($dokter_id, $request->poliklinik, $tglKunjungan);

            $estimasi = GenerateHelper::hitungEstimasi($tglKunjungan, $jadwal->waktu_mulai, $urutan);

            $registrasiUrutId = GenerateHelper::getNextId('registrasi_urut');
            $registrasiUrut = new RegistrasiUrut;
            $registrasiUrut->registrasi_urut_id = $registrasiUrutId;
            $registrasiUrut->registrasi_detail_id = $registrasiDetailId;
            $registrasiUrut->pegawai_id = $dokter_id;
            $registrasiUrut->bagian_id = $request->poliklinik;
            $registrasiUrut->urutan = $urutan;
            $registrasiUrut->tgl_urut = $estimasi;
            $registrasiUrut->status_batal = 0;
            $registrasiUrut->input_time = now();
            $registrasiUrut->input_user_id = auth()->id();
            $registrasiUrut->save();

            $diagnosaRawatId = GenerateHelper::getNextId('diagnosa_rawat');
            $diagnosaRawat = new DiagnosaRawat;
            $diagnosaRawat->diagnosa_rawat_id = $diagnosaRawatId;
            $diagnosaRawat->registrasi_id = $registrasiId;
            $diagnosaRawat->icd_id = $request->icd_id;
            $diagnosaRawat->jenis_diagnosa = 1;
            $diagnosaRawat->status_batal = 0;
            $diagnosaRawat->input_time = now();
            $diagnosaRawat->input_user_id = auth()->id();
            $diagnosaRawat->save();

            $penanggungRawatId = GenerateHelper::getNextId('penanggung_rawat');
            $penanggungRawat = new PenanggungRawat;
            $penanggungRawat->penanggung_rawat_id = $penanggungRawatId;
            $penanggungRawat->registrasi_id = $registrasiId;
            $penanggungRawat->rawat_user_id = $rawatUserId;
            $penanggungRawat->status_batal = 0;
            $penanggungRawat->input_time = now();
            $penanggungRawat->input_user_id = auth()->id();
            $penanggungRawat->save();

            DB::commit();

            return redirect()->route('list_pelayanan_pasien.index')->with('success', 'Pendaftaran rawat jalan berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
