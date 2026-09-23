@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">List Pasien IGD</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar pasien Instalasi Gawat Darurat beserta prioritas triase.</p>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="{{ route('list_pasien_gawat_darurat.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- Filter Tanggal -->
            <div>
                <label for="tanggal_kunjungan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Kunjungan</label>
                <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" value="{{ $tanggalKunjungan }}"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>

            <!-- Filter Ruangan / Zona IGD -->
            <div>
                <label for="zona" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Zona / Ruangan IGD</label>
                <select id="zona" name="zona"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                    <option value="">Semua Zona</option>
                    @foreach (\App\Helpers\SelectOption::all()['ruang_igd'] as $opt)
                        <option value="{{ $opt['value'] }}" @selected($zona === $opt['value'])>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Triase -->
            <div>
                <label for="triase" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Triase</label>
                <select id="triase" name="triase"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                    <option value="">Semua Triase</option>
                    <option value="Merah" @selected($triase === 'Merah')>Level 1 - Resusitasi (Merah)</option>
                    <option value="Kuning" @selected($triase === 'Kuning')>Level 2 - Gawat Darurat (Kuning)</option>
                    <option value="Hijau" @selected($triase === 'Hijau')>Level 3 - Darurat Tidak Gawat (Hijau)</option>
                    <option value="Hitam" @selected($triase === 'Hitam')>Level 4 - Meninggal (Hitam)</option>
                </select>
            </div>

            <!-- Filter Dokter Jaga -->
            <div>
                <x-select_dokter name="dokter_id" id="dokter_id" :selected="$dokterPegawaiId ?? ''" label="Dokter / Petugas Jaga" placeholder="Semua Petugas" />
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 mt-2 lg:mt-6">
                <a href="{{ route('list_pasien_gawat_darurat.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Tampilkan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 1200px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Tgl/Waktu Masuk</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. RM</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Triase</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ruangan / Zona</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Dokter / Petugas Jaga</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penjamin</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @php
                    $triaseClass = [
                        'Merah' => 'bg-red-100 text-red-700 border-red-200',
                        'Kuning' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'Hijau' => 'bg-green-100 text-green-700 border-green-200',
                        'Hitam' => 'bg-slate-200 text-slate-800 border-slate-300',
                    ];
                    $triaseRow = [
                        'Merah' => 'bg-red-50/30',
                        'Kuning' => 'bg-amber-50/30',
                        'Hijau' => 'bg-green-50/20',
                        'Hitam' => 'opacity-70',
                    ];
                    $jkLabel = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
                @endphp

                @forelse ($listPasien as $row)
                    @php
                        $rowClass = $triaseRow[$row->triase] ?? '';
                        $usia = $row->tgl_lahir ? \Carbon\Carbon::parse($row->tgl_lahir)->age : '-';
                        $selesai = $row->check_out !== null || (int) $row->status_selesai === 1;
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors {{ $rowClass }}">
                        <td class="px-3 py-3 text-center text-slate-600 font-medium whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row->tgl_masuk)->translatedFormat('d M Y') }}<br>
                            <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($row->tgl_masuk)->format('H:i') }} WIB</span>
                        </td>
                        <td class="px-3 py-3 font-semibold text-blue-600 whitespace-nowrap">{{ $row->no_mr }}</td>
                        <td class="px-3 py-3 font-semibold text-slate-800">
                            {{ $row->nama_pasien }}
                            <span class="block text-[10px] text-slate-400 font-normal mt-0.5">{{ $jkLabel[$row->jenis_kelamin] ?? $row->jenis_kelamin }}, {{ $usia }} Tahun</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 rounded font-bold text-[10px] border {{ $triaseClass[$row->triase] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $row->triase ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 font-medium text-slate-700">{{ $row->lokasi_rawat ?? '-' }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $row->dokter_jaga ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-blue-100 text-blue-700">
                                {{ $row->nama_nasabah ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if ($selesai)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                    Masih Dirawat
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <a href="{{ route('dashboard_pasien.index', $row->registrasi_detail_id) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Dashboard
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-sm font-medium">Tidak ada pasien IGD pada filter ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($listPasien->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <span class="text-sm text-slate-500 text-center sm:text-left">
                Menampilkan <span class="font-medium text-slate-700">{{ $listPasien->firstItem() ?? 0 }}</span> sampai <span class="font-medium text-slate-700">{{ $listPasien->lastItem() ?? 0 }}</span> dari <span class="font-medium text-slate-700">{{ $listPasien->total() }}</span> pasien IGD
            </span>
            <div class="flex items-center justify-center sm:justify-end">
                {{ $listPasien->links() }}
            </div>
        </div>
    @endif
</div>
@endsection