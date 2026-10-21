@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = isset($edit_soap) && !$isView;
        
        $actionUrl = $isEdit
            ? route('emr.form.update', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id])
            : route('emr.form.store', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.form.destroy', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id])
            : '';

        $formData = $formData ?? [];

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        
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
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']"
    >
        <x-slot name="listRiwayat">
            <x-emr-history-table 
                slug="soap" 
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id" 
                :current-emr-id="$edit_soap->emr_id ?? null" 
                :headers="['assessment' => 'Assessment', 'planning' => 'Plan']" 
            />
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
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['sistolik'] ?? '-' }} /
                                {{ $riwayat_pengkajian['diastolik'] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">mmHg</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Nadi</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['nadi'] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">x/mnt</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Suhu</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['suhu'] ?? '-' }} <span
                                    class="text-xs text-slate-400 font-normal">°C</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Pernapasan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['pernapasan'] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">x/mnt</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Saturasi O2</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['saturasi'] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">%</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Berat Badan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['berat_badan'] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">kg</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Tinggi Badan</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['tinggi_badan'] ?? '-' }}
                                <span class="text-xs text-slate-400 font-normal">cm</span></p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">EWS / GCS</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['ews'] ?? '-' }} /
                                {{ $riwayat_pengkajian['gcs_score'] ?? '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">Oksigen</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['oksigen'] ?? '-' }}
                                ({{ $riwayat_pengkajian['cara_pemberian'] ?? '-' }})</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-slate-500 text-xs">ETT</p>
                            <p class="font-medium text-slate-800">{{ $riwayat_pengkajian['ett'] ?? '-' }}</p>
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