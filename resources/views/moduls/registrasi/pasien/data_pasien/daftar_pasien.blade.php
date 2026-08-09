@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Daftar Pasien</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data induk rekam medis pasien klinik/rumah sakit.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('daftar_pasien.create') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Pasien Baru
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="{{ route('daftar_pasien.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="pasien_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pencarian Pasien (No. RM / NIK / Nama)</label>
                <select id="pasien_id" name="pasien_id" class="select2-pasien w-full text-sm border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
                        data-url="{{ route('api.pasien.search') }}" data-placeholder="-- Ketik No. RM / NIK / Nama --" style="width: 100%">
                    <option value=""></option>
                    @if ($selectedPasien)
                        <option value="{{ $selectedPasien->pasien_id }}" selected>{{ $selectedPasien->no_mr }} - {{ $selectedPasien->nama_pasien }}</option>
                    @endif
                </select>
            </div>

            <div>
                <label for="jenis_kelamin" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Kelamin</label>
                <div class="relative">
                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">Semua</option>
                        {!! \App\Helpers\SelectOption::render('jenis_kelamin', $jenisKelamin) !!}
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-2 md:mt-0">
                <a href="{{ route('daftar_pasien.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Cari Data
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 1000px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Rekam Medis</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">NIK KTP</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jenis Kelamin</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tgl Lahir / Umur</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. HP / WA</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($pasiens as $i => $pasien)
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $pasiens->firstItem() + $i }}</td>
                    <td class="px-3 py-3 font-semibold text-blue-600">{{ $pasien->no_mr ?? '-' }}</td>
                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $pasien->nama_pasien }}</td>
                    <td class="px-3 py-3 text-slate-500 font-mono text-xs">{{ $pasien->ktp ?? '-' }}</td>
                    <td class="px-3 py-3 text-slate-600">{{ $pasien->jenis_kelamin === 'P' ? 'Perempuan' : ($pasien->jenis_kelamin === 'L' ? 'Laki-laki' : ($pasien->jenis_kelamin ?? '-')) }}</td>
                    <td class="px-3 py-3 text-slate-600">
                        @if ($pasien->tgl_lahir)
                            {{ \Carbon\Carbon::parse($pasien->tgl_lahir)->format('d-m-Y') }}<br><span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->age }} Thn</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-3 text-slate-600">{{ $pasien->no_hp ?? '-' }}</td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('daftar_pasien.edit', $pasien->pasien_id) }}" class="cursor-pointer p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors" title="Edit Pasien">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('daftar_pasien.destroy', $pasien->pasien_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pasien {{ $pasien->nama_pasien }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus Pasien">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-3 py-8 text-center text-slate-400">Belum ada data pasien.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        {{ $pasiens->withQueryString()->links('components.pagination') }}
    </div>
</div>
@endsection
