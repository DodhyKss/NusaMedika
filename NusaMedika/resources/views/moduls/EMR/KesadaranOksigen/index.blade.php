@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Kesadaran & Pemberian Oksigen';
        $subtitleForm = 'Penilaian Glasgow Coma Scale, tingkat kesadaran, dan pemberian oksigen beserta flow rate serta saturasi.';
        $routeName = null;
        $routeUrl = url('emr/form/kesadaran_oksigen');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        // Nilai "ya" / "tidak" untuk radio: string kosong berarti belum dinilai.
        $ya = fn ($key) => ((string) $v($key)) === 'Ya';
        $tidak = fn ($key) => ((string) $v($key)) === 'Tidak';
        $adaOksigen = ((string) $v('pemberian_o2')) === 'Oksigen';

        // Tiga komponen GCS beserta deskripsi skornya.
        $komponenGcs = [
            'gcs_e' => [
                'judul' => 'Eye (E)',
                'maks' => 4,
                'deskripsi' => \App\Http\Controllers\EMR\KesadaranOksigen\KesadaranOksigenController::GCS_EYE,
            ],
            'gcs_m' => [
                'judul' => 'Motorik (M)',
                'maks' => 6,
                'deskripsi' => \App\Http\Controllers\EMR\KesadaranOksigen\KesadaranOksigenController::GCS_MOTORIK,
            ],
            'gcs_v' => [
                'judul' => 'Verbal (V)',
                'maks' => 5,
                'deskripsi' => \App\Http\Controllers\EMR\KesadaranOksigen\KesadaranOksigenController::GCS_VERBAL,
            ],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Kesadaran & Oksigen"
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
                slug="kesadaran_oksigen"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'gcs_jumlah' => 'GCS',
                    'kesadaran' => 'Kesadaran',
                    'pemberian_o2' => 'Oksigen',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS OBSERVASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Observasi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="tanggal_kesadaran" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_kesadaran" name="tanggal_kesadaran"
                                   value="{{ substr((string) $v('tanggal_kesadaran'), 0, 10) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('tanggal_kesadaran')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_kesadaran" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_kesadaran" name="waktu_kesadaran" step="60"
                                   value="{{ $v('waktu_kesadaran') ? substr((string) $v('waktu_kesadaran'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('waktu_kesadaran')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <label class="flex items-center gap-2 text-slate-700 font-medium">
                                <input type="checkbox" id="dpo" name="dpo" value="Ya"
                                       @checked($ya('dpo'))
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                Dalam Pengaruh Obat (DPO)
                            </label>
                            @error('dpo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= GLASGOW COMA SCALE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Glasgow Coma Scale</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach ($komponenGcs as $name => $gcs)
                            <div class="p-4 border border-slate-200 rounded-lg">
                                <div class="font-bold text-slate-800 mb-2">
                                    {{ $gcs['judul'] }} <span class="text-red-500">*</span>
                                </div>
                                <div class="space-y-1.5">
                                    @for ($skor = $gcs['maks']; $skor >= 1; $skor--)
                                        <label class="flex items-start gap-2 text-slate-700">
                                            <input type="radio" name="{{ $name }}" value="{{ $skor }}"
                                                   @checked((int) $v($name) === $skor)
                                                   class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                            <span><span class="font-bold">{{ $skor }}</span> - {{ $gcs['deskripsi'][$skor] }}</span>
                                        </label>
                                    @endfor
                                </div>
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-4 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Total GCS</div>
                                <div id="gcsTotal" class="text-xl font-bold text-slate-700">
                                    {{ old('gcs_jumlah', $emr_data['gcs_jumlah'] ?? '-') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Tingkat Kesadaran</div>
                                <div id="gcsKesadaran" class="text-sm font-semibold text-slate-700">
                                    {{ old('kesadaran', $emr_data['kesadaran'] ?? '-') }}
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 self-center">
                            Total GCS dan tingkat kesadaran dihitung ulang di server; nilai yang dikirim browser diabaikan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ================= PEMBERIAN OKSIGEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pemberian Oksigen</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="pemberian_o2" class="block font-bold text-slate-800 mb-1.5">
                                Pemberian Oksigen <span class="text-red-500">*</span>
                            </label>
                            <select id="pemberian_o2" name="pemberian_o2" class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('oksigen', $v('pemberian_o2'), '-- Pilih --') !!}
                            </select>
                            @error('pemberian_o2')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap-flow" class="{{ $adaOksigen ? '' : 'hidden' }}">
                            <label for="flow_rate" class="block font-bold text-slate-800 mb-1.5">
                                Flow Rate (L/menit)
                            </label>
                            <input type="number" step="any" min="0" max="60" id="flow_rate" name="flow_rate"
                                   value="{{ $v('flow_rate') }}" placeholder="contoh: 3"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('flow_rate')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap-cara" class="{{ $adaOksigen ? '' : 'hidden' }} md:col-span-2">
                            <label for="cara_oksigen" class="block font-bold text-slate-800 mb-1.5">Cara Pemberian Oksigen</label>
                            <select id="cara_oksigen" name="cara_oksigen" class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('cara_pemberian_o2', $v('cara_oksigen'), '-- Pilih Cara --') !!}
                            </select>
                            @error('cara_oksigen')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="block font-bold text-slate-800 mb-1.5">
                                Endotracheal Tube (ETT) <span class="text-red-500">*</span>
                            </div>
                            <div class="flex items-center gap-4 h-[42px]">
                                <label class="flex items-center gap-2 text-slate-700">
                                    <input type="radio" name="ett" value="Ya" @checked($ya('ett'))
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"> Ya
                                </label>
                                <label class="flex items-center gap-2 text-slate-700">
                                    <input type="radio" name="ett" value="Tidak" @checked($tidak('ett'))
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"> Tidak
                                </label>
                            </div>
                            @error('ett')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap-persen" class="{{ $ya('ett') ? '' : 'hidden' }}">
                            <label for="persentase_oksigen" class="block font-bold text-slate-800 mb-1.5">Oksigen Persen (%)</label>
                            <input type="number" step="any" min="21" max="100" id="persentase_oksigen" name="persentase_oksigen"
                                   value="{{ $v('persentase_oksigen') }}" placeholder="contoh: 40"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('persentase_oksigen')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="saturasi" class="block font-bold text-slate-800 mb-1.5">
                                Saturasi Oksigen (%) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="saturasi" name="saturasi" min="50" max="100"
                                   value="{{ $v('saturasi') }}" placeholder="contoh: 98"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('saturasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KETERANGAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keterangan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <textarea id="keterangan" name="keterangan" rows="3"
                              class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tampil/sembunyi WAJIB lewat style.display DAN class `hidden`.
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

        // Field yang tidak berlaku DILAPAS atribut `name`-nya supaya tidak
        // terkirim sama sekali (bukan `disabled`).
        function lepasNama(el, tampil) {
            if (!el) return;
            if (!el.dataset.namaAsli) el.dataset.namaAsli = el.getAttribute('name') || '';
            if (tampil) el.setAttribute('name', el.dataset.namaAsli);
            else el.removeAttribute('name');
        }

        function pilihan(name) {
            var el = document.querySelector('input[name="' + name + '"]:checked');
            return el ? el.value : '';
        }

        function syncOksigen() {
            var o2 = (document.getElementById('pemberian_o2') || {}).value || '';
            var pakai = o2 === 'Oksigen';

            tampilkan(document.getElementById('wrap-flow'), pakai, 'block');
            tampilkan(document.getElementById('wrap-cara'), pakai, 'block');

            // Tanpa oksigen, flow rate & cara pemberian tidak dikirim.
            lepasNama(document.getElementById('flow_rate'), pakai);
            lepasNama(document.getElementById('cara_oksigen'), pakai);
        }

        function syncEtt() {
            var ett = pilihan('ett') === 'Ya';
            tampilkan(document.getElementById('wrap-persen'), ett, 'block');
            lepasNama(document.getElementById('persentase_oksigen'), ett);
        }

        var selectO2 = document.getElementById('pemberian_o2');
        if (selectO2) selectO2.addEventListener('change', syncOksigen);

        document.querySelectorAll('input[name="ett"]').forEach(function (el) {
            el.addEventListener('change', syncEtt);
        });

        // Total GCS hanya tampilan; server menghitung ulang sendiri.
        function hitungGcs() {
            var total = 0;
            var lengkap = true;
            ['gcs_e', 'gcs_m', 'gcs_v'].forEach(function (name) {
                var el = document.querySelector('input[name="' + name + '"]:checked');
                if (!el) { lengkap = false; return; }
                total += parseInt(el.value, 10);
            });

            var out = document.getElementById('gcsTotal');
            if (out) out.textContent = lengkap ? total : '-';

            var sadar = document.getElementById('gcsKesadaran');
            if (!sadar) return;
            if (!lengkap) { sadar.textContent = '-'; return; }
            if (total === 3) sadar.textContent = 'Coma';
            else if (total <= 6) sadar.textContent = 'Soporus';
            else if (total <= 9) sadar.textContent = 'Somnolen';
            else if (total <= 11) sadar.textContent = 'Delirium';
            else if (total <= 14) sadar.textContent = 'Apatis';
            else if (total === 15) sadar.textContent = 'Compos Mentis';
            else sadar.textContent = '-';
        }

        document.querySelectorAll('input[name="gcs_e"], input[name="gcs_m"], input[name="gcs_v"]').forEach(function (el) {
            el.addEventListener('change', hitungGcs);
        });

        hitungGcs();
        syncOksigen();
        syncEtt();
    });
    </script>

@endsection