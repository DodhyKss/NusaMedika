@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Pasien Restraint';
        $subtitleForm = 'Indikasi klinis, order dokter, kebutuhan restraint non-farma/farma, kriteria penghentian & edukasi.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_restraint');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_asesmen' => 'Tgl Asesmen',
            'status_mental' => 'Status Mental',
            'nama_petugas' => 'Petugas',
        ];

        $cb = function ($name) use ($v) {
            $checked = $v($name) === '1' ? 'checked' : '';
            return '<input type="hidden" name="'.$name.'" value="0">'
                .'<input type="checkbox" name="'.$name.'" value="1" '.$checked.'>';
        };

        $gaduhItem = [
            'gaduh_1' => 'Melakukan kekerasan pada diri sendiri',
            'gaduh_2' => 'Melakukan kekerasan pada orang lain',
            'gaduh_3' => 'Impulsif / menyerang',
            'gaduh_4' => 'Perilaku tidak kooperatif',
            'gaduh_5' => 'Gaduh gelisah / amuk',
        ];
        $nonfarmItem = [
            'nonfarm_1' => 'Restraint tempat tidur / bedrails',
            'nonfarm_2' => 'Restraint tangan dan kaki',
            'nonfarm_3' => 'Restraint tangan',
            'nonfarm_4' => 'Isolasi',
            'nonfarm_5' => 'Jaket pengikat',
        ];
        $stopItem = [
            'stop_1' => 'Orientasi pada orang, tempat, lingkungan, waktu',
            'stop_2' => 'Mampu secara verbal melakukan kesepakatan untuk keamanan',
            'stop_3' => 'Mampu mengenali/mendiskusikan alternatif pertahanan',
            'stop_4' => 'Pasien sadar penuh (GCS 15)',
            'stop_5' => 'Perilaku awal tidak ditemukan',
            'stop_6' => 'Lainnya',
        ];
        $statusMental = ['Kooperatif', 'Gelisah', 'Menyerang', 'Tidak ada respon', 'Lainnya'];
        $kesadaranOpsi = ['CM', 'Somnolen', 'Apatis', 'Soporus', 'Koma'];

        $radio = function ($name, $nilai, $opsi) use ($v) {
            $html = '';
            foreach ($opsi as $opt) {
                $checked = $v($name) === $opt ? ' checked' : '';
                $html .= '<label class="inline-flex items-center gap-2 text-sm">'
                    .'<input type="radio" name="'.$name.'" value="'.$opt.'"'.$checked.'>'
                    .'<span>'.$opt.'</span></label>';
            }
            return $html;
        };
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Restraint"
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
                slug="asesmen_restraint"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ASESMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Asesmen <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_asesmen" value="{{ $v('waktu_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= GADUH GELISAH & ORDER DOKTER ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Penilaian Gaduh Gelisah &amp; Order Dokter</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Indikasi Klinis (centang bila ada)</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-w-4xl">
                            @foreach ($gaduhItem as $key => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <span class="mt-0.5">{!! $cb($key) !!}</span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @foreach (array_keys($gaduhItem) as $key)
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-500 bg-slate-50 border border-slate-200 rounded-sm px-3 py-2 max-w-4xl">
                        Apakah salah satu item di atas terpenuhi → indikasi untuk dilakukan fiksasi.
                    </p>
                    <div>
                        <label class="block text-xs font-medium mb-1">Penilaian dan Order Dokter <span class="text-red-500">*</span></label>
                        <textarea name="order_dokter" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('order_dokter') }}</textarea>
                        @error('order_dokter') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KEBUTUHAN RESTRAINT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-red-700 font-medium text-[15px]">Kebutuhan Restraint</span>
                    <span id="badge-kbutuhan" style="display:none"
                        class="text-xs font-semibold text-amber-700 bg-amber-100 border border-amber-300 rounded-full px-3 py-1">
                        Belum ada kebutuhan restraint yang dipilih
                    </span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Non Farmakologis</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-w-4xl">
                            @foreach ($nonfarmItem as $key => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <span class="mt-0.5">{!! $cb($key) !!}</span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @foreach (array_keys($nonfarmItem) as $key)
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endforeach
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Farmakologis (maksimal 10 baris)</p>
                        <div class="overflow-x-auto border border-slate-200 rounded-sm">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-xs text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left w-10">#</th>
                                        <th class="px-3 py-2 text-left">Nama Obat</th>
                                        <th class="px-3 py-2 text-left">Dosis</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <tr>
                                            <td class="px-3 py-1.5 text-slate-400">{{ $i }}</td>
                                            <td class="px-3 py-1.5">
                                                <input type="text" name="farmak_{{ $i }}" value="{{ $v('farmak_'.$i) }}" maxlength="150" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            </td>
                                            <td class="px-3 py-1.5">
                                                <input type="text" name="farmak_dosis_{{ $i }}" value="{{ $v('farmak_dosis_'.$i) }}" maxlength="100" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        @for ($i = 1; $i <= 10; $i++)
                            @error('farmak_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @error('farmak_dosis_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endfor
                        <p class="text-[11px] text-slate-500 mt-1">Minimal satu kebutuhan restraint non-farmakologis ATAU farmakologis wajib terisi.</p>
                    </div>
                </div>
            </div>

            {{-- ================= KRITERIA PENGHENTIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Pengkajian Penghentian Restraint</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-w-4xl">
                        @foreach ($stopItem as $key => $label)
                            <label class="flex items-start gap-2 text-sm">
                                <span class="mt-0.5">{!! $cb($key) !!}</span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @foreach (array_keys($stopItem) as $key)
                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @endforeach
                    <div id="blok-stop-lainnya">
                        <label class="block text-xs font-medium mb-1">Kriteria Lainnya <span class="text-red-500">*</span> (bila "Lainnya" dicentang)</label>
                        <textarea id="stop_lainnya" name="stop_lainnya" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm md:max-w-2xl">{{ $v('stop_lainnya') }}</textarea>
                        @error('stop_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PENGKAJIAN FISIK & MENTAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200">
                    <span class="text-sky-700 font-medium text-[15px]">Pengkajian Status Mental &amp; Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Status Mental <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5 mt-1">{!! $radio('status_mental', $v('status_mental'), $statusMental) !!}</div>
                        @error('status_mental') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-status-lainnya">
                        <label class="block text-xs font-medium mb-1">Status Mental Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" id="status_mental_lainnya" name="status_mental_lainnya" value="{{ $v('status_mental_lainnya') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm md:max-w-md">
                        @error('status_mental_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5 mt-1">{!! $radio('kesadaran', $v('kesadaran'), $kesadaranOpsi) !!}</div>
                        @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 max-w-4xl">
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
            </div>

            {{-- ================= INFORMASI & EDUKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-violet-50 border-b border-slate-200">
                    <span class="text-violet-700 font-medium text-[15px]">Informasi &amp; Edukasi Pasien/Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 max-w-5xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Edukasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl_edukasi" value="{{ $v('tgl_edukasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tgl_edukasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Edukasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_edukasi" value="{{ $v('jam_edukasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_edukasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Petugas <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_petugas" value="{{ $v('nama_petugas') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nama_petugas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Keluarga <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_keluarga" value="{{ $v('nama_keluarga') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nama_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="blok-melepas">
                        <p class="text-xs font-semibold text-slate-600 mb-2">Pelepasan Restraint (isi bila restraint sudah dilepas)</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl">
                            <div>
                                <label class="block text-xs font-medium mb-1">Tanggal Melepas</label>
                                <input type="date" id="tgl_melepas" name="tgl_melepas" value="{{ $v('tgl_melepas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('tgl_melepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Jam Melepas</label>
                                <input type="time" id="jam_melepas" name="jam_melepas" value="{{ $v('jam_melepas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('jam_melepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Nama Petugas yang Melepas</label>
                                <input type="text" id="nama_petugas_melepas" name="nama_petugas_melepas" value="{{ $v('nama_petugas_melepas') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('nama_petugas_melepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Tanggal melepas &amp; nama petugas wajib diisi bersamaan.</p>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function radioVal(name) {
                    var el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : '';
                }

                function cek121Restraint() {
                    var ada = false;
                    for (var i = 1; i <= 5; i++) {
                        var cb = document.querySelector('input[name="nonfarm_' + i + '"][type="checkbox"]');
                        if (cb && cb.checked) { ada = true; break; }
                    }
                    if (!ada) {
                        for (var j = 1; j <= 10; j++) {
                            var obat = document.querySelector('input[name="farmak_' + j + '"]');
                            var dosis = document.querySelector('input[name="farmak_dosis_' + j + '"]');
                            if ((obat && obat.value.trim() !== '') || (dosis && dosis.value.trim() !== '')) {
                                ada = true;
                                break;
                            }
                        }
                    }
                    var badge = document.getElementById('badge-kbutuhan');
                    if (badge) { badge.style.display = ada ? 'none' : ''; }
                }

                function sync() {
                    var status = radioVal('status_mental');
                    var blokLainnya = document.getElementById('blok-status-lainnya');
                    if (blokLainnya) { blokLainnya.style.display = status === 'Lainnya' ? '' : 'none'; }

                    var stop6 = document.querySelector('input[name="stop_6"][type="checkbox"]');
                    var blokStop = document.getElementById('blok-stop-lainnya');
                    if (blokStop) { blokStop.style.display = (stop6 && stop6.checked) ? '' : 'none'; }

                    var tgl = document.getElementById('tgl_melepas');
                    var petugas = document.getElementById('nama_petugas_melepas');
                    if (tgl && petugas) {
                        petugas.required = tgl.value !== '';
                        tgl.required = petugas.value !== '';
                    }
                }

                document.querySelectorAll('input[name="status_mental"], input[name="stop_6"], input[type="checkbox"][name^="nonfarm_"]')
                    .forEach(function (el) { el.addEventListener('change', function () { sync(); cek121Restraint(); }); });
                document.querySelectorAll('input[name^="farmak_"]').forEach(function (el) {
                    el.addEventListener('input', cek121Restraint);
                });
                var tglEl = document.getElementById('tgl_melepas');
                var petugasEl = document.getElementById('nama_petugas_melepas');
                if (tglEl) { tglEl.addEventListener('change', sync); }
                if (petugasEl) { petugasEl.addEventListener('input', sync); }

                sync();
                cek121Restraint();
            })();
        </script>
    </x-emr-split-layout>
@endsection
