@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">List Pasien Dokter</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar antrean pasien rawat jalan berdasarkan jadwal poliklinik.</p>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="#" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Filter Tanggal Kunjungan -->
            <div>
                <label for="tanggal_kunjungan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Kunjungan</label>
                <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" value="{{ date('Y-m-d') }}"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>

            <!-- Filter Pilih Poli -->
            <div>
                <label for="poliklinik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Poliklinik</label>
                <div class="relative">
                    <select id="poliklinik" name="poliklinik" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">Semua Poliklinik</option>
                        <option value="Poli Umum">Poliklinik Umum</option>
                        <option value="Poli Gigi">Poliklinik Gigi</option>
                        <option value="Poli Anak">Poliklinik Anak</option>
                        <option value="Poli Kandungan">Poliklinik Kandungan (Obgyn)</option>
                        <option value="Poli Penyakit Dalam">Poliklinik Penyakit Dalam</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 mt-2 md:mt-0">
                <a href="#" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
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
        <table class="w-full text-left" style="min-width: 1000px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No. Antrean</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Waktu Daftar</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. RM</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Poliklinik</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penjamin</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                <!-- Baris 1 (Menunggu) -->
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                            1
                        </span>
                    </td>
                    <td class="px-3 py-3 text-slate-500">08:15 WIB</td>
                    <td class="px-3 py-3 font-semibold text-blue-600">RM-001234</td>
                    <td class="px-3 py-3 font-semibold text-slate-800">
                        Budi Santoso
                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Laki-laki, 45 Tahun</span>
                    </td>
                    <td class="px-3 py-3 font-medium text-slate-700">Poliklinik Umum</td>
                    <td class="px-3 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            BPJS Kesehatan
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Menunggu
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <a href="#" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Periksa
                        </a>
                    </td>
                </tr>

                <!-- Baris 2 (Sedang Diperiksa) -->
                <tr class="hover:bg-blue-50/40 transition-colors bg-blue-50/30">
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                            2
                        </span>
                    </td>
                    <td class="px-3 py-3 text-slate-500">08:20 WIB</td>
                    <td class="px-3 py-3 font-semibold text-blue-600">RM-001235</td>
                    <td class="px-3 py-3 font-semibold text-slate-800">
                        Siti Aminah
                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Perempuan, 32 Tahun</span>
                    </td>
                    <td class="px-3 py-3 font-medium text-slate-700">Poliklinik Penyakit Dalam</td>
                    <td class="px-3 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-blue-100 text-blue-700">
                            Umum / Mandiri
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            Sedang Diperiksa
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <a href="#" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors border border-blue-200">
                            Lanjutkan
                        </a>
                    </td>
                </tr>

                <!-- Baris 3 (Selesai) -->
                <tr class="hover:bg-blue-50/40 transition-colors opacity-70">
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-400 font-bold text-xs border border-slate-200">
                            3
                        </span>
                    </td>
                    <td class="px-3 py-3 text-slate-400">08:05 WIB</td>
                    <td class="px-3 py-3 font-semibold text-slate-500">RM-001230</td>
                    <td class="px-3 py-3 font-semibold text-slate-500">
                        Andi Saputra
                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Laki-laki, 28 Tahun</span>
                    </td>
                    <td class="px-3 py-3 font-medium text-slate-500">Poliklinik Umum</td>
                    <td class="px-3 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                            Asuransi Swasta
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Selesai
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <a href="#" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat RM
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <span class="text-sm text-slate-500 text-center sm:text-left">Menampilkan <span class="font-medium text-slate-700">1</span> sampai <span class="font-medium text-slate-700">3</span> dari <span class="font-medium text-slate-700">12</span> antrean</span>
        <div class="flex items-center justify-center sm:justify-end gap-1">
            <button class="px-3 py-1.5 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-md cursor-not-allowed">
                Sebelumnya
            </button>
            <button class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-md">
                1
            </button>
            <button class="px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50">
                2
            </button>
            <button class="px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-50">
                Selanjutnya
            </button>
        </div>
    </div>
</div>
@endsection
