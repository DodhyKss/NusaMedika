@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Detail Dispense Resep</h1>
        <p class="text-sm text-slate-500 mt-1">Rincian resep dan riwayat dispense obat.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('list_pesanan_resep.cetak_tiket', $resep->peresepan_obat_id) }}" target="_blank" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak E-Tiket
        </a>
        <a href="{{ route('list_pesanan_resep.cetak_detail', $resep->peresepan_obat_id) }}" target="_blank" class="px-4 py-2 text-sm font-semibold text-white bg-slate-600 hover:bg-slate-700 rounded-lg shadow-sm shadow-slate-600/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Detail Resep
        </a>
        @php $statusResep = (int) $resep->status_resep; @endphp
        @if ($statusResep === 0 || $statusResep === 3)
            <a href="{{ route('list_pesanan_resep.dispense', $resep->peresepan_obat_id) }}" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                {{ $statusResep === 3 ? 'Lanjut Dispense' : 'Dispense' }}
            </a>
        @endif
        <a href="{{ route('list_pesanan_resep.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Info Resep -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base font-bold text-slate-800">{{ $resep->no_resep }}</h2>
                @php
                    $badge = match ((int) $resep->status_resep) {
                        0 => ['bg-amber-100 text-amber-700', 'Menunggu'],
                        1 => ['bg-emerald-100 text-emerald-700', 'Selesai'],
                        2 => ['bg-rose-100 text-rose-700', 'Batal'],
                        3 => ['bg-orange-100 text-orange-700', 'Dispense Sebagian'],
                        default => ['bg-slate-100 text-slate-600', '-'],
                    };
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badge[0] }}">{{ $badge[1] }}</span>
                @php $rawat = $resep->registrasiDetail?->registrasi?->jenis_rawat; @endphp
                @if ($rawat)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">{{ $rawat }}</span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1.5 text-xs text-slate-600">
                <p><span class="text-slate-400">Pasien:</span> <span class="font-semibold text-slate-800">{{ $resep->pasien->nama_pasien ?? '-' }} ({{ $resep->pasien->no_mr ?? '-' }})</span></p>
                <p><span class="text-slate-400">Dokter:</span> <span class="font-semibold text-slate-800">{{ $resep->dokter->nama_pegawai ?? '-' }}</span></p>
                <p><span class="text-slate-400">Tanggal Resep:</span> {{ \Carbon\Carbon::parse($resep->tanggal_resep)->format('d-m-Y H:i') }}</p>
            </div>
            @if ($resep->keterangan)
                <p class="mt-1.5 text-xs text-slate-500"><span class="font-semibold text-slate-600">Keterangan:</span> {{ $resep->keterangan }}</p>
            @endif
        </div>
    </div>
</div>

<!-- Item Resep & Status Dispense -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14v12H5z"></path></svg>
        <h3 class="text-sm font-semibold text-slate-700">Item Resep</h3>
    </div>
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 720px;">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Obat</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Jumlah</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Dispense</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Sisa</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Batch</th>
                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Waktu Dispense</th>
                    <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @foreach ($resep->details as $d)
                    @php
                        $jumlahDispense = (float) ($d->jumlah_dispense ?? 0);
                        $sisa = (float) $d->jumlah - $jumlahDispense;
                    @endphp
                    <tr>
                        <td class="px-5 py-3 text-slate-700">
                            <span class="font-semibold text-slate-800">{{ $d->barang->nama_barang ?? '-' }}</span>
                            @if ($d->s_1 || $d->s_2 || $d->aturan_pakai || $d->rute_pemberian)
                                <span class="block text-[10px] text-slate-400 mt-0.5">
                                    @if ($d->s_1 || $d->s_2) Signa: {{ $d->s_1 }} {{ $d->s_2 }} @endif
                                    @if ($d->aturan_pakai) · {{ $d->aturan_pakai }} @endif
                                    @if ($d->rute_pemberian) · {{ $d->rute_pemberian }} @endif
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-slate-700 tabular-nums">{{ rtrim(rtrim(number_format((float) $d->jumlah, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="px-3 py-3 text-center text-emerald-700 font-semibold tabular-nums">{{ rtrim(rtrim(number_format($jumlahDispense, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="px-3 py-3 text-center text-slate-600 tabular-nums">{{ $sisa > 0 ? rtrim(rtrim(number_format($sisa, 2, ',', '.'), '0'), ',') : '-' }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $d->no_batch ?? '-' }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $d->waktu_dispense ? \Carbon\Carbon::parse($d->waktu_dispense)->format('d-m-Y H:i') : '-' }}</td>
                        <td class="px-5 py-3">
                            @if ($jumlahDispense <= 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Belum</span>
                            @elseif ($sisa > 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">Sebagian</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">{{ (int) $d->flag_dispense === 1 ? 'Lengkap' : 'Selesai' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection