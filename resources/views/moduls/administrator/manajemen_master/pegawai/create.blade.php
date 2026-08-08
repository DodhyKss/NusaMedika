@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Pegawai</h1>
        <p class="text-sm text-slate-500 mt-1">Buat data pegawai baru.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

@if (session('error'))
    <div class="mb-4 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.pegawai.store') }}" method="POST" id="formTambahPegawai">
            @csrf

            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Data Diri</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div class="md:col-span-2">
                    <label for="nama_pegawai" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Pegawai <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_pegawai" name="nama_pegawai" value="{{ old('nama_pegawai') }}" placeholder="Nama lengkap pegawai"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_pegawai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nip" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">NIP</label>
                    <input type="text" id="nip" name="nip" value="{{ old('nip') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">NIK</label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nik')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Kepegawaian</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bagian</label>
                    <select id="bagian_id" name="bagian_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Bagian --</option>
                        @foreach ($bagians as $bagian)
                            <option value="{{ $bagian->bagian_id }}" {{ old('bagian_id') == $bagian->bagian_id ? 'selected' : '' }}>{{ $bagian->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="profesi_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Profesi</label>
                    <select id="profesi_id" name="profesi_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Profesi --</option>
                        @foreach ($profesis as $profesi)
                            <option value="{{ $profesi->profesi_id }}" {{ old('profesi_id') == $profesi->profesi_id ? 'selected' : '' }}>{{ $profesi->nama_profesi }}</option>
                        @endforeach
                    </select>
                    @error('profesi_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="jabatan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jabatan</label>
                    <select id="jabatan_id" name="jabatan_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach ($jabatans as $jabatan)
                            <option value="{{ $jabatan->jabatan_id }}" {{ old('jabatan_id') == $jabatan->jabatan_id ? 'selected' : '' }}>{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    @error('jabatan_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status_kepegawaian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Status Kepegawaian</label>
                    <select id="status_kepegawaian_id" name="status_kepegawaian_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        {!! \App\Helpers\SelectOption::render('status_kepegawaian', old('status_kepegawaian_id'), '-- Pilih Status --') !!}
                    </select>
                    @error('status_kepegawaian_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">SIP & STR</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
                <div>
                    <label for="sip" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. SIP</label>
                    <input type="text" id="sip" name="sip" value="{{ old('sip') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tgl_awal_sip" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">SIP Berlaku Awal</label>
                    <input type="date" id="tgl_awal_sip" name="tgl_awal_sip" value="{{ old('tgl_awal_sip') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @error('tgl_awal_sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tgl_akhir_sip" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">SIP Berlaku Akhir</label>
                    <input type="date" id="tgl_akhir_sip" name="tgl_akhir_sip" value="{{ old('tgl_akhir_sip') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @error('tgl_akhir_sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="str" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. STR</label>
                    <input type="text" id="str" name="str" value="{{ old('str') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('str')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tgl_awal_str" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">STR Berlaku Awal</label>
                    <input type="date" id="tgl_awal_str" name="tgl_awal_str" value="{{ old('tgl_awal_str') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @error('tgl_awal_str')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tgl_akhir_str" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">STR Berlaku Akhir</label>
                    <input type="date" id="tgl_akhir_str" name="tgl_akhir_str" value="{{ old('tgl_akhir_str') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @error('tgl_akhir_str')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="no_rfid" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. RFID</label>
                    <input type="text" id="no_rfid" name="no_rfid" value="{{ old('no_rfid') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('no_rfid')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="id_satu_sehat" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">ID Satu Sehat</label>
                    <input type="text" id="id_satu_sehat" name="id_satu_sehat" value="{{ old('id_satu_sehat') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('id_satu_sehat')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Pegawai
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
    }
</style>
@endsection
