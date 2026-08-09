@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah User</h1>
        <p class="text-sm text-slate-500 mt-1">Buat user baru dan pilih hak aksesnya.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.user.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<form action="{{ route('admin.user.store') }}" method="POST" id="formTambahUser">
    @csrf

    <!-- Data Dasar User -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Data User</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label for="user_name" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Username <span class="text-red-500">*</span></label>
                    <input type="text" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Contoh: admin_keuangan"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('user_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="user_password" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Password <span class="text-red-500">*</span></label>
                    <input type="text" id="user_password" name="user_password" value="{{ old('user_password') }}" placeholder="Password login"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('user_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="pegawai_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Pegawai <span class="text-red-500">*</span></label>
                    <select id="pegawai_id" name="pegawai_id" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach ($pegawais as $pegawai)
                            <option value="{{ $pegawai->pegawai_id }}" {{ old('pegawai_id') == $pegawai->pegawai_id ? 'selected' : '' }}>
                                {{ $pegawai->nama_pegawai }}{{ $pegawai->nip ? ' — '.$pegawai->nip : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p class="mt-1 text-[11px] text-slate-400">User wajib terhubung ke data pegawai dari master data.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hak Akses -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Hak Akses (Sub Menu)</h2>
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSemua(true)" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Pilih Semua</button>
                <span class="text-slate-300">|</span>
                <button type="button" onclick="toggleSemua(false)" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Bersihkan</button>
            </div>
        </div>
        <div class="p-6">
            @if ($moduls->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($moduls as $modul)
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-100/70 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                <span class="text-sm font-bold text-slate-700">{{ $modul->nama_modul }}</span>
                            </div>
                            <div class="p-4">
                                @foreach ($modul->menus as $menu)
                                    @if ($menu->subMenus->isNotEmpty())
                                        <div class="mb-4 last:mb-0">
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ $menu->nama_menu }}</p>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @foreach ($menu->subMenus as $subMenu)
                                                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors cursor-pointer">
                                                        <input type="checkbox" name="sub_menu_ids[]" value="{{ $subMenu->sub_menu_id }}"
                                                               {{ is_array(old('sub_menu_ids')) && in_array($subMenu->sub_menu_id, old('sub_menu_ids')) ? 'checked' : '' }}
                                                               class="mt-0.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 accent-blue-600 cursor-pointer">
                                                        <span class="text-sm text-slate-700">{{ $subMenu->nama_sub_menu }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Belum ada modul. Tambahkan modul terlebih dahulu.</p>
            @endif
            @error('sub_menu_ids')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
            Reset Form
        </button>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            Simpan User
        </button>
    </div>
</form>

<script>
    function toggleSemua(checked) {
        document.querySelectorAll('#formTambahUser input[name="sub_menu_ids[]"]').forEach(function (cb) {
            cb.checked = checked;
        });
    }
</script>
@endsection
