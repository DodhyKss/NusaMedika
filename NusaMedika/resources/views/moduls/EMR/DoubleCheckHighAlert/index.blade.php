@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Double Check Obat High Alert';
        $subtitleForm = 'Verifikasi 5 benar oleh dua perawat untuk setiap obat high alert + kros-check identitas/jumlah/jenis obat.';
        $routeName = null;
        $routeUrl = url('emr/form/double_check_high_alert');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_pemberian' => 'Tanggal',
            'waktu_pemberian' => 'Waktu',
        ];

        $maksBaris = \App\Http\Controllers\EMR\DoubleCheckHighAlert\DoubleCheckHighAlertController::MAKS_BARIS;
        $kolom5Benar = \App\Http\Controllers\EMR\DoubleCheckHighAlert\DoubleCheckHighAlertController::KOLOM_5_BENAR;
        $label5Benar = [
            'identitas' => 'Identitas Pasien',
            'obat' => 'Obat & Dosis',
            'dosis' => 'Dosis',
            'cara' => 'Cara Pemberian',
            'waktu' => 'Waktu Pemberian',
        ];
        $opsiVerifikasi = ['SESUAI', 'TIDAK'];

        // Nilai tersimpan per baris untuk diisi JS.
        $barisTersimpan = [];
        for ($b = 1; $b <= $maksBaris; $b++) {
            $baris = ['ha_obat' => $emr_data['ha_obat_'.$b] ?? ''];
            foreach ($kolom5Benar as $kolom) {
                $baris['ha_p1_'.$kolom] = $emr_data['ha_p1_'.$kolom.'_'.$b] ?? '';
                $baris['ha_p2_'.$kolom] = $emr_data['ha_p2_'.$kolom.'_'.$b] ?? '';
            }
            $barisTersimpan[$b] = $baris;
        }

        // Error guard server (cekBaris) untuk checkbox dinamis.
        $errBaris = array_filter(
            $errors->messages(),
            fn ($k) => (bool) preg_match('/^ha_(p1|p2)_[a-z]+_\d+$/', $k),
            ARRAY_FILTER_USE_KEY
        );

        // Dipisah dua variabel — argumen @json dengan koma internal (array_map
        // + arrow fn) dipecah Blade dan menghasilkan ParseError.
        $kolomP1Ha = array_map(fn ($k) => 'ha_p1_'.$k, $kolom5Benar);
        $kolomP2Ha = array_map(fn ($k) => 'ha_p2_'.$k, $kolom5Benar);
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Double Check High Alert"
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
                slug="double_check_high_alert"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Pemberian</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pemberian <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pemberian" value="{{ $v('tanggal_pemberian') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pemberian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Pemberian <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_pemberian" value="{{ $v('waktu_pemberian') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_pemberian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= VERIFIKASI GANDA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Verifikasi Ganda Identitas / Jumlah / Jenis Obat</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse max-w-3xl">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-3 py-2 border">Butir</th>
                                    <th class="px-3 py-2 border w-44">Perawat 1 <span class="text-red-500">*</span></th>
                                    <th class="px-3 py-2 border w-44">Perawat 2 <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (['identitas' => 'Identitas Pasien', 'jumlah' => 'Jumlah Obat', 'jenis' => 'Jenis Obat'] as $key => $label)
                                    <tr>
                                        <td class="px-3 py-2 border">{{ $label }}</td>
                                        <td class="px-3 py-2 border">
                                            <select name="cek_{{ $key }}_p1" class="w-full border rounded-sm px-2 py-1.5">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($opsiVerifikasi as $opsi)
                                                    <option value="{{ $opsi }}" {{ $v('cek_'.$key.'_p1') === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                            @error('cek_'.$key.'_p1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2 border">
                                            <select name="cek_{{ $key }}_p2" class="w-full border rounded-sm px-2 py-1.5">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($opsiVerifikasi as $opsi)
                                                    <option value="{{ $opsi }}" {{ $v('cek_'.$key.'_p2') === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                            @error('cek_'.$key.'_p2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <x-select_pegawai
                            :selected="$v('perawat_1_id')"
                            name="perawat_1_id"
                            id="perawat_1_id"
                            label="Perawat Pemeriksa 1"
                            placeholder="-- Pilih Perawat --"
                            :profesiId="2"
                            :required="true" />
                        <x-select_pegawai
                            :selected="$v('perawat_2_id')"
                            name="perawat_2_id"
                            id="perawat_2_id"
                            label="Perawat Pemeriksa 2"
                            placeholder="-- Pilih Perawat (berbeda dari Perawat 1) --"
                            :profesiId="2"
                            :required="true" />
                    </div>
                </div>
            </div>

            {{-- ================= TABEL OBAT HIGH ALERT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-amber-700 font-medium text-[15px]">Daftar Obat High Alert — 5 Benar per Perawat</span>
                    <span class="text-xs text-slate-600">Baris tampil: <span id="jumlah-baris">0</span> / {{ $maksBaris }}</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    @if ($errBaris)
                        <div class="mb-3 border border-red-300 bg-red-50 text-red-700 text-xs rounded-sm p-3">
                            <p class="font-semibold mb-1">Centang seluruh 5 benar untuk baris obat yang terisi:</p>
                            <ul class="list-disc ml-5 space-y-0.5">
                                @foreach ($errBaris as $key => $messages)
                                    <li>{{ $key }}: {{ implode(' ', $messages) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-xs text-slate-500 mb-2">Baris yang terisi obatnya wajib dicentang 5 benar oleh <strong>KEDUA</strong> perawat (10 centang per baris).</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse" id="tabel-high-alert">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-2 py-2 border w-8">#</th>
                                    <th class="px-2 py-2 border min-w-48">Obat (High Alert)</th>
                                    @foreach ($kolom5Benar as $kolom)
                                        <th class="px-2 py-2 border w-16 text-center">P1<br><span class="font-normal">{{ $label5Benar[$kolom] }}</span></th>
                                    @endforeach
                                    @foreach ($kolom5Benar as $kolom)
                                        <th class="px-2 py-2 border w-16 text-center">P2<br><span class="font-normal">{{ $label5Benar[$kolom] }}</span></th>
                                    @endforeach
                                    <th class="px-2 py-2 border w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-high-alert"></tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <button type="button" id="btn-tambah-baris"
                            class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-sm hover:bg-slate-50"
                            @disabled($isView)>
                            + Tambah Baris
                        </button>
                        <span class="text-xs text-slate-500">Maksimal {{ $maksBaris }} baris.</span>
                    </div>

                    <datalist id="daftar-obat-high-alert">
                        @foreach ($opsiObatHa as $namaObat)
                            <option value="{{ $namaObat }}"></option>
                        @endforeach
                    </datalist>

                    <template id="template-baris">
                        <tr class="border-b">
                            <td class="px-2 py-1 border text-center text-slate-400"></td>
                            <td class="px-2 py-1 border">
                                <input type="text" data-kolom="ha_obat" list="daftar-obat-high-alert" maxlength="150"
                                    class="w-full border rounded-sm px-2 py-1" placeholder="Nama obat high alert">
                            </td>
                            @foreach ($kolom5Benar as $kolom)
                                <td class="px-2 py-1 border text-center">
                                    <input type="checkbox" data-kolom="ha_p1_{{ $kolom }}" value="1">
                                </td>
                            @endforeach
                            @foreach ($kolom5Benar as $kolom)
                                <td class="px-2 py-1 border text-center">
                                    <input type="checkbox" data-kolom="ha_p2_{{ $kolom }}" value="1">
                                </td>
                            @endforeach
                            <td class="px-2 py-1 border text-center">
                                <button type="button" data-hapus class="text-red-600 hover:text-red-800" title="Hapus baris">&times;</button>
                            </td>
                        </tr>
                    </template>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                const MAKS = {{ $maksBaris }};
                const AWAL = {{ $barisTampil }};
                const KOLOM = ['ha_obat'].concat(@json($kolomP1Ha), @json($kolomP2Ha));
                const TERSIMPAN = @json($barisTersimpan);
                const tbody = document.getElementById('tbody-high-alert');
                const template = document.getElementById('template-baris');
                const btnTambah = document.getElementById('btn-tambah-baris');
                const spanJumlah = document.getElementById('jumlah-baris');

                function hitungBaris() {
                    spanJumlah.textContent = tbody.children.length;
                    btnTambah.disabled = tbody.children.length >= MAKS || !!{{ $isView ? 'true' : 'false' }};
                }

                function pasangBaris(tr, baris) {
                    tr.dataset.item = baris;
                    tr.querySelector('td:first-child').textContent = baris;

                    tr.querySelectorAll('[data-kolom]').forEach(function (el) {
                        const kolom = el.dataset.kolom;
                        el.name = kolom + '_' + baris;
                        const nilai = (TERSIMPAN[baris] && TERSIMPAN[baris][kolom] != null) ? TERSIMPAN[baris][kolom] : '';
                        if (el.type === 'checkbox') {
                            el.checked = nilai === '1' || nilai === 1 || nilai === true;
                        } else {
                            el.value = nilai;
                        }
                    });

                    const hapus = tr.querySelector('[data-hapus]');
                    if (hapus) {
                        hapus.addEventListener('click', function () {
                            tr.remove();
                            hitungBaris();
                        });
                    }
                }

                function tambahSatu(baris) {
                    const tr = template.content.firstElementChild.cloneNode(true);
                    tbody.appendChild(tr);
                    pasangBaris(tr, baris);
                    hitungBaris();
                }

                for (let i = 1; i <= AWAL; i++) {
                    if (tbody.children.length >= MAKS) break;
                    tambahSatu(i);
                }

                btnTambah.addEventListener('click', function () {
                    if (tbody.children.length >= MAKS) return;
                    tambahSatu(tbody.children.length + 1);
                });

                hitungBaris();
            })();
        </script>
    </x-emr-split-layout>
@endsection
