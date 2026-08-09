@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Kelas</h1>
        <p class="text-sm text-slate-500 mt-1">Tambahkan data kelas perawatan / kelas ruang.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.kelas.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Kelas</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data kelas perawatan / kelas ruang.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.kelas.store') }}" method="POST" id="formTambahKelas">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Nama Kelas -->
                <div>
                    <label for="nama_kelas_ruang" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_kelas_ruang" name="nama_kelas_ruang" value="{{ old('nama_kelas_ruang') }}" placeholder="Contoh: Kelas 1, Kelas 2, VIP, VVIP" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_kelas_ruang')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas Khusus -->
                <div>
                    <label for="kelas_khusus" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Khusus</label>
                    <input type="text" id="kelas_khusus" name="kelas_khusus" value="{{ old('kelas_khusus') }}" placeholder="Contoh: KHUSUS (opsional)" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('kelas_khusus')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas BPJS -->
                <div>
                    <label for="kelas_bpjs" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas BPJS</label>
                    <input type="number" id="kelas_bpjs" name="kelas_bpjs" value="{{ old('kelas_bpjs') }}" placeholder="Contoh: 1, 2, 3 (opsional)" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('kelas_bpjs')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.kelas.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
