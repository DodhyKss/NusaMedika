@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Ubah Bed</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data bed / tempat tidur.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.bed.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
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
            <h2 class="text-base font-semibold text-slate-800">Informasi Bed</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui data bed / tempat tidur.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.bed.update', $bed->bed_id) }}" method="POST" id="formEditBed">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Ruang Perawatan -->
                <div>
                    <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Ruang Perawatan <span class="text-red-500">*</span></label>
                    <select id="bagian_id" name="bagian_id"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
                        required>
                        <option value=""></option>
                        @foreach ($bagians as $ruang)
                            <option value="{{ $ruang->bagian_id }}" @selected((string) old('bagian_id', $bed->bagian_id) === (string) $ruang->bagian_id)>{{ $ruang->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div>
                    <label for="kelas_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas <span class="text-red-500">*</span></label>
                    <select id="kelas_id" name="kelas_id"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
                        required>
                        <option value=""></option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->kelas_ruang_id }}" @selected((string) old('kelas_id', $bed->kelas_id) === (string) $kelas->kelas_ruang_id)>{{ $kelas->nama_kelas_ruang }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No. Kamar -->
                <div>
                    <label for="no_kamar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Kamar <span class="text-red-500">*</span></label>
                    <input type="text" id="no_kamar" name="no_kamar" value="{{ old('no_kamar', $bed->no_kamar) }}" placeholder="Contoh: R16-01"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('no_kamar')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Bed -->
                <div>
                    <label for="nama_bed" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Bed <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_bed" name="nama_bed" value="{{ old('nama_bed', $bed->nama_bed) }}" placeholder="Contoh: B-01"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_bed')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Flag Isolasi -->
                <div>
                    <label for="flag_isolasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Isolasi</label>
                    <select id="flag_isolasi" name="flag_isolasi" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <option value="2" @selected((int) old('flag_isolasi', $bed->flag_isolasi ?? 2) === 2)>Normal</option>
                        <option value="1" @selected((int) old('flag_isolasi', $bed->flag_isolasi ?? 2) === 1)>Isolasi</option>
                        <option value="3" @selected((int) old('flag_isolasi', $bed->flag_isolasi ?? 2) === 3)>Covid-19</option>
                    </select>
                    @error('flag_isolasi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan', $bed->keterangan) }}" placeholder="Opsional"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('keterangan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.bed.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Perbarui Bed
                </button>
            </div>
        </form>
    </div>
</div>
@endsection