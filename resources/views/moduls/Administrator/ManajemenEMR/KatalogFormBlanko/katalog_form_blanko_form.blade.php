@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">{{ $row ? 'Edit Template: '.$row->nama_blanko : 'Tambah Template Form Blanko' }}</h1>
        <p class="text-sm text-slate-500 mt-1">
            {{ $row ? 'Metadata template + field builder. Field yang dihapus dari daftar ikut di-soft-delete saat disimpan.' : 'Isi metadata dulu — setelah tersimpan, tambahkan field lewat halaman edit.' }}
        </p>
    </div>
    <a href="{{ route('admin.katalog_form_blanko.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Kembali ke Daftar</a>
</div>

@php
    $isEdit = $row !== null;
    $v = fn ($key, $default = '') => old($key, $row ? $row->{$key} : $default);
    $oldFilter = old('filter_adaptif');
    $filterAktif = [
        'kategori_umur' => array_map('strval', is_array($oldFilter) ? ($oldFilter['kategori_umur'] ?? []) : ($row->filter_adaptif['kategori_umur'] ?? [])),
        'jenis_kelamin' => array_map('strval', is_array($oldFilter) ? ($oldFilter['jenis_kelamin'] ?? []) : ($row->filter_adaptif['jenis_kelamin'] ?? [])),
        'jenis_rawat' => array_map('strval', is_array($oldFilter) ? ($oldFilter['jenis_rawat'] ?? []) : ($row->filter_adaptif['jenis_rawat'] ?? [])),
        'profesi_id' => array_map('strval', is_array($oldFilter) ? ($oldFilter['profesi_id'] ?? []) : ($row->filter_adaptif['profesi_id'] ?? [])),
    ];
    $fieldBuilder = \App\Http\Controllers\Administrator\ManajemenEMR\KatalogFormBlanko\KatalogFormBlankoController::class;
@endphp

<form action="{{ $isEdit ? route('admin.katalog_form_blanko.update', ['katalog_form_blanko' => $row->form_blanko_id]) : route('admin.katalog_form_blanko.store') }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <!-- Metadata -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Metadata Template</h2>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Blanko <span class="text-red-500">*</span></label>
                <input name="kode_blanko" value="{{ $v('kode_blanko') }}" required maxlength="50" placeholder="mis. BLN-001"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @error('kode_blanko') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Template <span class="text-red-500">*</span></label>
                <input name="nama_blanko" value="{{ $v('nama_blanko') }}" required maxlength="150" placeholder="mis. Lembar Verifikasi Farmasi"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @error('nama_blanko') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Slug <span class="text-red-500">*</span></label>
                <input name="slug" value="{{ $v('slug') }}" required maxlength="150" pattern="[a-z0-9_]+" placeholder="lembar_verifikasi"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @error('slug') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
                <label class="flex items-center gap-2 text-sm text-slate-700 mt-2">
                    <input type="checkbox" name="is_aktif" value="1" {{ $v('is_aktif', 1) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Aktif
                </label>
            </div>

            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kategori</label>
                <input name="kategori" value="{{ $v('kategori') }}" maxlength="100" placeholder="mis. Farmasi"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @error('kategori') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Departemen</label>
                <input name="departemen" value="{{ $v('departemen') }}" maxlength="100" placeholder="mis. Instalasi Farmasi"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @error('departemen') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Ukuran Kertas <span class="text-red-500">*</span></label>
                <select name="ukuran_halaman" required
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @foreach (['A4', 'A5', 'Legal'] as $uk)
                        <option value="{{ $uk }}" {{ $v('ukuran_halaman', 'A4') === $uk ? 'selected' : '' }}>{{ $uk }}</option>
                    @endforeach
                </select>
                @error('ukuran_halaman') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Orientasi <span class="text-red-500">*</span></label>
                <select name="orientasi_halaman" required
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    <option value="portrait" {{ $v('orientasi_halaman', 'portrait') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                    <option value="landscape" {{ $v('orientasi_halaman') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                </select>
                @error('orientasi_halaman') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="2" maxlength="2000" placeholder="Keterangan singkat isi formulir…"
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">{{ $v('deskripsi') }}</textarea>
                @error('deskripsi') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kop Surat (HTML sederhana, opsional)</label>
                <textarea name="kop_surat" rows="2" maxlength="10000" placeholder="RS …&#10;Jl. …"
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">{{ $v('kop_surat') }}</textarea>
                @error('kop_surat') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Menu Induk (opsional)</label>
                <input type="number" name="menu_induk" value="{{ $v('menu_induk') }}" min="0"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sub Induk (opsional)</label>
                <input type="number" name="sub_induk" value="{{ $v('sub_induk') }}" min="0"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Extra Induk (opsional)</label>
                <input type="number" name="extra_induk" value="{{ $v('extra_induk') }}" min="0"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tampilan Cetak</label>
                <div class="flex flex-col gap-1.5 mt-1">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="tampilkan_identitas_pasien" value="1" {{ $v('tampilkan_identitas_pasien', 1) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Identitas pasien
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="tampilkan_logo" value="1" {{ $v('tampilkan_logo', 1) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Logo RS
                    </label>
                </div>
            </div>
        </div>

        <!-- Filter adaptif -->
        <div class="mt-5 pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Filter Adaptif (kosongkan = berlaku untuk semua)</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach ($fieldBuilder::FILTER as $grupKey => $opsiGrup)
                <div>
                    <p class="text-[11px] font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        @if ($grupKey === 'profesi_id') Profesi @elseif ($grupKey === 'jenis_rawat') Jenis Rawat @elseif ($grupKey === 'jenis_kelamin') Jenis Kelamin @else Kategori Umur @endif
                    </p>
                    @foreach ($opsiGrup as $val => $label)
                        <label class="flex items-center gap-2 text-sm text-slate-700 mb-1">
                            <input type="checkbox" name="filter_adaptif[{{ $grupKey }}][]" value="{{ $val }}"
                                   {{ in_array((string) $val, $filterAktif[$grupKey], false) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            {{ is_numeric($val) && $grupKey === 'profesi_id' ? $label : $val }}
                            @if ($grupKey === 'jenis_kelamin' && $label !== $val) <span class="text-slate-400 text-xs">({{ $label }})</span> @endif
                        </label>
                    @endforeach
                </div>
                @endforeach
            </div>
            @error('filter_adaptif') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Field builder -->
    @if ($isEdit)
        @include('moduls.Administrator.ManajemenEMR.KatalogFormBlanko.katalog_form_blanko_field')
    @endif

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            {{ $isEdit ? 'Simpan Template & Field' : 'Simpan Template' }}
        </button>
        <a href="{{ route('admin.katalog_form_blanko.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Batal</a>
        @if ($isEdit && $row->publish_time)
            <span class="text-xs text-slate-400">Terakhir publish: {{ $row->publish_time->format('d/m/Y H:i') }} (versi {{ $row->versi }})</span>
        @endif
    </div>
</form>

@if ($isEdit)
    <form action="{{ route('admin.katalog_form_blanko.publish', ['katalog_form_blanko' => $row->form_blanko_id]) }}" method="POST" class="mt-3">
        @csrf
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
            Publish (versi {{ $row->versi }} → {{ $row->versi + 1 }})
        </button>
    </form>
@endif
@endsection
