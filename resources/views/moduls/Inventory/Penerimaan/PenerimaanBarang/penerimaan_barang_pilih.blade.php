@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Proses Penerimaan Barang</h1>
        <p class="text-sm text-slate-500 mt-1">Pilih pemesanan (PO) yang telah disetujui untuk dicatat penerimaannya.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('penerimaan_barang.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

@forelse ($pemesananList as $p)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
        <!-- Header Kartu -->
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-800">{{ $p->no_pemesanan }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ \Carbon\Carbon::parse($p->tanggal_pemesanan)->format('d-m-Y') }}
                    • {{ optional($p->supplier)->nama_supplier }}
                    @if ($p->bagian)
                        • {{ $p->bagian->nama_bagian }}
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('penerimaan_barang.create', ['pemesanan_id' => $p->pemesanan_id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Proses Penerimaan
                </a>
            </div>
        </div>

        <!-- Item Listing -->
        <div class="overflow-x-auto drag-scroll">
            <table class="w-full text-left" style="min-width: 640px;">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Barang</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah Pesan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-[12px] divide-y divide-slate-100">
                    @foreach ($p->details as $d)
                        <tr>
                            <td class="px-5 py-3 text-slate-700">
                                <span class="font-semibold text-slate-800">{{ $d->barang->nama_barang ?? '-' }}</span>
                                @if ($d->barang && $d->barang->kode_barang)
                                    <span class="block text-[10px] text-slate-400">{{ $d->barang->kode_barang }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center text-slate-600">{{ $d->barang->textSatuan() ?? '-' }}</td>
                            <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ rtrim(rtrim(number_format((float) $d->jumlah_pesan, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ number_format((float) $d->harga_beli, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800 tabular-nums">{{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-slate-200 bg-slate-50/60">
                    <tr>
                        <td colspan="4" class="px-5 py-2.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Total Nilai</td>
                        <td class="px-5 py-2.5 text-right text-sm font-bold text-slate-800 tabular-nums">Rp {{ number_format($p->details->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@empty
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex flex-col items-center gap-2 py-12 text-slate-400">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <p class="text-sm font-medium">Tidak ada pemesanan berstatus "Disetujui" yang siap diterima.</p>
        </div>
    </div>
@endforelse
@endsection