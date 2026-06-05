@extends('layouts.app')

@section('content')
<!-- Welcome Banner -->
<div class="mb-8 relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 to-blue-500 shadow-lg text-white">
    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
    <div class="absolute bottom-0 left-20 w-40 h-40 bg-blue-300 opacity-20 rounded-full blur-3xl"></div>
    
    <div class="relative z-10 px-8 py-10 sm:px-10 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Selamat Datang, {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}!</h1>
            <p class="mt-2 text-blue-100 max-w-xl text-sm leading-relaxed">
                Anda memiliki <strong class="text-white">89</strong> antrian pasien hari ini. Pantau seluruh aktivitas dan operasional rumah sakit dengan mudah melalui dashboard terpusat ini.
            </p>
        </div>
        <div class="mt-6 sm:mt-0 flex-shrink-0">
            <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-md border border-white/30 shadow-sm">
                <span class="w-2 h-2 mr-2 bg-green-400 rounded-full animate-pulse shadow-[0_0_8px_rgba(74,222,128,1)]"></span>
                Status Sistem: Optimal
            </span>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Stat 1: Blue -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-white rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
        <div class="flex items-center relative z-10">
            <div class="p-3.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white mr-5 shadow-lg shadow-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Pasien</p>
                <div class="flex items-baseline mt-1">
                    <p class="text-3xl font-black text-slate-800">1,234</p>
                    <span class="ml-2 text-xs font-semibold text-green-500 flex items-center">
                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        12%
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stat 2: Green -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-green-50 to-white rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
        <div class="flex items-center relative z-10">
            <div class="p-3.5 bg-gradient-to-br from-green-400 to-green-600 rounded-xl text-white mr-5 shadow-lg shadow-green-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Selesai Berobat</p>
                <div class="flex items-baseline mt-1">
                    <p class="text-3xl font-black text-slate-800">45</p>
                    <span class="ml-2 text-xs font-semibold text-green-500 flex items-center">
                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        8%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Yellow -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-yellow-50 to-white rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
        <div class="flex items-center relative z-10">
            <div class="p-3.5 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl text-white mr-5 shadow-lg shadow-yellow-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Antrian Aktif</p>
                <div class="flex items-baseline mt-1">
                    <p class="text-3xl font-black text-slate-800">89</p>
                    <span class="ml-2 text-xs font-semibold text-slate-400 flex items-center">
                        Hari Ini
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Area -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-white">
        <h2 class="text-lg font-bold text-slate-800">Antrian Pasien Terbaru</h2>
        <button class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">Lihat Semua &rarr;</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-4">No. Rekam Medis</th>
                    <th class="px-6 py-4">Nama Pasien</th>
                    <th class="px-6 py-4">Poli / Tujuan</th>
                    <th class="px-6 py-4">Waktu Daftar</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4 font-semibold text-slate-700">RM-00123</td>
                    <td class="px-6 py-4 text-slate-800">Ahmad Sudirman</td>
                    <td class="px-6 py-4 text-slate-500">Poli Umum</td>
                    <td class="px-6 py-4 text-slate-500">08:15 WIB</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                            Menunggu
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4 font-semibold text-slate-700">RM-00124</td>
                    <td class="px-6 py-4 text-slate-800">Siti Aminah</td>
                    <td class="px-6 py-4 text-slate-500">Poli Gigi</td>
                    <td class="px-6 py-4 text-slate-500">08:30 WIB</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                            Diperiksa
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4 font-semibold text-slate-700">RM-00120</td>
                    <td class="px-6 py-4 text-slate-800">Budi Santoso</td>
                    <td class="px-6 py-4 text-slate-500">Poli Jantung</td>
                    <td class="px-6 py-4 text-slate-500">07:45 WIB</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            Selesai
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
