@extends('layouts.app')

@section('content')
@php
    $instalasiOptions = array_filter([
        env('JENIS_RAWAT_RJ') => 'Rawat Jalan',
        env('JENIS_RAWAT_RI') => 'Rawat Inap',
        env('JENIS_RAWAT_IGD') => 'IGD',
        env('JENIS_RAWAT_MCU') => 'MCU',
    ]);
    $selectedInstalasi = old('instalasi', $nasabah->instalasi ?? []) ?? [];
@endphp
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Nasabah</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data master penjamin, asuransi, atau BPJS.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.nasabah.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Nasabah</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbaiki data penjamin, asuransi, atau BPJS.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.nasabah.update', $nasabah->nasabah_id) }}" method="POST" id="formEditNasabah">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Nama Nasabah -->
                <div>
                    <label for="nama_nasabah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Nasabah <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_nasabah" name="nama_nasabah" value="{{ old('nama_nasabah', $nasabah->nama_nasabah) }}" placeholder="Contoh: BPJS Kesehatan, Prudential, PT. Maju Bersama" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_nasabah')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Nasabah -->
                <div>
                    <label for="email_nasabah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Email Nasabah</label>
                    <input type="email" id="email_nasabah" name="email_nasabah" value="{{ old('email_nasabah', $nasabah->email_nasabah) }}" placeholder="nama@perusahaan.com" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('email_nasabah')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat Nasabah -->
                <div>
                    <label for="alamat_nasabah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Alamat Nasabah</label>
                    <textarea id="alamat_nasabah" name="alamat_nasabah" rows="3" placeholder="Alamat kantor / penjamin" 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('alamat_nasabah', $nasabah->alamat_nasabah) }}</textarea>
                    @error('alamat_nasabah')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telepon 1 -->
                <div>
                    <label for="telp_nasabah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Telepon 1</label>
                    <input type="text" id="telp_nasabah" name="telp_nasabah" value="{{ old('telp_nasabah', $nasabah->telp_nasabah) }}" placeholder="021-0000000" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('telp_nasabah')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telepon 2 -->
                <div>
                    <label for="telp_nasabah_2" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Telepon 2</label>
                    <input type="text" id="telp_nasabah_2" name="telp_nasabah_2" value="{{ old('telp_nasabah_2', $nasabah->telp_nasabah_2) }}" placeholder="0812-00000000" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('telp_nasabah_2')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <hr class="border-slate-200 my-2">
                </div>

                <!-- Tipe Biaya -->
                <div>
                    <label for="tipe_biaya" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tipe Biaya</label>
                    <input type="number" id="tipe_biaya" name="tipe_biaya" value="{{ old('tipe_biaya', $nasabah->tipe_biaya) }}" placeholder="Contoh: 1" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('tipe_biaya')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Biaya Administrasi -->
                <div>
                    <label for="biaya_administrasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Biaya Administrasi</label>
                    <input type="number" id="biaya_administrasi" name="biaya_administrasi" value="{{ old('biaya_administrasi', $nasabah->biaya_administrasi) }}" step="0.01" placeholder="Contoh: 50000" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('biaya_administrasi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Batas Atas -->
                <div>
                    <label for="batas_atas" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Batas Atas</label>
                    <input type="number" id="batas_atas" name="batas_atas" value="{{ old('batas_atas', $nasabah->batas_atas) }}" step="0.01" placeholder="Batas maksimal klaim" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('batas_atas')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instalasi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Instalasi</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($instalasiOptions as $kode => $label)
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="instalasi[]" value="{{ $kode }}" @checked(in_array($kode, $selectedInstalasi))
                                       class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                <span class="text-sm text-slate-600">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('instalasi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <hr class="border-slate-200 my-2">
                </div>

                <!-- Contact Person 1 -->
                <div>
                    <label for="cp_nama" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Person (Nama)</label>
                    <input type="text" id="cp_nama" name="cp_nama" value="{{ old('cp_nama', $nasabah->cp_nama) }}" placeholder="Nama CP utama" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('cp_nama')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cp_telp" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Person (Telepon)</label>
                    <input type="text" id="cp_telp" name="cp_telp" value="{{ old('cp_telp', $nasabah->cp_telp) }}" placeholder="Telepon CP utama" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('cp_telp')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Person 2 -->
                <div>
                    <label for="cp_nama_2" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Person 2 (Nama)</label>
                    <input type="text" id="cp_nama_2" name="cp_nama_2" value="{{ old('cp_nama_2', $nasabah->cp_nama_2) }}" placeholder="Nama CP kedua (opsional)" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('cp_nama_2')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cp_telp_2" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Person 2 (Telepon)</label>
                    <input type="text" id="cp_telp_2" name="cp_telp_2" value="{{ old('cp_telp_2', $nasabah->cp_telp_2) }}" placeholder="Telepon CP kedua (opsional)" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('cp_telp_2')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.nasabah.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
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
