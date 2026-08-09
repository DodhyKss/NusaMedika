@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Nasabah Pasien</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data penjamin, asuransi, atau BPJS untuk pasien.</p>
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
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Penjamin / Asuransi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbaiki data asuransi atau BPJS yang digunakan oleh pasien.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('nasabah_pasien.update', $pasienNasabah->pasien_nasabah_id) }}" method="POST" id="formEditNasabah">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <x-select_pasien label="Pilih Pasien" :selected="old('pasien_id', $pasienNasabah->pasien_id)" required />
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
                    <input type="text" id="nomor_kartu" name="nomor_kartu" value="{{ old('nomor_kartu', $pasienNasabah->no_peserta) }}" placeholder="Masukkan nomor kartu" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Nasabah / Penjamin -->
                <div>
                    <label for="nasabah_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nasabah / Penjamin</label>
                    <select id="nasabah_id" name="nasabah_id" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">Umum / Mandiri</option>
                        @foreach ($nasabahs as $nasabah)
                            <option value="{{ $nasabah->nasabah_id }}" @selected((string) old('nasabah_id', $pasienNasabah->nasabah_id) === (string) $nasabah->nasabah_id)>{{ $nasabah->nama_nasabah }}</option>
                        @endforeach
                    </select>
                    @error('nasabah_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas Perawatan -->
                <div>
                    <label for="kelas_perawatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Hak Perawatan</label>
                    <select id="kelas_perawatan" name="kelas_perawatan" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach ($kelas as $kelasItem)
                            <option value="{{ $kelasItem->kelas_ruang_id }}" @selected((string) old('kelas_perawatan', $pasienNasabah->hak_kelas_id) === (string) $kelasItem->kelas_ruang_id)>{{ $kelasItem->nama_kelas_ruang }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Catatan Tambahan -->
                <div class="md:col-span-2 mt-2">
                    <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Catatan Tambahan</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan khusus mengenai nasabah/asuransi ini" 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('catatan', $pasienNasabah->catatan) }}</textarea>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('nasabah_pasien.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
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
