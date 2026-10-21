@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Katarak';
        $subtitleForm = 'Asesmen keperawatan operasi katarak: risiko jatuh, nyeri (VAS), intervensi pre/post operasi, jadwal obat & TTV + visus harian.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_katarak');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_asesmen' => 'Tgl Asesmen',
            'jam_asesmen' => 'Jam',
            'resiko_jatuh' => 'Resiko Jatuh',
        ];

        $opsiResiko = ['Risiko Tinggi', 'Tidak Berisiko'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Katarak"
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
                slug="asesmen_katarak"
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
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Asesmen <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_asesmen" value="{{ $v('jam_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">DPJP Utama <span class="text-red-500">*</span></label>
                            <x-select_dokter
                                name="dpjp_utama"
                                id="dpjp_utama"
                                label=""
                                placeholder="-- Pilih DPJP --"
                                :selected="$v('dpjp_utama')"
                                :required="true" />
                            @error('dpjp_utama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                            <select name="kesadaran" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('kesadaran', $v('kesadaran'), '-- Pilih Kesadaran --') !!}
                            </select>
                            @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan Utama <span class="text-red-500">*</span></label>
                        <textarea name="keluhan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Keluhan utama pasien...">{{ $v('keluhan') }}</textarea>
                        @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SKRINING & ASUHAN KEPERAWATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Skrining Risiko Jatuh &amp; Asuhan Keperawatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Skrining Risiko Jatuh <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiResiko as $idx => $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" id="radio-resiko-{{ $idx }}" name="resiko_jatuh" value="{{ $opsi }}" {{ $v('resiko_jatuh') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('resiko_jatuh') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Edukasi (Skrining Risiko Jatuh) <span class="text-red-500">*</span></label>
                            <textarea name="edukasi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Edukasi risiko jatuh...">{{ $v('edukasi') }}</textarea>
                            @error('edukasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Nyeri (VAS 0–10) <span class="text-red-500">*</span></label>
                            <input type="number" name="skala_nyeri" min="0" max="10" value="{{ $v('skala_nyeri') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="0 – 10">
                            @error('skala_nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosis Keperawatan <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_keperawatan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Diagnosis keperawatan...">{{ $v('diagnosa_keperawatan') }}</textarea>
                        @error('diagnosa_keperawatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Tujuan &amp; Kriteria Hasil <span class="text-red-500">*</span></label>
                        <textarea name="tujuan_kriteria_hasil" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Tujuan & kriteria hasil...">{{ $v('tujuan_kriteria_hasil') }}</textarea>
                        @error('tujuan_kriteria_hasil') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Intervensi Pre Operasi <span class="text-red-500">*</span></label>
                        <p id="hint-intervensi" class="text-xs text-amber-600 mb-1" style="display:none">Risiko jatuh Tinggi — wajib memuat uraian intervensi.</p>
                        <textarea name="intervensi_pre_operasi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Intervensi pre operasi...">{{ $v('intervensi_pre_operasi') }}</textarea>
                        @error('intervensi_pre_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Intervensi Post Operasi <span class="text-red-500">*</span></label>
                        <textarea name="intervensi_post_operasi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Intervensi post operasi...">{{ $v('intervensi_post_operasi') }}</textarea>
                        @error('intervensi_post_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= JADWAL PEMBERIAN OBAT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Jadwal Pemberian Obat</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border px-3 py-2 text-xs font-semibold w-12">No</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Jam</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Nama Obat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 5; $i++)
                                    <tr>
                                        <td class="border px-3 py-2 text-center">{{ $i }}</td>
                                        <td class="border px-2 py-1">
                                            <input type="time" name="jam_pemberian_obat_{{ $i }}" value="{{ $v('jam_pemberian_obat_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('jam_pemberian_obat_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="nama_obat_{{ $i }}" value="{{ $v('nama_obat_'.$i) }}" maxlength="2000" class="w-full border rounded-sm px-2 py-1.5 text-sm" placeholder="Nama obat...">
                                            @error('nama_obat_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Baris obat bersifat opsional — dikosongkan bila tidak ada pemberian obat terjadwal.</p>
                </div>
            </div>

            {{-- ================= TTV & EVALUASI PASCA OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">TTV &amp; Evaluasi Pasca Operasi (Harian)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border px-3 py-2 text-xs font-semibold w-10">No</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Sistolik</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Diastolik</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Nadi</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Suhu</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Pernapasan</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Skala Nyeri</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Visus Kanan</th>
                                    <th class="border px-3 py-2 text-xs font-semibold">Visus Kiri</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 5; $i++)
                                    <tr>
                                        <td class="border px-3 py-2 text-center">{{ $i }}</td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="sistolik_{{ $i }}" min="0" value="{{ $v('sistolik_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('sistolik_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="diastolik_{{ $i }}" min="0" value="{{ $v('diastolik_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('diastolik_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="nadi_{{ $i }}" min="0" value="{{ $v('nadi_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('nadi_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="suhu_{{ $i }}" min="0" step="0.1" value="{{ $v('suhu_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('suhu_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="pernapasan_{{ $i }}" min="0" value="{{ $v('pernapasan_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                                            @error('pernapasan_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="number" name="skala_nyeri_{{ $i }}" min="0" max="10" value="{{ $v('skala_nyeri_'.$i) }}" class="w-full border rounded-sm px-2 py-1.5 text-sm" placeholder="0–10">
                                            @error('skala_nyeri_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="visus_kanan_{{ $i }}" value="{{ $v('visus_kanan_'.$i) }}" maxlength="2000" class="w-full border rounded-sm px-2 py-1.5 text-sm" placeholder="mis. 6/9">
                                            @error('visus_kanan_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="border px-2 py-1">
                                            <input type="text" name="visus_kiri_{{ $i }}" value="{{ $v('visus_kiri_'.$i) }}" maxlength="2000" class="w-full border rounded-sm px-2 py-1.5 text-sm" placeholder="mis. 6/9">
                                            @error('visus_kiri_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Skala Nyeri memakai skala VAS 0–10; visus diisi setelah operasi (mis. 6/9, CFM).</p>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var hint = document.getElementById('hint-intervensi');
                var radios = Array.prototype.slice.call(document.querySelectorAll('[name="resiko_jatuh"]'));

                function syncHint() {
                    var aktif = radios.find(function (r) { return r.checked; });
                    if (hint) { hint.style.display = aktif && aktif.value === 'Risiko Tinggi' ? '' : 'none'; }
                }

                radios.forEach(function (r) { r.addEventListener('change', syncHint); });
                syncHint();
            })();
        </script>
    </x-emr-split-layout>
@endsection
