@extends('layouts.app')

@section('content')
@php
    $registrasi = $detail->registrasi;
    $pasien = $registrasi?->pasien;
@endphp

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Detail Pasien Rehabilitasi Medik</h1>
        <p class="text-sm text-slate-500 mt-1">Detail konsultasi pasien dan riwayat Konsultasi Rehabilitasi Medik.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('daftar_pasien_rehabilitasi_medik.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Info Pasien -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
        <h2 class="text-base font-bold text-slate-800">{{ $pasien->nama_pasien ?? '-' }} <span class="text-sm font-semibold text-slate-400">({{ $pasien->no_mr ?? '-' }})</span></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1.5 text-xs text-slate-600">
            <p><span class="text-slate-400">Tgl. Lahir:</span> {{ $pasien->tgl_lahir ? \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') : '-' }}</p>
            <p><span class="text-slate-400">Jenis Kelamin:</span> {{ $pasien->jenis_kelamin ?? '-' }}</p>
            <p><span class="text-slate-400">Jenis Rawat:</span> {{ $registrasi->jenis_rawat ?? '-' }}</p>
            <p><span class="text-slate-400">Tgl. Masuk:</span> {{ $registrasi->tgl_masuk ? \Carbon\Carbon::parse($registrasi->tgl_masuk)->format('d-m-Y H:i') : '-' }}</p>
            <p><span class="text-slate-400">Bagian Konsul:</span> {{ $detail->bagian?->nama_bagian ?? '-' }}</p>
            <p><span class="text-slate-400">Dokter Penanggung:</span>
                @if ($registrasi && $registrasi->penanggungRawat->isNotEmpty())
                    {{ $registrasi->penanggungRawat->first()->user?->nama_pegawai ?? '-' }}
                @else
                    -
                @endif
            </p>
        </div>
    </div>
</div>

<!-- Riwayat Konsultasi -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <h3 class="text-sm font-semibold text-slate-700">Riwayat Konsultasi</h3>
    </div>
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 880px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Dokter Pencatat</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jenis Konsultasi</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bagian / Dokter Tujuan</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Indikasi</th>
                    <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] divide-y divide-slate-100">
                @forelse ($konsultasiList as $k)
                    @php
                        $kd = \App\Helpers\EmrHelper::emrDetailByVariabel((int) $k->emr_id);
                        $namaBagianTujuan = ($kd['jenis_konsultasi'] ?? '') === 'REHABILITASI_MEDIK'
                            ? \App\Models\Bagian::find($kd['bagian_rehab_id'] ?? null)?->nama_bagian
                            : \App\Models\Bagian::find($kd['bagian_tujuan_id'] ?? null)?->nama_bagian;
                        $namaDokterTujuan = \App\Models\Pegawai::find($kd['dokter_tujuan_id'] ?? null)?->nama_pegawai;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3 text-slate-600 tabular-nums">{{ \Carbon\Carbon::parse($k->tgl_jam)->format('d-m-Y H:i') }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $k->nama_pegawai ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-700">{{ $kd['jenis_konsultasi'] ?? '-' }}</span>
                        </td>
                        <td class="px-3 py-3 text-slate-600">
                            {{ $namaBagianTujuan ?? '-' }}
                            @if ($namaDokterTujuan)
                                <span class="block text-[10px] text-slate-400">{{ $namaDokterTujuan }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-600 max-w-[260px] truncate" title="{{ $kd['indikasi_konsultasi'] ?? '' }}">{{ $kd['indikasi_konsultasi'] ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($formKonsultasi)
                                    <a href="{{ route('emr.konsultasi.print', $k->emr_id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                            Belum ada konsultasi tercatat untuk pasien ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection