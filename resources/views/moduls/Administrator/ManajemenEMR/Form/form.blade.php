@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Master Form EMR</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola form, objek, dan mapping variabel form dengan objek pada EMR.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.form.create', ['tab' => $tab]) }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah {{ ucfirst($tab) }}
        </a>
    </div>
</div>

<!-- Tabs -->
<div class="mb-5">
    <div class="inline-flex bg-slate-100 rounded-xl p-1 gap-1">
        @php
            $tabs = [
                'form' => ['label' => 'Form', 'count' => $forms->count()],
                'objek' => ['label' => 'Objek', 'count' => $objeks->count()],
                'mapping' => ['label' => 'Mapping', 'count' => ''],
            ];
        @endphp
        @foreach ($tabs as $key => $t)
            <a href="{{ route('admin.form.index', ['tab' => $key]) }}"
               class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $tab === $key ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $t['label'] }}
                @if ($t['count'] !== '')
                <span class="ml-1.5 text-[11px] {{ $tab === $key ? 'bg-blue-50 text-blue-600' : 'bg-slate-200 text-slate-500' }} px-1.5 py-0.5 rounded-full">{{ $t['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>

<!-- Filter -->
@if ($tab === 'mapping')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('admin.form.index') }}" method="GET">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:w-80">
                <label for="form_filter" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Form</label>
                <div class="relative">
                    <select id="form_filter" name="form_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none"
                            onchange="this.form.submit()">
                        <option value="">-- Semua Form --</option>
                        @foreach ($forms as $item)
                            <option value="{{ $item->form_id }}" {{ request('form_id') == $item->form_id ? 'selected' : '' }}>{{ $item->nama_form }} ({{ $item->slug }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if (request()->has('form_id'))
                <a href="{{ route('admin.form.index', ['tab' => $tab]) }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Reset</a>
            @endif
        </div>
    </form>
</div>
@endif

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 900px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    @if ($tab === 'form')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Form</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Slug</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">ID Dash Menu</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Rawatan</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Jumlah Objek</th>
                    @elseif ($tab === 'objek')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Objek</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Dipakai Form</th>
                    @elseif ($tab === 'mapping')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Form</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Objek</th>
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Variabel</th>
                    @endif
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($data as $i => $row)
                @php
                    $pk = match ($tab) { 'objek' => 'objek_id', 'mapping' => 'objek_form_control_id', default => 'form_id' };
                @endphp
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $i + 1 }}</td>
                    @if ($tab === 'form')
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $row->nama_form }}</td>
                        <td class="px-3 py-3 text-slate-600"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $row->slug }}</code></td>
                        <td class="px-3 py-3 text-slate-600">{{ $row->id_dash_menu ?: '-' }}</td>
                        <td class="px-3 py-3 text-center">
                            <div class="flex justify-center gap-1">
                                @foreach (['ri' => 'RI', 'rj' => 'RJ', 'igd' => 'IGD', 'mcu' => 'MCU'] as $key => $label)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $row->$key == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">{{ $label }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-slate-600">{{ $row->objek_count }}</td>
                    @elseif ($tab === 'objek')
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $row->nama_objek }}</td>
                        <td class="px-3 py-3 text-center text-slate-600">{{ $row->form_count }}</td>
                    @elseif ($tab === 'mapping')
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $row->nama_form }} <span class="text-[10px] text-slate-400">({{ $row->slug }})</span></td>
                        <td class="px-3 py-3 text-slate-600">{{ $row->nama_objek ?: '-' }}</td>
                        <td class="px-3 py-3 text-slate-600"><code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $row->variabel }}</code></td>
                    @endif
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.form.edit', ['form' => $row->$pk, 'tab' => $tab]) }}" class="cursor-pointer p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors" title="Edit {{ ucfirst($tab) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('admin.form.destroy', ['form' => $row->$pk, 'tab' => $tab]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $tab }} ini beserta relasinya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus {{ ucfirst($tab) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-slate-400">Belum ada data {{ $tab }}.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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