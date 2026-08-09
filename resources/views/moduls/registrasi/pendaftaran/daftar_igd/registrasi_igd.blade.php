@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Registrasi IGD</h1>
        <p class="text-sm text-slate-500 mt-1">Daftarkan pasien ke Instalasi Gawat Darurat dengan cepat.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('list_pelayanan_pasien.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            List Pelayanan
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <form action="#" method="POST" id="formRegistrasiIGD">
        @csrf
        
        <!-- Section 1: Data Pasien -->
        <div class="px-6 py-4 border-b border-slate-200 bg-red-50 flex items-center gap-3">
            <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div class="flex-1 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-red-800">1. Identitas Pasien (IGD)</h2>
                    <p class="text-xs text-red-600/80 mt-0.5">Cari pasien atau daftarkan sebagai pasien darurat (Tanpa Identitas).</p>
                </div>
                <!-- Tombol Pasien Baru (Cepat) -->
                <button type="button" class="text-xs font-semibold bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded shadow-sm transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Pasien Darurat (Mr. X)
                </button>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <label for="pasien_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cari Pasien Terdaftar <span class="text-red-500">*</span></label>
                    <select id="pasien_id" name="pasien_id" class="select2-pasien w-full text-sm border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700" style="width: 100%">
                    <option value=""></option>
                        <option value="">-- Ketik Nama atau No. RM Pasien --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Layanan & Triase -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">2. Triase & Petugas IGD</h2>
                <p class="text-xs text-slate-500 mt-0.5">Penilaian awal kondisi kedaruratan dan dokter jaga.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Tanggal & Waktu Kedatangan -->
                <div>
                    <label for="waktu_kedatangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Waktu Kedatangan <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="waktu_kedatangan" name="waktu_kedatangan" value="{{ date('Y-m-d\TH:i') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                </div>

                <!-- Kategori Triase -->
                <div>
                    <label for="triase" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kategori Triase <span class="text-red-500">*</span></label>
                    <select id="triase" name="triase" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none font-medium">
                        <option value="">-- Pilih Triase --</option>
                        {!! \App\Helpers\SelectOption::render('triase_igd') !!}
                    </select>
                </div>

                <!-- Dokter Jaga IGD -->
                <div>
                    <label for="dokter_igd" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Dokter Jaga IGD <span class="text-red-500">*</span></label>
                    <select id="dokter_igd" name="dokter_igd" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Dokter Jaga --</option>
                        {!! \App\Helpers\SelectOption::render('dokter_igd') !!}
                    </select>
                </div>

                <!-- Cara Masuk -->
                <div>
                    <label for="cara_masuk" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cara Kedatangan <span class="text-red-500">*</span></label>
                    <select id="cara_masuk" name="cara_masuk" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Kedatangan --</option>
                        {!! \App\Helpers\SelectOption::render('cara_masuk_igd') !!}
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Pengantar & Keluhan -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">3. Penjamin & Pengantar Pasien</h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi pengantar IGD dan keluhan darurat.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Jenis Penjamin -->
                <div>
                    <label for="jenis_penjamin" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Penjamin <span class="text-red-500">*</span></label>
                    <select id="jenis_penjamin" name="jenis_penjamin" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        {!! \App\Helpers\SelectOption::render('jaminan', 'Umum') !!}
                    </select>
                </div>

                <!-- Nama Pengantar -->
                <div>
                    <label for="nama_pengantar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Pengantar</label>
                    <input type="text" id="nama_pengantar" name="nama_pengantar" placeholder="Nama lengkap pengantar" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Hubungan Pengantar -->
                <div>
                    <label for="hubungan_pengantar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Hubungan Pengantar</label>
                    <select id="hubungan_pengantar" name="hubungan_pengantar" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Hubungan --</option>
                        {!! \App\Helpers\SelectOption::render('hubungan_penanggung') !!}
                    </select>
                </div>

                <!-- No HP Pengantar -->
                <div>
                    <label for="nohp_pengantar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. HP Pengantar / Darurat</label>
                    <input type="text" id="nohp_pengantar" name="nohp_pengantar" placeholder="08xxxxxxxxxx" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Keluhan / Kondisi Saat Tiba -->
                <div class="md:col-span-2">
                    <label for="kondisi_tiba" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keluhan / Kondisi Saat Tiba (Cito) <span class="text-red-500">*</span></label>
                    <textarea id="kondisi_tiba" name="kondisi_tiba" rows="3" placeholder="Deskripsikan kondisi kegawatdaruratan, misal: Korban KLL, tidak sadar, pendarahan..." 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none"></textarea>
                </div>
            </div>
            
            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Batalkan
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm shadow-red-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Daftarkan Pasien IGD (Cepat)
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    /* Custom select chevron */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
    }
</style>
@endsection
