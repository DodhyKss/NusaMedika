@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Katalog Form Blanko</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola template formulir kosong (form generator) beserta field-nya — diisi petugas dari dashboard pasien &amp; bisa dicetak.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.katalog_form_blanko.create') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Template
        </a>
    </div>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('admin.katalog_form_blanko.index') }}" method="GET">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:w-64">
                <label for="f_kategori" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kategori</label>
                <input id="f_kategori" name="kategori" value="{{ request('kategori') }}" placeholder="Cari kategori..."
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>
            <div class="w-full sm:w-64">
                <label for="f_departemen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Departemen</label>
                <input id="f_departemen" name="departemen" value="{{ request('departemen') }}" placeholder="Cari departemen..."
                       class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            </div>
            <div class="w-full sm:w-48">
                <label for="f_status" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                <select id="f_status" name="status"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    <option value="">-- Semua --</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Filter</button>
                @if (request()->hasAny(['kategori', 'departemen', 'status']))
                    <a href="{{ route('admin.katalog_form_blanko.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Reset</a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 980px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kode</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Template</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kategori / Departemen</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Versi</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Field</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($data as $i => $row)
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $i + 1 }}</td>
                    <td class="px-3 py-3"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $row->kode_blanko }}</code></td>
                    <td class="px-3 py-3 font-semibold text-slate-800">
                        {{ $row->nama_blanko }}
                        <div class="text-[10px] text-slate-400 font-normal">/ {{ $row->slug }}{{ $row->publish_time ? ' · publish '.$row->publish_time->format('d/m/Y H:i') : ' · belum publish' }}</div>
                    </td>
                    <td class="px-3 py-3 text-slate-600">
                        {{ $row->kategori ?: '-' }} <span class="text-slate-400">/</span> {{ $row->departemen ?: '-' }}
                        @if ($row->filter_adaptif)
                            <div class="text-[10px] text-slate-400">
                                adaptif: {{ collect($row->filter_adaptif)->map(fn ($v, $k) => $k.'='.implode(',', (array) $v))->implode(' · ') }}
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-center text-slate-600">{{ $row->versi }}</td>
                    <td class="px-3 py-3 text-center text-slate-600">{{ $row->fields_count }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="text-[10px] px-1.5 py-0.5 rounded {{ $row->is_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">{{ $row->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('emr.katalog_form_blanko.cetak', ['form_blanko' => $row->form_blanko_id]) }}" target="_blank" class="cursor-pointer p-1.5 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-md transition-colors" title="Cetak Blanko Kosong">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                            <a href="{{ route('admin.katalog_form_blanko.edit', ['katalog_form_blanko' => $row->form_blanko_id]) }}" class="cursor-pointer p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors" title="Edit Template">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('admin.katalog_form_blanko.destroy', ['katalog_form_blanko' => $row->form_blanko_id]) }}" method="POST" data-confirm-message="Yakin ingin menghapus template {{ $row->nama_blanko }} beserta field dan seluruh pengisiannya?" data-confirm-danger="true" data-confirm-title="Hapus Template">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus Template">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-3 py-8 text-center text-slate-400">Belum ada template. Klik "Tambah Template" untuk membuat yang pertama.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
