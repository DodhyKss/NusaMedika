@extends('layouts.iframe')

@section('content')
    @php
        $isEdit = isset($edit_soap);
        $actionUrl = $isEdit 
            ? route('emr.soap.update', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id]) 
            : route('emr.soap.store', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);
        
        $deleteUrl = $isEdit 
            ? route('emr.soap.destroy', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id]) 
            : '';
            
        $formData = $isEdit ? (json_decode($edit_soap->data, true) ?? []) : [];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat SOAP"
        :titleForm="$isEdit ? 'Edit Form SOAP' : 'Form SOAP Baru'"
        :subtitleForm="$isEdit ? 'Perbarui rekam medis pasien.' : 'Isi form rekam medis di bawah ini.'"
        :historyGrouped="$history_grouped ?? []"
        routeName="emr.soap.index"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$actionUrl"
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl"
        :emrId="$isEdit ? $edit_soap->emr_id : ''"
    >
        <x-slot name="listRiwayat">
            @forelse($riwayat_soap as $item)
                @php
                    $data = json_decode($item->data, true) ?? [];
                    $isActive = isset($edit_soap) && $edit_soap->emr_id == $item->emr_id;
                @endphp
                <div class="group border {{ $isActive ? 'border-blue-400 bg-blue-50 shadow-md' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md' }} rounded-xl p-4 cursor-pointer transition-all relative">
                    <a href="{{ route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="absolute inset-0 z-10"></a>
                    
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-blue-600">{{ \Carbon\Carbon::parse($item->tgl_jam)->format('d/m/Y H:i') }}</p>
                            <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $item->nama_pegawai ?? 'Dokter' }}</p>
                        </div>
                        
                        @if($isActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Aktif
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-xs text-slate-600 space-y-1 mt-3 line-clamp-3">
                        <p><span class="font-semibold text-slate-700">S:</span> {{ Str::limit($data['s'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">O:</span> {{ Str::limit($data['o'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">A:</span> {{ Str::limit($data['a'] ?? '-', 40) }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-center opacity-50">
                    <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-sm text-slate-500">Belum ada riwayat SOAP</p>
                </div>
            @endforelse
        </x-slot>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 space-y-6">
                <!-- Subjective -->
                <div>
                    <label for="subjective" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs">S</span>
                        Subjective (Subyektif)
                    </label>
                    <textarea id="subjective" name="subjective" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors text-sm" placeholder="Keluhan utama, riwayat penyakit, keluhan tambahan...">{{ old('subjective', $formData['s'] ?? '') }}</textarea>
                </div>

                <!-- Objective -->
                <div>
                    <label for="objective" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs">O</span>
                        Objective (Obyektif)
                    </label>
                    <textarea id="objective" name="objective" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-sm" placeholder="Hasil pemeriksaan fisik, tanda vital, hasil lab...">{{ old('objective', $formData['o'] ?? '') }}</textarea>
                </div>

                <!-- Assessment -->
                <div>
                    <label for="assessment" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold text-xs">A</span>
                        Assessment (Asesmen)
                    </label>
                    <textarea id="assessment" name="assessment" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors text-sm" placeholder="Diagnosis kerja, diagnosis banding...">{{ old('assessment', $formData['a'] ?? '') }}</textarea>
                </div>

                <!-- Plan -->
                <div>
                    <label for="plan" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs">P</span>
                        Plan (Perencanaan)
                    </label>
                    <textarea id="plan" name="plan" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm" placeholder="Rencana terapi, tindakan, edukasi...">{{ old('plan', $formData['p'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </x-emr-split-layout>
@endsection
