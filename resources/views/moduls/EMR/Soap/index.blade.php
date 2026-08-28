@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = isset($edit_soap) && !$isView;
        
        $actionUrl = $isEdit
            ? route('emr.soap.update', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id])
            : route('emr.soap.store', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.soap.destroy', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id])
            : '';

        $formData = $formData ?? [];
        
        $titleForm = 'Form SOAP Baru';
        $subtitleForm = 'Isi form rekam medis di bawah ini.';
        if ($isEdit) {
            $titleForm = 'Edit Form SOAP';
            $subtitleForm = 'Perbarui rekam medis pasien.';
        } elseif ($isView) {
            $titleForm = 'Detail Rekam Medis (SOAP)';
            $subtitleForm = 'Detail pencatatan SOAP pasien.';
        }
    @endphp

    <x-emr-split-layout 
        titleRiwayat="Riwayat SOAP" 
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="$history_grouped ?? []" 
        routeName=""
        routeUrl="{{ url('emr/form/soap') }}"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id" 
        :formAction="$actionUrl" 
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl" 
        :emrId="($isEdit || $isView) ? $edit_soap->emr_id : ''" 
        :printUrl="$isView ? route('emr.soap.print', $edit_soap->emr_id) : ''"
        :isView="$isView"
    >
        <x-slot name="listRiwayat">
            <!-- Search Bar -->
            <div class="mb-4 relative">
                <input type="text" id="searchInput" placeholder="Cari di riwayat saat ini..." class="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @forelse($riwayat_soap as $item)
                @php
                    $details = $riwayat_details[$item->emr_id] ?? [];
                    if (!empty($details)) {
                        $data = [
                            's' => $details[env('OBJEK_ID_SUBJECTIVE')] ?? '',
                            'o' => $details[env('OBJEK_ID_OBJECTIVE')] ?? '',
                            'a' => $details[env('OBJEK_ID_ASSESSMENT')] ?? '',
                            'p' => $details[env('OBJEK_ID_PLANNING')] ?? '',
                            'i' => $details[env('OBJEK_ID_INSTRUKSI')] ?? '',
                        ];
                    } else {
                        $data = json_decode($item->data, true) ?? [];
                    }
                    $isActive = isset($edit_soap) && $edit_soap->emr_id == $item->emr_id;
                @endphp
                <div class="riwayat-item group border {{ $isActive ? 'border-blue-400 bg-blue-50 shadow-md' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md' }} rounded-xl p-4 transition-all relative mb-3">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-blue-600">
                                Tanggal Input: {{ \Carbon\Carbon::parse($item->tgl_jam)->format('d/m/Y H:i') }}</p>
                            <p class="text-sm font-semibold text-slate-800 mt-0.5">Pencatat: {{ $item->nama_pegawai ?? 'Dokter' }}</p>
                        </div>

                        @if($isActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Aktif
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-600 space-y-1 mt-3 mb-3">
                        <p><span class="font-semibold text-slate-700">S:</span> {{ Str::limit($data['s'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">O:</span> {{ Str::limit($data['o'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">A:</span> {{ Str::limit($data['a'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">P:</span> {{ Str::limit($data['p'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">I:</span> {{ Str::limit($data['i'] ?? '-', 40) }}</p>
                    </div>

                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id, 'action' => 'view']) }}" class="flex-1 inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 hover:text-slate-800 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat
                        </a>
                        <a href="{{ route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="flex-1 inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 hover:text-blue-800 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit
                        </a>
                        <form action="{{ route('emr.soap.destroy', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat SOAP ini?');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-800 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-center opacity-50">
                    <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    <p class="text-sm text-slate-500">Belum ada riwayat SOAP</p>
                </div>
            @endforelse

            @if($riwayat_soap->hasPages())
                <div class="mt-4">
                    {{ $riwayat_soap->links('components.pagination') }}
                </div>
            @endif
        </x-slot>

        {{-- Riwayat Pengkajian Pasien --}}
        @if(!empty($riwayat_pengkajian) && count($riwayat_pengkajian) > 0)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Data Pengkajian Terakhir
                    </h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-sm">
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Tekanan Darah</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_SISTOLIK')] ?? '-' }} /
                                {{ $riwayat_pengkajian[env('OBJEK_ID_DIASTOLIK')] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">mmHg</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Nadi</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_NADI')] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">x/mnt</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Suhu</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_SUHU')] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">°C</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Pernapasan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_PERNAPASAN')] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">x/mnt</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Saturasi O2</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_SATURASI')] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">%</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Berat Badan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_BERAT_BADAN')] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">kg</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Tinggi Badan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_TINGGI_BADAN')] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">cm</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">EWS / GCS</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_EWS')] ?? '-' }} /
                                {{ $riwayat_pengkajian[env('OBJEK_ID_GCS_SCORE')] ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Oksigen</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_OKSIGEN')] ?? '-' }}
                                ({{ $riwayat_pengkajian[env('OBJEK_ID_CARA_PEMBERIAN')] ?? '-' }})</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">ETT</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian[env('OBJEK_ID_ETT')] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 space-y-6">
                <!-- Subjective -->
                <div>
                    <label for="subjective"
                        class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs">S</span>
                        Subjective (Subyektif)
                    </label>
                    <textarea id="subjective" name="subjective" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm"
                        placeholder="Keluhan utama, riwayat penyakit, keluhan tambahan...">{{ old('subjective', $formData['s'] ?? '') }}</textarea>
                </div>

                <!-- Objective -->
                <div>
                    <label for="objective"
                        class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs">O</span>
                        Objective (Obyektif)
                    </label>
                    <textarea id="objective" name="objective" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors' }} text-sm"
                        placeholder="Hasil pemeriksaan fisik, tanda vital, hasil lab...">{{ old('objective', $formData['o'] ?? '') }}</textarea>
                </div>

                <!-- Assessment -->
                <div>
                    <label for="assessment"
                        class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold text-xs">A</span>
                        Assessment (Asesmen)
                    </label>
                    <textarea id="assessment" name="assessment" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors' }} text-sm"
                        placeholder="Diagnosis kerja, diagnosis banding...">{{ old('assessment', $formData['a'] ?? $assesment_terakhir) }}</textarea>
                </div>

                <!-- Plan -->
                <div>
                    <label for="plan" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs">P</span>
                        Plan (Perencanaan)
                    </label>
                    <textarea id="plan" name="plan" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors' }} text-sm"
                        placeholder="Rencana terapi, tindakan, edukasi...">{{ old('plan', $formData['p'] ?? '') }}</textarea>
                </div>

                <!-- Instruksi -->
                <div>
                    <label for="instruction"
                        class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs">I</span>
                        Instruksi (Dokter, Perawat, Dietisien, Farmasi)
                    </label>
                    <textarea id="instruction" name="instruction" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors' }} text-sm"
                        placeholder="Rencana terapi, tindakan, edukasi...">{{ old('instruction', $formData['i'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
        <script>
            document.getElementById('searchInput').addEventListener('input', function() {
                let filter = this.value.toLowerCase();
                let items = document.querySelectorAll('.riwayat-item');
                
                items.forEach(function(item) {
                    let text = item.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        </script>
    </x-emr-split-layout>
@endsection