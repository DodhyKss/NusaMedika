@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Setujui Pesanan</h1>
        <p class="text-sm text-slate-500 mt-1">Review dan setujui / tolak pemesanan barang sebelum diterima.</p>
    </div>
</div>

@forelse ($pemesananList as $p)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
        <!-- Header Kartu -->
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-800">{{ $p->no_pemesanan }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">{{ $p->status_label }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ \Carbon\Carbon::parse($p->tanggal_pemesanan)->format('d-m-Y') }}
                        • {{ optional($p->supplier)->nama_supplier }}
                        @if ($p->bagian)
                            • {{ $p->bagian->nama_bagian }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form action="{{ route('pemesanan.tolak', $p->pemesanan_id) }}" method="POST" onsubmit="return confirm('Yakin menolak pemesanan ini?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        Tolak
                    </button>
                </form>
                <form action="{{ route('pemesanan.setujui', $p->pemesanan_id) }}" method="POST" onsubmit="return confirm('Setujui pemesanan ini?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Setujui
                    </button>
                </form>
            </div>
        </div>

        <!-- Item Detail -->
        <div class="overflow-x-auto drag-scroll">
            <table class="w-full text-left" style="min-width: 640px;">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Barang</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah Pesan</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Jual</th>
                        <th class="px-5 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-[12px] divide-y divide-slate-100">
                    @forelse ($p->details as $d)
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
                            <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ $d->harga_jual !== null ? number_format((float) $d->harga_jual, 0, ',', '.') : '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800 tabular-nums">{{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-xs text-slate-400">Tidak ada item.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t border-slate-200 bg-slate-50/60">
                    <tr>
                        <td colspan="4" class="px-5 py-2.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Total Nilai</td>
                        <td class="px-5 py-2.5 text-right text-sm font-bold text-slate-800 tabular-nums">Rp {{ number_format($p->details->sum('subtotal'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($p->keterangan)
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/40 text-xs text-slate-500">
                <span class="font-semibold text-slate-600">Keterangan:</span> {{ $p->keterangan }}
            </div>
        @endif
    </div>
@empty
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="flex flex-col items-center gap-2 py-12 text-slate-400">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <p class="text-sm font-medium">Tidak ada pemesanan yang menunggu persetujuan.</p>
        </div>
    </div>
@endforelse

<div class="mt-4">
    {{ $pemesananList->links('components.pagination') }}
</div>
@endsection