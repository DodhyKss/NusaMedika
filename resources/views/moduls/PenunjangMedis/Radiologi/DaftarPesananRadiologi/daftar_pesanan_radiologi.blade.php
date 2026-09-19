@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Daftar Pesanan Radiologi</h1>
        <p class="text-sm text-slate-500 mt-1">Terima, entri hasil, dan selesaikan order radiologi dari EMR berdasarkan bagian tempat Anda bertugas.</p>
    </div>
</div>

<!-- Pilih Bagian -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('daftar_pesanan_radiologi.pilih_bagian') }}" method="POST" class="flex flex-col sm:flex-row sm:items-end gap-3">
        @csrf
        <div class="flex-1">
            <label for="bagian_tujuan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bagian Radiologi Aktif</label>
            <select id="bagian_tujuan_id" name="bagian_tujuan_id" required
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Bagian --</option>
                @foreach ($bagianList as $b)
                    <option value="{{ $b->bagian_id }}" @selected($bagianTujuanId === (int) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Pilih Bagian
        </button>
    </form>
    @if ($bagianTujuanId)
        <p class="mt-2 text-xs text-emerald-600 font-semibold">
            Bagian aktif: {{ $bagianList->firstWhere('bagian_id', $bagianTujuanId)?->nama_bagian ?? '-' }}
        </p>
    @else
        <p class="mt-2 text-xs text-slate-400">Pilih bagian radiologi terlebih dahulu sebelum mengelola pesanan.</p>
    @endif
</div>

<!-- Filter Pencarian -->
<form method="GET" action="{{ route('daftar_pesanan_radiologi.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tgl. Order Awal</label>
            <input type="date" name="tgl_awal" value="{{ $tglAwal }}"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tgl. Order Akhir</label>
            <input type="date" name="tgl_akhir" value="{{ $tglAkhir }}"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Rawat</label>
            <select name="jenis_rawat"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Semua Jenis Rawat --</option>
                <option value="RJ" @selected($jenisRawat === 'RJ')>Rawat Jalan (RJ)</option>
                <option value="RI" @selected($jenisRawat === 'RI')>Rawat Inap (RI)</option>
                <option value="IGD" @selected($jenisRawat === 'IGD')>IGD</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Order</label>
            <input type="text" name="no_order" value="{{ $noOrder }}" placeholder="Ketik No. Order..."
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
            <select name="status"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Semua Status --</option>
                <option value="0" @selected($status !== null && (int) $status === 0)>Menunggu</option>
                <option value="1" @selected($status !== null && (int) $status === 1)>Diproses</option>
                <option value="2" @selected($status !== null && (int) $status === 2)>Selesai</option>
                <option value="3" @selected($status !== null && (int) $status === 3)>Batal</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pasien</label>
            <x-select_pasien
                id="pasien_id"
                name="pasien_id"
                placeholder="-- Pilih Pasien --"
                :selected="request('pasien_id')"
            ></x-select_pasien>
        </div>
    </div>
    <div class="flex items-center gap-2 mt-4">
        <a href="{{ route('daftar_pesanan_radiologi.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
            Reset
        </a>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Cari
        </button>
    </div>
</form>

@if($orderList !== null)
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 960px;">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Order</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tanggal Order</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jenis Rawat</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Peminta</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tindakan</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] divide-y divide-slate-100">
                @forelse ($orderList as $o)
                    @php
                        $rawat = $o->registrasiDetail?->registrasi?->jenis_rawat;
                        $itemList = $o->details->pluck('nama_tindakan')->implode(', ');
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-semibold text-slate-800">{{ $o->no_order }}</span>
                        </td>
                        <td class="px-3 py-3 text-slate-600 tabular-nums">{{ \Carbon\Carbon::parse($o->tanggal_order)->format('d-m-Y H:i') }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $o->pasien?->nama_pasien ?? '-' }} <span class="block text-[10px] text-slate-400">{{ $o->pasien?->no_mr ?? '' }}</span></td>
                        <td class="px-3 py-3">
                            @if ($rawat)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">{{ $rawat }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $o->dokter ? 'Dr. '.$o->dokter->nama_pegawai : '-' }}</td>
                        <td class="px-3 py-3 text-slate-600 max-w-[220px] truncate" title="{{ $itemList }}">{{ $itemList ?: '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $o->status_class }}">{{ $o->status_label }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ((int) $o->status_order === 0)
                                    <form action="{{ route('order_radiologi.batal', $o->order_radiologi_id) }}" method="POST" onsubmit="return confirm('Yakin membatalkan order ini?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                                            Batal
                                        </button>
                                    </form>
                                @endif
                                @if ((int) $o->status_order === 1)
                                    <form action="{{ route('order_radiologi.selesai', $o->order_radiologi_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all">
                                            Selesai
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('order_radiologi.cetak', $o->order_radiologi_id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                                    Cetak
                                </a>
                                <a href="{{ route('daftar_pesanan_radiologi.detail', $o->order_radiologi_id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-slate-600 hover:bg-slate-700 rounded-lg transition-colors">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-400">
                            Tidak ada order radiologi ditemukan untuk filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $orderList->links('components.pagination') }}
</div>
@else
<div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="flex flex-col items-center gap-3 py-16 text-slate-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <p class="text-sm font-medium">Gunakan filter pencarian di atas untuk menampilkan daftar order radiologi.</p>
    </div>
</div>
@endif
@endsection