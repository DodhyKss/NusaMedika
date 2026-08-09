<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\DaftarRajal;

use App\Http\Controllers\Controller;
use App\Models\JadwalDokter;

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

        $dokters = $jadwals->map(fn ($jd) => $jd->pegawai)
            ->filter()
            ->unique('pegawai_id')
            ->sortBy('nama_pegawai')
            ->values();

        $poliklinikDokter = $jadwals->groupBy('pegawai_id')
            ->map(fn ($group) => $group->pluck('bagian_id')->filter()->unique()->values()->all())
            ->all();

        return view('moduls.registrasi.pendaftaran.daftar_rj.daftar_rajal', compact('polikliniks', 'dokters', 'poliklinikDokter'));
    }
}
