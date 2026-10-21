@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form DAR (D-Rekognisi)';
        $subtitleForm = 'Catatan kejadian untuk keperluan hukum dan forensik saat penanganan pasien.';
        $routeName = null;
        $routeUrl = url('emr/form/dar');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $pilihMeninggal = (string) $v('meninggal') !== '';
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat DAR"
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
                slug="dar"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'tanggal_dar' => 'Tanggal DAR',
                    'dasar_laporan' => 'Dasar Laporan',
                    'kesimpulan_dar' => 'Kesimpulan',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= RINGKASAN KLINIS (READ-ONLY) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <span class="text-slate-600 font-medium text-[15px]">Ringkasan Klinis (Triase / Pengkajian)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    @if (empty($ringkasan))
                        <p class="text-slate-400">Belum ada data triase atau pengkajian awal pada kunjungan ini.</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($ringkasan as $label => $isi)
                                <div>
                                    <div class="text-[11px] uppercase tracking-wider text-slate-500">{{ $label }}</div>
                                    <div class="text-sm font-semibold text-slate-700 break-words">{{ $isi }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-3 text-xs text-slate-400">
                        Panel ini hanya tampilan. Isian DAR tetap ditulis sendiri pada panel di bawah.
                    </p>
                </div>
            </div>

            {{-- ================= IDENTITAS KEJADIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Kejadian</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_dar" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal DAR <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_dar" name="tanggal_dar"
                                   value="{{ $v('tanggal_dar') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_dar')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="dasar_laporan" class="block font-bold text-slate-800 mb-1.5">
                                Dasar Laporan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="dasar_laporan" name="dasar_laporan"
                                   value="{{ $v('dasar_laporan') }}"
                                   placeholder="Contoh: pasien dibawa petugas atas permintaan aparat setempat karena potencialmente menjadi korban kekerasan"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                            @error('dasar_laporan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800">
                        Tulis uraian faktual apa adanya. Dilarang menyalin hasil pemeriksaan
                        dari berkas lain ke dalam isian DAR ini, dan sebaiknya tidak memakai
                        karakter kutip tunggal maupun tanda petik ganda.
                    </div>
                </div>
            </div>

            {{-- ================= KRONOLOGI KEJADIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kronologi Kejadian</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="kronologi_kejadian" class="block font-bold text-slate-800 mb-1.5">
                            Kronologi Kejadian <span class="text-red-500">*</span>
                        </label>
                        <textarea id="kronologi_kejadian" name="kronologi_kejadian" rows="5"
                                  placeholder="Tulis urutan waktu kejadian, sumber informasi, dan pernyataan pasien atau pemberi berita"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('kronologi_kejadian') }}</textarea>
                        @error('kronologi_kejadian')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TEMUAN & KESIMPULAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Temuan Pemeriksaan &amp; Kesimpulan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="temuan_pemeriksaan" class="block font-bold text-slate-800 mb-1.5">
                            Temuan Pemeriksaan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="temuan_pemeriksaan" name="temuan_pemeriksaan" rows="4"
                                  placeholder="Uraikan temuan objektif beserta letaknya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('temuan_pemeriksaan') }}</textarea>
                        @error('temuan_pemeriksaan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="kesimpulan_dar" class="block font-bold text-slate-800 mb-1.5">
                            Kesimpulan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="kesimpulan_dar" name="kesimpulan_dar" rows="4"
                                  placeholder="Tulis kesimpulan kejadian. Bila kesimpulan menyatakan pasien meninggal, isi klasifikasi kematian di bawah ini."
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('kesimpulan_dar') }}</textarea>
                        @error('kesimpulan_dar')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Klasifikasi kematian: tampil bila kesimpulan menyebut kematian --}}
                    <div id="wrap-meninggal" class="{{ $pilihMeninggal ? '' : 'hidden' }}">
                        <label for="meninggal" class="block font-bold text-slate-800 mb-1.5">Klasifikasi Kematian</label>
                        <select id="meninggal" name="meninggal"
                                class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                            {!! \App\Helpers\SelectOption::render('penyebab_kematian', $v('meninggal'), '-- Pilih --') !!}
                        </select>
                        @error('meninggal')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TINDAKAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tindakan yang Dilakukan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="tindakan_dar" class="block font-bold text-slate-800 mb-1.5">
                            Tindakan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="tindakan_dar" name="tindakan_dar" rows="4"
                                  placeholder="Tulis tindakan yang dilakukan petugas, waktu pelaksanaan, dan hasil tindakannya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('tindakan_dar') }}</textarea>
                        @error('tindakan_dar')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tampil/sembunyi harus lewat style.display + lepas class "hidden"
            // (lihat AGENTS.md - utility .hidden bisa dikalahkan utility display lain).
            function tampilkan(el, tampil, display) {
                if (!el) return;
                if (tampil) {
                    el.classList.remove('hidden');
                    el.style.display = display;
                } else {
                    el.classList.add('hidden');
                    el.style.display = 'none';
                }
            }

            var kesimpulan = document.getElementById('kesimpulan_dar');
            var wrapMeninggal = document.getElementById('wrap-meninggal');

            function syncMeninggal() {
                if (!wrapMeninggal) return;
                var isi = ((kesimpulan ? kesimpulan.value : '') || '').toLowerCase();
                var sudahAda = wrapMeninggal.querySelector('select');
                var terpilih = sudahAda && sudahAda.value !== '';
                tampilkan(wrapMeninggal, terpilih || isi.indexOf('meninggal') !== -1, 'block');
            }

            if (kesimpulan) {
                kesimpulan.addEventListener('input', syncMeninggal);
            }
            if (wrapMeninggal) {
                var selectMeninggal = wrapMeninggal.querySelector('select');
                if (selectMeninggal) {
                    selectMeninggal.addEventListener('change', syncMeninggal);
                }
            }
            syncMeninggal();
        });
    </script>

@endsection