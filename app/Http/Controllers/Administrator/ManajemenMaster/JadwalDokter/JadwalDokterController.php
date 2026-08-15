<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\JadwalDokter;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JadwalDokterController extends Controller
{
    public const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = JadwalDokter::aktif()->with([
            'pegawai' => fn ($q) => $q->where(function ($sq) {
                $sq->whereNull('status_batal')->orWhere('status_batal', 0);
            }),
            'bagian' => fn ($q) => $q->where(function ($sq) {
                $sq->whereNull('status_batal')->orWhere('status_batal', 0);
            }),
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pegawai', function ($q2) use ($search) {
                    $q2->where('nama_pegawai', 'ilike', "%{$search}%");
                })->orWhereHas('bagian', function ($q2) use ($search) {
                    $q2->where('nama_bagian', 'ilike', "%{$search}%");
                })->orWhere('ruang_praktek', 'ilike', "%{$search}%");
            });
        }

        $jadwals = $query->orderBy('hari')->orderBy('waktu_mulai')->paginate(10)->withQueryString();
        $hariMap = self::HARI;

        return view('moduls.administrator.manajemen_master.jadwal_dokter.index', compact('jadwals', 'search', 'hariMap'));
    }

    public function create()
    {
        return view('moduls.administrator.manajemen_master.jadwal_dokter.create', ['hariMap' => self::HARI]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($this->cekJadwalDuplikat($data['pegawai_id'], $data['hari'], $data['bagian_id'])) {
            return back()->with('error', 'Dokter sudah memiliki jadwal pada hari yang sama di poliklinik yang sama.')->withInput();
        }

        DB::beginTransaction();
        try {
            $jadwal = new JadwalDokter;
            $jadwal->jadwal_dokter_id = GenerateHelper::getNextId('jadwal_dokter');
            $jadwal->pegawai_id = $data['pegawai_id'];
            $jadwal->hari = $data['hari'];
            $jadwal->waktu_mulai = $data['waktu_mulai'];
            $jadwal->waktu_selesai = $data['waktu_selesai'];
            $jadwal->kuota = $data['kuota'];
            $jadwal->bagian_id = $data['bagian_id'];
            $jadwal->ruang_praktek = $data['ruang_praktek'];
            $jadwal->input_time = now();
            $jadwal->input_user_id = Auth::id();
            $jadwal->status_batal = 0;
            $jadwal->save();

            DB::commit();

            return redirect()->route('admin.jadwal_dokter.index')->with('success', 'Jadwal dokter berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan jadwal dokter: '.$e->getMessage())->withInput();
        }
    }

    public function edit($jadwal)
    {
        $jadwal = JadwalDokter::findOrFail($jadwal);

        return view('moduls.administrator.manajemen_master.jadwal_dokter.edit', ['jadwal' => $jadwal, 'hariMap' => self::HARI]);
    }

    public function update(Request $request, $jadwal)
    {
        $data = $this->validated($request);

        $jadwal = JadwalDokter::findOrFail($jadwal);

        if ($this->cekJadwalDuplikat($data['pegawai_id'], $data['hari'], $data['bagian_id'], $jadwal->jadwal_dokter_id)) {
            return back()->with('error', 'Dokter sudah memiliki jadwal pada hari yang sama di poliklinik yang sama.')->withInput();
        }

        DB::beginTransaction();
        try {
            $jadwal->pegawai_id = $data['pegawai_id'];
            $jadwal->hari = $data['hari'];
            $jadwal->waktu_mulai = $data['waktu_mulai'];
            $jadwal->waktu_selesai = $data['waktu_selesai'];
            $jadwal->kuota = $data['kuota'];
            $jadwal->bagian_id = $data['bagian_id'];
            $jadwal->ruang_praktek = $data['ruang_praktek'];
            $jadwal->mod_time = now();
            $jadwal->mod_user_id = Auth::id();
            $jadwal->save();

            DB::commit();

            return redirect()->route('admin.jadwal_dokter.index')->with('success', 'Jadwal dokter berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui jadwal dokter: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($jadwal)
    {
        DB::beginTransaction();
        try {
            $jadwal = JadwalDokter::findOrFail($jadwal);
            $jadwal->status_batal = 1;
            $jadwal->mod_time = now();
            $jadwal->mod_user_id = Auth::id();
            $jadwal->save();

            DB::commit();

            return redirect()->route('admin.jadwal_dokter.index')->with('success', 'Jadwal dokter berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus jadwal dokter: '.$e->getMessage());
        }
    }

    private function cekJadwalDuplikat($pegawaiId, $hari, $bagianId, $ignoreId = null)
    {
        return JadwalDokter::aktif()
            ->where('pegawai_id', $pegawaiId)
            ->where('hari', $hari)
            ->where('bagian_id', $bagianId)
            ->when($ignoreId, fn ($q) => $q->where('jadwal_dokter_id', '!=', $ignoreId))
            ->exists();
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'pegawai_id' => 'required|integer|exists:pegawai,pegawai_id',
            'hari' => 'required|integer|between:1,7',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'kuota' => 'required|integer|min:0',
            'bagian_id' => 'required|integer|exists:bagian,bagian_id',
            'ruang_praktek' => 'nullable|string|max:10',
        ], [
            'waktu_mulai.date_format' => 'Jam mulai harus berformat 24 jam (HH:MM).',
            'waktu_selesai.date_format' => 'Jam selesai harus berformat 24 jam (HH:MM).',
            'waktu_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'kuota.required' => 'Kuota pasien wajib diisi.',
        ]);

        return array_merge([
            'ruang_praktek' => null,
        ], $data);
    }
}
