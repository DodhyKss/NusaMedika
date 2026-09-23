@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Bed Management</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola penempatan pasien dan status bed per ruang perawatan.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.bed.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Master Bed
        </a>
    </div>
</div>

<!-- Ruang Selector -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <form action="{{ route('bed_management.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <x-select-ruang-perawatan :selected="$ruangId" name="ruang_id" id="ruang_id" />
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('bed_management.index') }}" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    Tampilkan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="space-y-6">
    <!-- Bed Grid -->
    <div class="space-y-6">
        <!-- Legend -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-600">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Kosong</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Terisi</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Persiapan Pulang</span>
        </div>

        <!-- Permintaan Pindah Ruangan Masuk -->
        @if ($permintaanPindah->isNotEmpty())
            <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-200 bg-amber-50 flex items-center gap-3">
                    <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Permintaan Pindah Ruangan Masuk</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pasien meminta pindah masuk ke ruang ini, menunggu persetujuan Anda.</p>
                    </div>
                    <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-600 text-white">{{ $permintaanPindah->count() }} Permintaan</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($permintaanPindah as $pindah)
                        <div class="px-5 py-3.5 flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-5">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $pindah->nama_pasien }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">NORM {{ $pindah->no_mr }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-1 text-xs text-slate-600">
                                <div>
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Dari Ruangan</p>
                                    <p class="font-medium mt-0.5">{{ $pindah->nama_bagian_asal ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Bed Tujuan</p>
                                    <p class="font-medium mt-0.5">{{ $pindah->nama_bed ?? '-' }} (Kamar {{ $pindah->no_kamar ?? '-' }})</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Diajukan</p>
                                    <p class="font-medium mt-0.5">{{ date('d-m-Y H:i', strtotime($pindah->input_time)) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('bed_management.pindah_tolak') }}" method="POST" onsubmit="return pilihAlasanTolak(this);">
                                    @csrf
                                    <input type="hidden" name="pindah_ruangan_id" value="{{ $pindah->pindah_ruangan_id }}">
                                    <input type="hidden" name="alasan_tolak">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors whitespace-nowrap">
                                        Tolak
                                    </button>
                                </form>
                                <form action="{{ route('bed_management.pindah_approve') }}" method="POST" onsubmit="return confirm('Setujui pemindahan pasien ' + '{{ $pindah->nama_pasien }}' + ' ke ' + '{{ $pindah->nama_bed }}' + '?');">
                                    @csrf
                                    <input type="hidden" name="pindah_ruangan_id" value="{{ $pindah->pindah_ruangan_id }}">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Setujui
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Daftar Bed -->
        <div class="space-y-3">
            @forelse ($beds as $bed)
                @php
                    $terisi = (bool) $bed->pasien_id_1;
                    $pulang = $terisi && (($bed->flag_persiapan_pulang ?? 0) === 1);
                    $statusBadge = ! $terisi
                        ? ['Kosong', 'bg-emerald-100 text-emerald-600 border-emerald-200', 'bg-emerald-500']
                        : ($pulang
                            ? ['Persiapan Pulang', 'bg-blue-100 text-blue-600 border-blue-200', 'bg-blue-500']
                            : ['Terisi', 'bg-red-100 text-red-600 border-red-200', 'bg-red-500']);
                    $border = ! $terisi ? 'border-emerald-200' : ($pulang ? 'border-blue-200' : 'border-red-200');
                    $reg = $terisi ? ($registrasiMap[$bed->pasien_id_1] ?? null) : null;
                    $dpjp = $reg?->dpjp ?? '-';
                    $umur = $bed->pasien?->tgl_lahir ? \Carbon\Carbon::parse($bed->pasien->tgl_lahir)->age : '-';
                    $jk = $bed->pasien?->jenis_kelamin === 'L' ? 'Laki-laki' : ($bed->pasien?->jenis_kelamin === 'P' ? 'Perempuan' : '-');
                @endphp
                <div class="bg-white rounded-xl border {{ $border }} shadow-sm px-4 py-3 flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-5">
                    <!-- Identitas Bed -->
                    <div class="flex items-center gap-3 lg:w-64 shrink-0">
                        <span class="w-1.5 h-11 rounded-full {{ $statusBadge[2] }}"></span>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-slate-800">{{ $bed->nama_bed }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Kamar {{ $bed->no_kamar }} &middot; {{ $bed->namakelas ?? '-' }}</p>
                        </div>
                    </div>

                    @if ($terisi && $bed->pasien)
                        <!-- Info Pasien & DPJP -->
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-x-5 gap-y-2">
                            <div class="xl:col-span-2">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Pasien</p>
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $bed->pasien->nama_pasien }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $jk }}, {{ $umur }} Tahun &middot; NORM {{ $bed->pasien->no_mr ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">DPJP</p>
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $dpjp }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Masuk</p>
                                <p class="text-sm font-medium text-slate-700 whitespace-nowrap">{{ $bed->tgl_masuk ? date('d-m-Y H:i', strtotime($bed->tgl_masuk)) : '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex-1">
                            <p class="text-sm text-slate-400 italic">Bed kosong & siap terima pasien.</p>
                        </div>
                    @endif

                    <!-- Aksi -->
                    <div class="lg:w-64 shrink-0 mt-3 lg:mt-0" data-bed-id="{{ $bed->bed_id }}" data-ruang-id="{{ $bed->bagian_id }}" data-kosong-sameroom='@json($beds->whereNull('pasien_id_1')->where('bed_id', '!=', $bed->bed_id)->mapWithKeys(fn ($b) => [$b->bed_id => $b->nama_bed.' (Kamar '.$b->no_kamar.')'])->all())'>
                        <div class="grid grid-cols-2 gap-2">
                            @if ($terisi && $reg)
                                <a href="{{ route('dashboard_pasien.index', $reg->registrasi_detail_id) }}" class="col-span-2 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Dashboard EMR
                                </a>
                            @endif
                            @if ($terisi)
                                @if ($pulang)
                                    <form action="{{ route('bed_management.ready') }}" method="POST" onsubmit="return confirm('Batalkan rencana persiapan pulang pasien ini?');" class="col-span-2">
                                        @csrf
                                        <input type="hidden" name="bed_id" value="{{ $bed->bed_id }}">
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors whitespace-nowrap">
                                            Batalkan Persiapan
                                        </button>
                                    </form>
                                    <button type="button" onclick="bukaModalPulang({{ $bed->bed_id }})"
                                            class="col-span-2 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Pulang
                                    </button>
                                @else
                                    <button type="button" onclick="bukaModalPersiapan({{ $bed->bed_id }})"
                                            class="col-span-2 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        Persiapan Pulang
                                    </button>
@if (isset($pendingPindah[$bed->pasien_id_1]))
                                        <div class="col-span-2 w-full inline-flex items-center justify-center gap-1.5 px-2 py-2 text-center text-xs font-semibold leading-snug text-amber-600 bg-amber-50 border border-amber-200 rounded-lg">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Menunggu Persetujuan Pindah ke {{ $pendingPindah[$bed->pasien_id_1]->nama_bagian_tujuan ?? 'Ruang Tujuan' }}
                                        </div>
                                    @else
                                    <button type="button" onclick="bukaModalPindahBed({{ $bed->bed_id }})"
                                            class="inline-flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                        Pindah Bed
                                    </button>
                                    <button type="button" onclick="bukaModalPindahRuang({{ $bed->bed_id }})"
                                            class="inline-flex items-center justify-center gap-1.5 px-2 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"></path></svg>
                                        Pindah Ruangan
                                    </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-400">
                    <p class="text-sm font-medium">Belum ada bed terdaftar untuk ruang ini.</p>
                    <p class="text-xs mt-1">Kelola data bed pada menu Master Bed.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Waitlist -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">Waitlist</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pasien menunggu bed di ruang ini.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($waitlist as $item)
                <div class="p-4">
                    <p class="text-sm font-bold text-slate-800">{{ $item->nama_pasien }}</p>
                    <p class="text-xs text-slate-500">{{ $item->no_mr }} &middot; {{ $item->nama_nasabah ?? 'Umum' }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar: {{ date('d-m-Y H:i', strtotime($item->tgl_masuk)) }}</p>

                    <form action="{{ route('bed_management.assign') }}" method="POST" class="mt-3 flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="registrasi_detail_id" value="{{ $item->registrasi_detail_id }}">
                        <select name="bed_id" class="flex-1 text-xs border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" required>
                            <option value="">-- Pilih Bed --</option>
                            @foreach ($kosongBeds as $bedId => $namaBed)
                                <option value="{{ $bedId }}">{{ $namaBed }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            Tempatkan
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    <p class="text-xs italic">Tidak ada pasien menunggu bed.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Persiapan Pulang -->
<div id="modalPersiapanPulang" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPersiapan()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-blue-50 flex items-center gap-3">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-slate-800">Persiapan Pulang</h3>
                <p class="text-xs text-slate-500 mt-0.5">Atur rencana tanggal & jam pasien akan pulang.</p>
            </div>
            <button type="button" onclick="tutupModalPersiapan()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('bed_management.ready') }}" method="POST">
            @csrf
            <input type="hidden" name="bed_id" id="modal_bed_id">
            <div class="p-5">
                <label for="modal_tgl_pulang" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Rencana Tanggal & Jam Pulang <span class="text-red-500">*</span></label>
                <input type="datetime-local" id="modal_tgl_pulang" name="tgl_pulang" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <p class="mt-2 text-xs text-slate-400">Waktu yang dipilih menjadi rencana tanggal pulang pasien.</p>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <button type="button" onclick="tutupModalPersiapan()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Persiapan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pindah Bed -->
<div id="modalPindahBed" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPindahBed()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-indigo-50 flex items-center gap-3">
            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-slate-800">Pindah Bed</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pindahkan pasien ke bed kosong pada ruangan yang sama.</p>
            </div>
            <button type="button" onclick="tutupModalPindahBed()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('bed_management.move') }}" method="POST">
            @csrf
            <input type="hidden" name="bed_id" id="modal_pindah_bed_id">
            <div class="p-5">
                <label for="pindah_bed_tujuan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bed Tujuan <span class="text-red-500">*</span></label>
                <select id="pindah_bed_tujuan" name="target_bed_id" required
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none text-slate-700">
                    <option value="">-- Pilih Bed Kosong --</option>
                </select>
                <p class="mt-2 text-xs text-slate-400">Data pasien dipindahkan bersamaan, riwayat kedatangan dipertahankan.</p>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <button type="button" onclick="tutupModalPindahBed()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm shadow-indigo-600/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"></path></svg>
                    Pindahkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pindah Ruangan -->
<div id="modalPindahRuang" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPindahRuang()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-white flex items-center gap-3">
            <div class="p-2 bg-slate-100 text-slate-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-slate-800">Pindah Ruangan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ajukan permintaan pindah ke bed kosong di ruangan lain. Pasien tetap di bed saat ini hingga disetujui ruang tujuan.</p>
            </div>
            <button type="button" onclick="tutupModalPindahRuang()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('bed_management.pindah_request') }}" method="POST" id="formPindahRuang">
            @csrf
            <input type="hidden" name="bed_id" id="modal_pindah_ruang_bed_id">
            <div class="p-5 space-y-4">
                <div>
                    <label for="pindah_ruang_tujuan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Ruangan Tujuan <span class="text-red-500">*</span></label>
                    <select id="pindah_ruang_tujuan" name="ruang_id" required onchange="muatBedRuangTujuan()"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-slate-500/20 focus:border-slate-500 transition-all outline-none text-slate-700">
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach ($ruangs as $ruang)
                            <option value="{{ $ruang->bagian_id }}">{{ $ruang->nama_bagian }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pindah_ruang_bed" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bed Tujuan <span class="text-red-500">*</span></label>
                    <select id="pindah_ruang_bed" name="target_bed_id" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-slate-500/20 focus:border-slate-500 transition-all outline-none text-slate-700">
                        <option value="">-- Pilih Bed Terlebih Dahulu --</option>
                    </select>
                </div>
                <p class="text-xs text-slate-400">Ruang & bed pasien akan dipindahkan setelah permintaan disetujui oleh ruang tujuan.</p>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <button type="button" onclick="tutupModalPindahRuang()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm shadow-slate-800/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4a1 1 0 011-1h4a1 1 0 011 1v4"></path></svg>
                    Ajukan Permintaan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pulang -->
<div id="modalPulang" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalPulang()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-emerald-50 flex items-center gap-3">
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-slate-800">Pasien Pulang</h3>
                <p class="text-xs text-slate-500 mt-0.5">Selesaikan rawat inap pasien dengan data kepulangan.</p>
            </div>
            <button type="button" onclick="tutupModalPulang()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('bed_management.pulang') }}" method="POST">
            @csrf
            <input type="hidden" name="bed_id" id="modal_pulang_bed_id">
            <div class="p-5 space-y-4">
                <div>
                    <label for="modal_pulang_tgl" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal & Jam Pulang <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="modal_pulang_tgl" name="tgl_pulang" required
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-slate-700">
                </div>
                <div>
                    <label for="modal_pulang_alasan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Alasan Pulang</label>
                    <select id="modal_pulang_alasan" name="alasan_pulang"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-slate-700">
                        @php echo \App\Helpers\SelectOption::render('alasan_pulang', null, '-- Pilih Alasan Pulang --'); @endphp
                    </select>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <button type="button" onclick="tutupModalPulang()" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan & Pulangkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.ruangBeds = @json($kosongBedsPerRuang);

    function bukaModalPindahBed(bedId) {
        var wrap = document.querySelector('[data-bed-id="' + bedId + '"]');
        var opsi = {};
        try {
            opsi = JSON.parse(wrap.dataset.kosongSameroom || '{}');
        } catch (e) {
            opsi = {};
        }
        var select = document.getElementById('pindah_bed_tujuan');
        select.innerHTML = '<option value="">-- Pilih Bed Kosong --</option>';
        Object.keys(opsi).forEach(function (id) {
            var opt = document.createElement('option');
            opt.value = id;
            opt.textContent = opsi[id];
            select.appendChild(opt);
        });
        document.getElementById('modal_pindah_bed_id').value = bedId;
        document.getElementById('modalPindahBed').classList.remove('hidden');
    }

    function tutupModalPindahBed() {
        document.getElementById('modalPindahBed').classList.add('hidden');
    }

    function bukaModalPindahRuang(bedId) {
        var wrap = document.querySelector('[data-bed-id="' + bedId + '"]');
        var ruangSumber = parseInt(wrap.dataset.ruangId, 10);
        var selectRuang = document.getElementById('pindah_ruang_tujuan');
        var opsi = '<option value="">-- Pilih Ruangan --</option>';
        Object.keys(window.ruangBeds || {}).forEach(function (ruangId) {
            if (parseInt(ruangId, 10) === ruangSumber) {
                return;
            }
            var beds = window.ruangBeds[ruangId] || {};
            if (Object.keys(beds).length === 0) {
                return;
            }
            var label = selectRuang.querySelector('option[value="' + ruangId + '"]');
            opsi += '<option value="' + ruangId + '">' + (label ? label.textContent : ('Ruang ' + ruangId)) + '</option>';
        });
        selectRuang.innerHTML = opsi;
        document.getElementById('pindah_ruang_bed').innerHTML = '<option value="">-- Pilih Bed Terlebih Dahulu --</option>';
        document.getElementById('modal_pindah_ruang_bed_id').value = bedId;
        document.getElementById('modalPindahRuang').classList.remove('hidden');
    }

    function tutupModalPindahRuang() {
        document.getElementById('modalPindahRuang').classList.add('hidden');
    }

    function muatBedRuangTujuan() {
        var ruangId = document.getElementById('pindah_ruang_tujuan').value;
        var select = document.getElementById('pindah_ruang_bed');
        select.innerHTML = '<option value="">-- Pilih Bed Kosong --</option>';
        var beds = (window.ruangBeds || {})[ruangId] || {};
        Object.keys(beds).forEach(function (bedId) {
            var opt = document.createElement('option');
            opt.value = bedId;
            opt.textContent = beds[bedId];
            select.appendChild(opt);
        });
    }

    function pilihAlasanTolak(form) {
        var alasan = prompt('Alasan penolakan (opsional):');
        if (alasan === null) {
            return false;
        }
        form.querySelector('input[name="alasan_tolak"]').value = alasan;
        return true;
    }

    function tutupModalPulang() {
        document.getElementById('modalPulang').classList.add('hidden');
    }

    function bukaModalPulang(bedId) {
        document.getElementById('modal_pulang_bed_id').value = bedId;
        document.getElementById('modal_pulang_tgl').value = '';
        document.getElementById('modal_pulang_alasan').selectedIndex = 0;
        document.getElementById('modalPulang').classList.remove('hidden');
        document.getElementById('modal_pulang_tgl').focus();
    }

    function tutupModalPersiapan() {
        document.getElementById('modalPersiapanPulang').classList.add('hidden');
    }

    function bukaModalPersiapan(bedId) {
        document.getElementById('modal_bed_id').value = bedId;
        document.getElementById('modal_tgl_pulang').value = '';
        document.getElementById('modalPersiapanPulang').classList.remove('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            tutupModalPersiapan();
            tutupModalPindahBed();
            tutupModalPindahRuang();
            tutupModalPulang();
        }
    });
</script>
@endsection