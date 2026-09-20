@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = ($edit_konsultasi ?? null) && ! $isView;

        $actionUrl = $isEdit
            ? route('emr.form.update', ['form_name' => 'konsultasi', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_konsultasi->emr_id])
            : route('emr.form.store', ['form_name' => 'konsultasi', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.form.destroy', ['form_name' => 'konsultasi', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_konsultasi->emr_id])
            : '';

        $formData = $formData ?? [];

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Konsultasi Baru';
        $subtitleForm = 'Isi form konsultasi di bawah ini.';
        if ($isEdit) {
            $titleForm = 'Edit Konsultasi';
            $subtitleForm = 'Perbarui data konsultasi pasien.';
        } elseif ($isView) {
            $titleForm = 'Detail Konsultasi';
            $subtitleForm = 'Detail pencatatan konsultasi pasien.';
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Konsultasi"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="$history_grouped ?? []"
        routeName=""
        routeUrl="{{ url('emr/form/konsultasi') }}"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$actionUrl"
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl"
        :emrId="$edit_konsultasi->emr_id ?? ''"
        :printUrl="($isView && $edit_konsultasi) ? route('emr.konsultasi.print', $edit_konsultasi->emr_id) : ''"
        :isView="$isView"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']"
    >
        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="konsultasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$edit_konsultasi->emr_id ?? null"
                :headers="['jenis_konsultasi' => 'Jenis', 'indikasi_konsultasi' => 'Indikasi']"
            />
        </x-slot>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 space-y-6">
                <!-- Jenis Konsultasi -->
                <div>
                    <label for="jenis_konsultasi" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Jenis Konsultasi <span class="text-red-500">*</span>
                    </label>
                    <select id="jenis_konsultasi" name="jenis_konsultasi" {{ $isView ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm">
                        {!! \App\Helpers\SelectOption::render('jenis_konsultasi', old('jenis_konsultasi', $formData['jenis_konsultasi'] ?? ''), '-- Pilih Jenis Konsultasi --') !!}
                    </select>
                </div>

                <!-- Blok Rehabilitasi Medik -->
                <div id="blok_rehabilitasi" class="{{ ($formData['jenis_konsultasi'] ?? '') === 'REHABILITASI_MEDIK' ? '' : 'hidden' }}">
                    <label for="bagian_rehab_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Bagian Rehabilitasi Medik <span class="text-red-500">*</span>
                    </label>
                    <select id="bagian_rehab_id" name="bagian_rehab_id" {{ $isView ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm">
                        <option value="">-- Pilih Bagian Rehabilitasi Medik --</option>
                        @foreach($bagianRehabList as $b)
                            <option value="{{ $b->bagian_id }}" {{ old('bagian_rehab_id', $formData['bagian_rehab_id'] ?? '') == $b->bagian_id ? 'selected' : '' }}>{{ $b->nama_bagian }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Blok Konsul Layanan / Rencana Kontrol -->
                <div id="blok_layanan" class="{{ in_array($formData['jenis_konsultasi'] ?? '', ['KONSUL_LAYANAN', 'RENCANA_KONTROL']) ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bagian_tujuan_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Poliklinik / Bagian Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select id="bagian_tujuan_id" name="bagian_tujuan_id" {{ $isView ? 'disabled' : '' }}
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm">
                                <option value="">-- Pilih Poliklinik / Bagian Tujuan --</option>
                                @foreach($polikliniks as $p)
                                    <option value="{{ $p->bagian_id }}" {{ old('bagian_tujuan_id', $formData['bagian_tujuan_id'] ?? '') == $p->bagian_id ? 'selected' : '' }}>{{ $p->nama_bagian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="dokter_tujuan_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Dokter Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select id="dokter_tujuan_id" name="dokter_tujuan_id" {{ $isView ? 'disabled' : '' }}
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm">
                                <option value="">-- Pilih Dokter Tujuan --</option>
                                @foreach($dokters as $d)
                                    <option value="{{ $d->pegawai_id }}" data-bagian="{{ isset($dokterPoliMap[$d->pegawai_id]) ? implode(',', $dokterPoliMap[$d->pegawai_id]) : '' }}" {{ old('dokter_tujuan_id', $formData['dokter_tujuan_id'] ?? '') == $d->pegawai_id ? 'selected' : '' }}>{{ $d->nama_pegawai }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Hanya dokter yang berpraktik di poli tujuan yang ditampilkan.</p>
                        </div>
                    </div>
                </div>

                <!-- Blok Rencana Kontrol -->
                <div id="blok_kontrol" class="{{ ($formData['jenis_konsultasi'] ?? '') === 'RENCANA_KONTROL' ? '' : 'hidden' }}">
                    <label for="tanggal_kontrol" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Tanggal Kontrol Selanjutnya <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_kontrol" name="tanggal_kontrol" {{ $isView ? 'readonly' : '' }}
                        value="{{ old('tanggal_kontrol', $formData['tanggal_kontrol'] ?? '') }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm">
                </div>

                <!-- Indikasi -->
                <div>
                    <label for="indikasi_konsultasi" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Indikasi Konsultasi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="indikasi_konsultasi" name="indikasi_konsultasi" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm"
                        placeholder="Alasan / indikasi konsultasi...">{{ old('indikasi_konsultasi', $formData['indikasi_konsultasi'] ?? '') }}</textarea>
                </div>

                <!-- Catatan -->
                <div>
                    <label for="catatan" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                    <textarea id="catatan" name="catatan" rows="3" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm"
                        placeholder="Catatan tambahan (opsional)...">{{ old('catatan', $formData['catatan'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        @if(!$isView)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const jenis = document.getElementById('jenis_konsultasi');
                const blokRehab = document.getElementById('blok_rehabilitasi');
                const blokLayanan = document.getElementById('blok_layanan');
                const blokKontrol = document.getElementById('blok_kontrol');
                const poli = document.getElementById('bagian_tujuan_id');
                const dokter = document.getElementById('dokter_tujuan_id');

                function toggleBlok() {
                    const v = jenis.value;
                    blokRehab.classList.toggle('hidden', v !== 'REHABILITASI_MEDIK');
                    blokLayanan.classList.toggle('hidden', v !== 'KONSUL_LAYANAN' && v !== 'RENCANA_KONTROL');
                    blokKontrol.classList.toggle('hidden', v !== 'RENCANA_KONTROL');
                }

                function filterDokter() {
                    const bagianId = poli.value;
                    [...dokter.options].forEach(function (opt) {
                        if (!opt.value) {
                            opt.style.display = '';
                            return;
                        }
                        const bagians = (opt.dataset.bagian || '').split(',').filter(Boolean);
                        opt.style.display = !bagianId || bagians.includes(bagianId) ? '' : 'none';
                    });
                    if (dokter.value && [...dokter.options].find(o => o.value === dokter.value && o.style.display === 'none')) {
                        dokter.value = '';
                    }
                }

                jenis.addEventListener('change', toggleBlok);
                poli.addEventListener('change', filterDokter);

                toggleBlok();
                filterDokter();
            });
        </script>
        @endif
    </x-emr-split-layout>
@endsection