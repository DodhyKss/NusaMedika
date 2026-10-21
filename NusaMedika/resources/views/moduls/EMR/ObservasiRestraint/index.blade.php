@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Observasi Restraint';
        $subtitleForm = 'Observasi berkala selama restraint — kondisi fisik, cedera, asupan & posisi fiksasi per ronde.';
        $routeName = null;
        $routeUrl = url('emr/form/observasi_restraint');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'waktu_observasi' => 'Waktu',
            'sistolik' => 'TD',
            'kognitif' => 'Kognitif',
        ];

        $daftarRonde = array_map('intval', array_keys($rondes));
        sort($daftarRonde);
        $rondeUrl = fn (int $n) => url('emr/form/observasi_restraint/'.$registrasiDetailId.'/'.$emr_id).'?ronde='.$n;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Observasi Restraint"
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
                slug="observasi_restraint"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        {{-- ================= NAVIGASI RONDE ================= --}}
        @if ($isEdit)
            <div class="mb-3 border border-slate-200 rounded-sm shadow-sm bg-white p-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Ronde:</span>
                        @foreach ($daftarRonde as $n)
                            <a href="{{ $rondeUrl($n) }}"
                                class="px-3 py-1.5 text-xs font-medium rounded-full border {{ $n === $rondeAktif ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                                Ronde {{ $n }}
                            </a>
                        @endforeach
                    </div>
                    @if (! $isView)
                        <a href="{{ $rondeUrl($rondeBaru) }}"
                            class="px-3 py-1.5 text-xs font-medium border border-emerald-500 text-emerald-700 rounded-sm hover:bg-emerald-50">
                            + Tambah Ronde {{ $rondeBaru }}
                        </a>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Ronde aktif: <strong>{{ $rondeAktif }}</strong> — menyimpan menimpa isi ronde ini saja. Observasi idealnya minimal tiap 2 jam.</p>
            </div>
        @endif

        {{-- Input ronde ikut form milik x-emr-split-layout (jangan membuat
             <form> bersarang — parser HTML mengabaikan tag form bersarang). --}}
        @if ($isEdit)
            <input type="hidden" name="ronde" value="{{ $rondeAktif }}">
        @endif

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS RONDE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Observasi Ronde {{ $rondeAktif }}</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Observasi <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_observasi" value="{{ $v('waktu_observasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_observasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-5 gap-4 max-w-4xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Sistolik <span class="text-red-500">*</span></label>
                        <input type="number" name="sistolik" value="{{ $v('sistolik') }}" min="50" max="300" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diastolik <span class="text-red-500">*</span></label>
                        <input type="number" name="diastolik" value="{{ $v('diastolik') }}" min="20" max="200" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nadi <span class="text-red-500">*</span></label>
                        <input type="number" name="nadi" value="{{ $v('nadi') }}" min="20" max="250" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pernapasan <span class="text-red-500">*</span></label>
                        <input type="number" name="pernapasan" value="{{ $v('pernapasan') }}" min="5" max="80" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                        <input type="number" name="suhu" value="{{ $v('suhu') }}" min="30" max="45" step="0.1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= OBSERVASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sky-700 font-medium text-[15px]">Observasi Selama Restraint</span>
                    <span id="badge-luka" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Tanda cedera terdeteksi — evaluasi &amp; lapor
                    </span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Fungsi Kognitif <span class="text-red-500">*</span></label>
                            <input type="text" name="kognitif" value="{{ $v('kognitif') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Tenang, Bingung, Tidak kooperatif">
                            @error('kognitif') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanda Cedera / Luka <span class="text-red-500">*</span></label>
                            <input type="text" name="luka" value="{{ $v('luka') }}" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Tidak ada, Luka lecet ...">
                            @error('luka') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Asupan Makanan/Minuman <span class="text-red-500">*</span></label>
                            <input type="text" name="asupan_makanan" value="{{ $v('asupan_makanan') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('asupan_makanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Posisi Fiksasi <span class="text-red-500">*</span></label>
                            <input type="text" name="posisi_fiksasi" value="{{ $v('posisi_fiksasi') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('posisi_fiksasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">PANSS-EC (Diisi Dokter)</label>
                        <textarea name="panses_ec" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('panses_ec') }}</textarea>
                        @error('panses_ec') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek122Ronde() {
                    var luka = document.querySelector('input[name="luka"]');
                    var badge = document.getElementById('badge-luka');
                    if (!luka || !badge) { return; }
                    var ada = luka.value.trim() !== '' && !/^(tidak ada|tanpa luka|-|none|n\/a)$/i.test(luka.value.trim());
                    badge.style.display = ada ? '' : 'none';
                }

                var lukaEl = document.querySelector('input[name="luka"]');
                if (lukaEl) {
                    lukaEl.addEventListener('input', cek122Ronde);
                    cek122Ronde();
                }
            })();
        </script>
    </x-emr-split-layout>
@endsection
