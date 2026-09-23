@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Pendaftaran Rawat Inap</h1>
        <p class="text-sm text-slate-500 mt-1">Daftarkan pasien ke ruang perawatan/bangsal rawat inap.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('list_pelayanan_pasien.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            List Pelayanan
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <form action="{{ route('daftar_ranap.store') }}" method="POST" id="formDaftarRanap">
        @csrf
        
        <!-- Section 1: Data Pasien -->
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">1. Identitas Pasien</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cari dan pilih pasien yang akan dirawat inap.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <x-select_pasien label="Cari Pasien" required />
                </div>
            </div>
        </div>

        <!-- Section 2: Ruangan & Layanan -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">2. Ruang Perawatan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan ruangan, kelas, dan dokter penanggung jawab. Bed ditempatkan kemudian lewat Bed Management.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Tanggal Masuk -->
                <div>
                    <label for="tgl_masuk" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal & Waktu Masuk <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="tgl_masuk" name="tgl_masuk" value="{{ date('Y-m-d\TH:i') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700" required>
                    @error('tgl_masuk')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dokter DPJP -->
                <div>
                    <x-select_dokter label="Dokter (DPJP)" name="dokter_id" id="dokter_id" placeholder="-- Pilih Dokter --" />
                </div>

                <!-- Ruangan / Bangsal -->
                <div>
                    <x-select-ruang-perawatan name="bagian_id" id="bagian_id" :selected="old('bagian_id')" />
                    @error('bagian_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas Perawatan -->
                <div>
                    <label for="kelas_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Perawatan</label>
                    <select id="kelas_id" name="kelas_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Ikuti Hak Kelas Nasabah --</option>
                        @foreach ($kelasList as $kelasItem)
                            <option value="{{ $kelasItem->kelas_ruang_id }}" @selected((string) old('kelas_id') === (string) $kelasItem->kelas_ruang_id)>{{ $kelasItem->nama_kelas_ruang }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section 3: Penjamin & Penanggung Jawab -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">3. Penjamin & Penanggung Jawab</h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi asuransi, keluarga terdekat, dan diagnosa awal.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Penjamin -->
                <div>
                    <label for="nasabah_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Penjamin / Nasabah <span class="text-red-500">*</span></label>
                    <select id="nasabah_id" name="nasabah_id"
                            class="select2 w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none" required>
                        <option value="">-- Pilih Penjamin --</option>
                        @foreach ($nasabahs as $nasabah)
                            <option value="{{ $nasabah->nasabah_id }}" @selected((string) old('nasabah_id') === (string) $nasabah->nasabah_id)>{{ $nasabah->nama_nasabah }}</option>
                        @endforeach
                    </select>
                    @error('nasabah_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Asal Masuk (Rujukan/IGD) -->
                <div>
                    <label for="asal_pasien_ranap" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Asal Masuk <span class="text-red-500">*</span></label>
                    <select id="asal_pasien_ranap" name="asal_pasien_ranap"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none" required>
                        <option value="">-- Pilih Asal --</option>
                        {!! \App\Helpers\SelectOption::render('asal_pasien_ranap', old('asal_pasien_ranap')) !!}
                    </select>
                    @error('asal_pasien_ranap')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Penanggung Jawab -->
                <div>
                    <label for="nama_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Penanggung Jawab</label>
                    <input type="text" id="nama_pj" name="nama_pj" value="{{ old('nama_pj') }}" placeholder="Nama lengkap keluarga/kerabat" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Hubungan dengan Pasien -->
                <div>
                    <label for="hubungan_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Hubungan dengan Pasien</label>
                    <select id="hubungan_pj" name="hubungan_pj"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Hubungan --</option>
                        {!! \App\Helpers\SelectOption::render('hubungan_keluarga_ranap', old('hubungan_pj')) !!}
                    </select>
                </div>

                <!-- No. HP Penanggung Jawab -->
                <div>
                    <label for="nohp_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. HP Penanggung Jawab</label>
                    <input type="text" id="nohp_pj" name="nohp_pj" value="{{ old('nohp_pj') }}" placeholder="08xxxxxxxxxx" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Diagnosa Awal (ICD) -->
                <div>
                    <label for="icd_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Diagnosa Awal (ICD)</label>
                    <select id="icd_id" name="icd_id" class="select2-icd w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
                            data-url="{{ route('api.icd.search') }}">
                        <option value=""></option>
                    </select>
                    @error('icd_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Diagnosa Awal / Alasan Masuk -->
                <div class="md:col-span-2">
                    <label for="diagnosa_awal" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Diagnosa Awal / Indikasi Rawat Inap</label>
                    <textarea id="diagnosa_awal" name="diagnosa_awal" rows="3" placeholder="Masukkan diagnosa awal atau alasan medis pasien dirawat inap..." 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('diagnosa_awal') }}</textarea>
                </div>
            </div>
            
            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan & Daftarkan Ranap
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
