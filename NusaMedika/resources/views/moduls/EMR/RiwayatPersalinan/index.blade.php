@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Riwayat Persalinan Sekarang';
        $subtitleForm = 'Rekap episode persalinan berjalan — penolong, durasi kala, kondisi ketuban, data bayi, plasenta, dan keadaan ibu.';
        $routeName = null;
        $routeUrl = url('emr/form/riwayat_persalinan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'penolong_persalinan' => 'Penolong',
            'durasi_kala_1' => 'Kala I',
            'durasi_kala_2' => 'Kala II',
            'durasi_kala_3' => 'Kala III',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Persalinan Sekarang"
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
                slug="riwayat_persalinan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= PENOLONG & DURASI KALA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penolong Persalinan &amp; Durasi Kala</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Penolong Persalinan <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penolong_persalinan" value="Dokter Obgyn" {{ $v('penolong_persalinan') === 'Dokter Obgyn' ? 'checked' : '' }}> Dokter Obgyn</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penolong_persalinan" value="Bidan" {{ $v('penolong_persalinan') === 'Bidan' ? 'checked' : '' }}> Bidan</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penolong_persalinan" value="Lainnya" {{ $v('penolong_persalinan') === 'Lainnya' ? 'checked' : '' }}> Lainnya</label>
                        </div>
                        @error('penolong_persalinan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-penolong-lain" style="{{ $v('penolong_persalinan') === 'Lainnya' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Keterangan Penolong Lain <span class="text-red-500">*</span></label>
                        <input type="text" name="penolong_persalinan_lain" value="{{ $v('penolong_persalinan_lain') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('penolong_persalinan_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Durasi Kala I (HH:MM) <span class="text-red-500">*</span></label>
                            <input type="text" name="durasi_kala_1" value="{{ $v('durasi_kala_1') }}" placeholder="00:45"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('durasi_kala_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Durasi Kala II (HH:MM) <span class="text-red-500">*</span></label>
                            <input type="text" name="durasi_kala_2" value="{{ $v('durasi_kala_2') }}" placeholder="00:10"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('durasi_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Durasi Kala III (HH:MM) <span class="text-red-500">*</span></label>
                            <input type="text" name="durasi_kala_3" value="{{ $v('durasi_kala_3') }}" placeholder="00:15"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('durasi_kala_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KETUBAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ketuban</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-2">Ketuban <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="ketuban" value="Pecah" {{ $v('ketuban') === 'Pecah' ? 'checked' : '' }}> Pecah</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="ketuban" value="Dipecahkan" {{ $v('ketuban') === 'Dipecahkan' ? 'checked' : '' }}> Dipecahkan</label>
                            </div>
                            @error('ketuban') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-2">Warna Ketuban <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="warna_ketuban" value="Jernih" class="warna-ketuban" {{ $v('warna_ketuban') === 'Jernih' ? 'checked' : '' }}> Jernih</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="warna_ketuban" value="Keruh" class="warna-ketuban" {{ $v('warna_ketuban') === 'Keruh' ? 'checked' : '' }}> Keruh</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="warna_ketuban" value="Hijau" class="warna-ketuban" {{ $v('warna_ketuban') === 'Hijau' ? 'checked' : '' }}> Hijau</label>
                            </div>
                            @error('warna_ketuban') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="blok-ketuban-lain" style="{{ $v('warna_ketuban') === 'Hijau' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Keterangan Ketuban (meconium) <span class="text-red-500">*</span></label>
                        <input type="text" name="ketuban_lain" value="{{ $v('ketuban_lain') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('ketuban_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-amber-600 mt-1">Warna hijau = meconium-stained liquor — peringatan IUGR/aspiksia.</p>
                    </div>
                </div>
            </div>

            {{-- ================= KELAHIRAN KALA II (DATA BAYI) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kelahiran Kala II — Data Bayi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Persalinan Kala II <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_persalinan_kala_2" value="{{ $v('jam_persalinan_kala_2') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_persalinan_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Lahir Bayi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir_bayi" value="{{ $v('tanggal_lahir_bayi') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_lahir_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="jenis_kelamin_kala_2" value="L" {{ $v('jenis_kelamin_kala_2') === 'L' ? 'checked' : '' }}> L</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="jenis_kelamin_kala_2" value="P" {{ $v('jenis_kelamin_kala_2') === 'P' ? 'checked' : '' }}> P</label>
                            </div>
                            @error('jenis_kelamin_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">AS (A/S) <span class="text-red-500">*</span></label>
                            <input type="text" name="as_bayi" value="{{ $v('as_bayi') }}" maxlength="1"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('as_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="berat_badan_kala_2" value="{{ $v('berat_badan_kala_2') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('berat_badan_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_badan_kala_2" value="{{ $v('tinggi_badan_kala_2') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tinggi_badan_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lingkar Kepala (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="lingkar_kepala_kala_2" value="{{ $v('lingkar_kepala_kala_2') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lingkar_kepala_kala_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kelainan Bayi <span class="text-red-500">*</span></label>
                        <input type="text" name="kelainan_bayi" value="{{ $v('kelainan_bayi') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('kelainan_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PLASENTA & KEADAAN IBU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Plasenta &amp; Keadaan Ibu</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Plasenta Lahir <span class="text-red-500">*</span></label>
                            <input type="text" name="plasenta_lahir" value="{{ $v('plasenta_lahir') }}"
                                placeholder="lengkap / kotiledon tertinggal"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('plasenta_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Infark Plasenta <span class="text-red-500">*</span></label>
                            <input type="text" name="infark_plasenta" value="{{ $v('infark_plasenta') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('infark_plasenta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Keadaan Umum Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="keadaan_umum_ibu" value="{{ $v('keadaan_umum_ibu') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('keadaan_umum_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kehilangan Darah (cc) <span class="text-red-500">*</span></label>
                            <input type="number" min="0" name="kehilangan_darah" value="{{ $v('kehilangan_darah') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('kehilangan_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-amber-600 mt-1" id="peringatan-darah" style="{{ (float) $v('kehilangan_darah') > 500 ? '' : 'display:none' }}">
                                Pendarahan &gt; 500 cc — perlu perhatian.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                // Toggle "Penolong Lain".
                document.querySelectorAll('input[name="penolong_persalinan"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        document.getElementById('blok-penolong-lain').style.display = this.value === 'Lainnya' ? '' : 'none';
                    });
                });
                // Toggle "Ketuban Lain" saat warna = Hijau.
                document.querySelectorAll('.warna-ketuban').forEach(function (r) {
                    r.addEventListener('change', function () {
                        document.getElementById('blok-ketuban-lain').style.display = this.value === 'Hijau' ? '' : 'none';
                    });
                });
                // Peringatan kehilangan darah > 500.
                var darah = document.querySelector('input[name="kehilangan_darah"]');
                var peringatan = document.getElementById('peringatan-darah');
                if (darah && peringatan) {
                    darah.addEventListener('input', function () {
                        peringatan.style.display = (parseFloat(this.value) > 500) ? '' : 'none';
                    });
                }
            })();
        </script>
    </x-emr-split-layout>
@endsection
