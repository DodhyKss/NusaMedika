@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Group Tindakan</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui panel pemeriksaan unit penunjang.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.master_group_tindakan.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 1 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">{{ $group->nama_group_tindakan }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui nama group, unit penunjang, dan daftar tindakannya.</p>
        </div>
    </div>

    <div class="p-6">
        @include('moduls.Administrator.ManajemenMaster.MasterGroupTindakan._form', [
            'actionUrl' => route('admin.master_group_tindakan.update', $group->group_tindakan_id),
            'isEdit' => true,
            'group' => $group,
            'bagianList' => $bagianList,
            'tindakanList' => $tindakanList,
            'selectedTindakanIds' => $selectedTindakanIds,
        ])
    </div>
</div>
@endsection
