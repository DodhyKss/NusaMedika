@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Nasabah Pasien</h1>
        <p class="text-sm text-slate-500 mt-1">Tambahkan data penjamin, asuransi, atau BPJS untuk pasien.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('nasabah_pasien.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Penjamin / Asuransi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data asuransi atau BPJS yang digunakan oleh pasien.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('nasabah_pasien.store') }}" method="POST" id="formTambahNasabah">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <x-select_pasien label="Pilih Pasien" :selected="old('pasien_id')" required />
                    @error('pasien_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <hr class="border-slate-200 my-2">
                </div>

                <!-- Nomor Kartu / BPJS -->
                <div>
                    <label for="nomor_kartu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nomor Kartu / BPJS</label>
                    <input type="text" id="nomor_kartu" name="nomor_kartu" value="{{ old('nomor_kartu') }}" placeholder="Masukkan nomor kartu" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Nasabah / Penjamin -->
                <div>
                    <label for="nasabah_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nasabah / Penjamin <span class="text-red-500">*</span></label>
                    <select id="nasabah_id" name="nasabah_id" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Nasabah --</option>
                        @foreach ($nasabahs as $nasabah)
                            <option value="{{ $nasabah->nasabah_id }}" @selected((string) old('nasabah_id') === (string) $nasabah->nasabah_id)>{{ $nasabah->nama_nasabah }}</option>
                        @endforeach
                    </select>
                    @error('nasabah_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas Perawatan -->
                <div>
                    <label for="kelas_perawatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Hak Perawatan <span class="text-red-500">*</span></label>
                    <select id="kelas_perawatan" name="kelas_perawatan" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($kelas as $kelasItem)
                            <option value="{{ $kelasItem->kelas_ruang_id }}" @selected((string) old('kelas_perawatan') === (string) $kelasItem->kelas_ruang_id)>{{ $kelasItem->nama_kelas_ruang }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Catatan Tambahan -->
                <div class="md:col-span-2 mt-2">
                    <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Catatan Tambahan</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan khusus mengenai nasabah/asuransi ini" 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('nasabah_pasien.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Simpan Nasabah Baru
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