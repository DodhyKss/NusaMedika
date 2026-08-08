@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Nasabah Pasien</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data penjamin, asuransi, atau BPJS pasien.</p>
    </div>
    <div class="flex gap-2">
        <a href="#" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
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
        <form action="#" method="POST" id="formEditNasabah">
            @csrf
            <!-- Method PUT untuk Edit -->
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                
                <!-- No Rekam Medis (Readonly) -->
                <div>
                    <label for="no_rm" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Rekam Medis</label>
                    <input type="text" id="no_rm" name="no_rm" value="RM-001234" readonly
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                </div>

                <!-- Nama Pasien (Readonly) -->
                <div>
                    <label for="nama_pasien" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Pasien</label>
                    <input type="text" id="nama_pasien" name="nama_pasien" value="John Doe" readonly
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                </div>

                <div class="md:col-span-2">
                    <hr class="border-slate-200 my-2">
                </div>

                <!-- Jenis Nasabah / Penjamin -->
                <div>
                    <label for="jenis_nasabah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Nasabah <span class="text-red-500">*</span></label>
                    <select id="jenis_nasabah" name="jenis_nasabah" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Jenis Nasabah --</option>
                        {!! \App\Helpers\SelectOption::render('jenis_nasabah', 'BPJS Kesehatan') !!}
                    </select>
                </div>

                <!-- Nomor Kartu / BPJS -->
                <div>
                    <label for="nomor_kartu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nomor Kartu / BPJS</label>
                    <input type="text" id="nomor_kartu" name="nomor_kartu" value="0001234567890" placeholder="Masukkan nomor kartu" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Nama Perusahaan / Asuransi -->
                <div>
                    <label for="nama_perusahaan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Perusahaan Asuransi</label>
                    <input type="text" id="nama_perusahaan" name="nama_perusahaan" placeholder="Contoh: Prudential, Allianz, dll (Jika ada)" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Kelas Perawatan -->
                <div>
                    <label for="kelas_perawatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Hak Perawatan</label>
                    <select id="kelas_perawatan" name="kelas_perawatan" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        {!! \App\Helpers\SelectOption::render('kelas_perawatan', 'Kelas 1') !!}
                    </select>
                </div>

                <!-- Catatan Tambahan -->
                <div class="md:col-span-2 mt-2">
                    <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Catatan Tambahan</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Catatan khusus mengenai nasabah/asuransi ini" 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none"></textarea>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="#" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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