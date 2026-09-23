@extends('layouts.app')

@section('content')
@php
    $statusOrder = (int) $order->status_order;
    $bisaUbah = in_array($statusOrder, [0, 1], true); // Menunggu / Diproses
@endphp

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Detail Order Laboratorium</h1>
        <p class="text-sm text-slate-500 mt-1">Terima order dan entri hasil pemeriksaan laboratorium.</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if (in_array($statusOrder, [0, 1], true))
            @if ($statusOrder === 0)
                <form action="{{ route('order_laboratorium.terima', $order->order_laboratorium_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm shadow-sky-600/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Terima
                    </button>
                </form>
            @endif
            <a href="{{ route('order_laboratorium.cetak', $order->order_laboratorium_id) }}" target="_blank" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak LHU
            </a>
            <form action="{{ route('order_laboratorium.batal', $order->order_laboratorium_id) }}" method="POST" data-confirm-message="Yakin membatalkan order ini?" data-confirm-danger="true" data-confirm-title="Batalkan">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Batal
                </button>
            </form>
        @endif
        <a href="{{ route('daftar_pesanan_laboratorium.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Info Order -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base font-bold text-slate-800">{{ $order->no_order }}</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status_class }}">{{ $order->status_label }}</span>
                @if ($order->prioritas)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->prioritas === 'CITO' ? 'bg-rose-100 text-rose-700' : ($order->prioritas === 'SEGERA' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">{{ $order->prioritas }}</span>
                @endif
                @php $rawat = $order->registrasiDetail?->registrasi?->jenis_rawat; @endphp
                @if ($rawat)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">{{ $rawat }}</span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1 mt-1.5 text-xs text-slate-600">
                <p><span class="text-slate-400">Pasien:</span> <span class="font-semibold text-slate-800">{{ $order->pasien?->nama_pasien ?? '-' }} ({{ $order->pasien?->no_mr ?? '-' }})</span></p>
                <p><span class="text-slate-400">Dokter Peminta:</span> <span class="font-semibold text-slate-800">{{ $order->dokter?->nama_pegawai ?? '-' }}</span></p>
                <p><span class="text-slate-400">Bagian Asal:</span> {{ $order->bagianAsal?->nama_bagian ?? '-' }}</p>
                <p><span class="text-slate-400">Bagian Tujuan:</span> {{ $order->bagianTujuan?->nama_bagian ?? '-' }}</p>
                <p><span class="text-slate-400">Tanggal Order:</span> {{ \Carbon\Carbon::parse($order->tanggal_order)->format('d-m-Y H:i') }}</p>
                <p><span class="text-slate-400">Tanggal Terima:</span> {{ $order->tanggal_terima ? \Carbon\Carbon::parse($order->tanggal_terima)->format('d-m-Y H:i') : '-' }}</p>
            </div>
            @if ($order->keterangan)
                <p class="mt-1.5 text-xs text-slate-500"><span class="font-semibold text-slate-600">Keterangan:</span> {{ $order->keterangan }}</p>
            @endif
        </div>
    </div>
</div>

<!-- Entri Hasil -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="flex items-center gap-2 px-5 py-3 border-b border-slate-100">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <h3 class="text-sm font-semibold text-slate-700">Hasil Pemeriksaan</h3>
        @if (! $bisaUbah)
            <span class="text-[10px] text-slate-400 font-medium">(read-only)</span>
        @endif
    </div>

    <form action="{{ route('order_laboratorium.simpan_hasil', $order->order_laboratorium_id) }}" method="POST" id="formHasil">
        @csrf
        <div class="overflow-x-auto drag-scroll">
            <table class="w-full text-left" style="min-width: 880px;">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pemeriksaan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nilai Normal</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hasil</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Abnormal</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] divide-y divide-slate-100">
                    @forelse ($order->details as $d)
                        <tr @if (!empty($d->hasil) && (int) $d->flag_abnormal === 1) class="bg-red-50/40" @endif>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $d->nama_tindakan }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $d->satuan_hasil ?? '-' }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $d->nilai_normal ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <input type="text" name="hasil[{{ $d->order_laboratorium_detail_id }}]" value="{{ old('hasil.'.$d->order_laboratorium_detail_id, $d->hasil) }}"
                                       placeholder="Isi hasil..." {{ $bisaUbah ? '' : 'readonly' }}
                                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 {{ $bisaUbah ? 'bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500' : 'bg-slate-50 text-slate-500 cursor-not-allowed' }} outline-none transition-all">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" name="flag_abnormal[{{ $d->order_laboratorium_detail_id }}]" value="1"
                                       @checked((int) $d->flag_abnormal === 1) {{ $bisaUbah ? '' : 'disabled' }}
                                       class="w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-red-500/20 align-middle">
                            </td>
                            <td class="px-3 py-3 text-slate-600">{{ $d->petugas?->nama_pegawai ?? '-' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ((int) $d->status === 1)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ (int) $d->flag_abnormal === 1 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ (int) $d->flag_abnormal === 1 ? 'ABNORMAL' : 'SELESAI' }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                                Tidak ada item pemeriksaan pada order ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        </form>

        @if ($bisaUbah)
            <div class="px-5 py-4 border-t border-slate-100 flex items-center gap-2 justify-end">
                <button type="submit" form="formHasil" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Hasil
                </button>
                <form action="{{ route('order_laboratorium.selesai', $order->order_laboratorium_id) }}" method="POST" data-confirm-message="Finalisasi order ini? Hasil yang belum diisi akan ditandai kosong." data-confirm-danger="false">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Selesai
                    </button>
                </form>
            </div>
        @endif
</div>

@if ($statusOrder === 2 && $order->petugasPelaksana)
    <p class="text-xs text-slate-500">Diproses oleh: <span class="font-semibold text-slate-700">{{ $order->petugasPelaksana->nama_pegawai }}</span> · Selesai {{ $order->tanggal_hasil ? \Carbon\Carbon::parse($order->tanggal_hasil)->format('d-m-Y H:i') : '' }}</p>
@endif
@endsection