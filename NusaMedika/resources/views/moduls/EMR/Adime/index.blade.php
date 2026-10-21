@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form ADIME';
        $subtitleForm = 'Assesment - Diagnosa - Intervensi - Monitoring - Evaluasi. Seluruh field memakai ulang objek dari Asesmen Gizi (form 39).';
        $routeName = null;
        $routeUrl = url('emr/form/adime');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $opsiMonitor = \App\Http\Controllers\EMR\Adime\AdimeController::OPSI_MONITOR;

        $headers = [
            'tanggal_asesmen' => 'Tanggal',
            'diagnosa_gizi' => 'Diagnosa Gizi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat ADIME"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="$historyGrouped"
        :routeName="$routeName"
        :routeUrl="$routeUrl"
        :registrasiDetailId="$registrasiDetailId"
        :formAction="$formAction"
        :isEdit="$isEdit"
        :deleteAction="$deleteAction"
        :emrId="$emr_id"
        :isView="$isView"
        :printUrl="''"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="adime"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= TANGGAL & WAKTU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanggal &amp; Waktu Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_asesmen') border-red-500 @enderror" required>
                        @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Asesmen <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_asesmen" value="{{ $v('waktu_asesmen') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('waktu_asesmen') border-red-500 @enderror" required>
                        @error('waktu_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= A - ASSESSMENT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">A — Assessment</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-1">Assessment <span class="text-red-500">*</span></label>
                    <textarea name="assessment" rows="4" class="w-full border rounded-sm px-3 py-2 text-sm @error('assessment') border-red-500 @enderror" required>{{ $v('assessment') }}</textarea>
                    @error('assessment') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= D - DIAGNOSA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">D — Diagnosa Gizi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-1">Diagnosa Gizi <span class="text-red-500">*</span></label>
                    <textarea name="diagnosa_gizi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm @error('diagnosa_gizi') border-red-500 @enderror" required>{{ $v('diagnosa_gizi') }}</textarea>
                    @error('diagnosa_gizi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= I - INTERVENSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">I — Intervensi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-1">Intervensi <span class="text-red-500">*</span></label>
                    <textarea name="intervensi" rows="4" class="w-full border rounded-sm px-3 py-2 text-sm @error('intervensi') border-red-500 @enderror" required>{{ $v('intervensi') }}</textarea>
                    @error('intervensi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= M - MONITORING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">M — Monitoring</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-2">Aspek yang Dimonitoring</label>
                    <div class="flex flex-wrap gap-6">
                        @foreach ($opsiMonitor as $variabel => $label)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="{{ $variabel }}" value="on" class="form-checkbox"
                                    {{ ($emr_data[$variabel] ?? '') === 'on' ? 'checked' : '' }}>
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Yang tidak dicentang disimpan sebagai "off" — bukan dihapus.</p>
                    @error('monitor_asupan_makanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('monitor_hasil_lab') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('monitor_berat_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= E - EVALUASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">E — Evaluasi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-1">Evaluasi <span class="text-red-500">*</span></label>
                    <textarea name="evaluasi" rows="4" class="w-full border rounded-sm px-3 py-2 text-sm @error('evaluasi') border-red-500 @enderror" required>{{ $v('evaluasi') }}</textarea>
                    @error('evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= INSTRUKSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Instruksi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-1">Instruksi Dokter, Perawat, Dietisen, Farmasi <span class="text-red-500">*</span></label>
                    <textarea name="instruksi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm @error('instruksi') border-red-500 @enderror" required>{{ $v('instruksi') }}</textarea>
                    @error('instruksi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection