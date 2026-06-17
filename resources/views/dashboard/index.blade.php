@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Selamat Datang, {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}</h1>
    <p class="text-sm text-slate-500 mt-1">Pantau seluruh aktivitas dan operasional rumah sakit dari dashboard terpusat.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <!-- Stat: Total Pasien -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pasien</p>
            <div class="p-2 bg-blue-50 rounded-lg">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">1,234</p>
        <div class="flex items-center gap-1 mt-1">
            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            <span class="text-xs font-medium text-emerald-600">12%</span>
            <span class="text-xs text-slate-400 ml-1">dari bulan lalu</span>
        </div>
    </div>
    
    <!-- Stat: Selesai Berobat -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai Berobat</p>
            <div class="p-2 bg-emerald-50 rounded-lg">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">45</p>
        <div class="flex items-center gap-1 mt-1">
            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
            <span class="text-xs font-medium text-emerald-600">8%</span>
            <span class="text-xs text-slate-400 ml-1">hari ini</span>
        </div>
    </div>

    <!-- Stat: Antrian Aktif -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Antrian Aktif</p>
            <div class="p-2 bg-amber-50 rounded-lg">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">89</p>
        <p class="text-xs text-slate-400 mt-1">Pasien menunggu hari ini</p>
    </div>
</div>

<!-- System Status Bar -->
<div class="flex items-center gap-2 px-4 py-2.5 mb-6 bg-emerald-50 border border-emerald-200 rounded-lg">
    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
    <span class="text-xs font-semibold text-emerald-700">Status Sistem: Operational</span>
    <span class="text-xs text-emerald-500 ml-auto">Terakhir diperbarui: {{ now()->format('H:i') }} WIB</span>
</div>

<!-- Patient Queue Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Antrian Pasien Terbaru</h2>
        <button class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">Lihat Semua &rarr;</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Rekam Medis</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Poli / Tujuan</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Waktu Daftar</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="text-[13px] divide-y divide-slate-100">
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-700">RM-00123</td>
                    <td class="px-5 py-3.5 text-slate-800">Ahmad Sudirman</td>
                    <td class="px-5 py-3.5 text-slate-500">Poli Umum</td>
                    <td class="px-5 py-3.5 text-slate-500">08:15 WIB</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Menunggu
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-700">RM-00124</td>
                    <td class="px-5 py-3.5 text-slate-800">Siti Aminah</td>
                    <td class="px-5 py-3.5 text-slate-500">Poli Gigi</td>
                    <td class="px-5 py-3.5 text-slate-500">08:30 WIB</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Diperiksa
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-slate-700">RM-00120</td>
                    <td class="px-5 py-3.5 text-slate-800">Budi Santoso</td>
                    <td class="px-5 py-3.5 text-slate-500">Poli Jantung</td>
                    <td class="px-5 py-3.5 text-slate-500">07:45 WIB</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Selesai
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
