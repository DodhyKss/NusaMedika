@extends('layouts.iframe')

@section('content')
    @php
        $C = \App\Http\Controllers\EMR\DaftarTerapiObat\DaftarTerapiObatController::class;
        $maksBaris = $C::MAKS_BARIS;
        $opsiRute = $C::OPSI_RUTE;
        $statusPemberian = \App\Helpers\FarmasiHelper::STATUS_PEMBERIAN;
        $perluAlasan = \App\Helpers\FarmasiHelper::STATUS_PERLU_ALASAN;

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Daftar Terapi Obat';
        $subtitleForm = 'Obat yang sedang berjalan beserta status pemberian per baris. Maksimal ' . $maksBaris . ' obat. Hanya ID obat yang disimpan — nama di-resolve dari master.';
        $routeName = null;
        $routeUrl = url('emr/form/daftar_terapi_obat');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Opsi obat (ID => label) untuk select2.
        $opsiObat = [];
        foreach ($barangMap as $id => $b) {
            $opsiObat[$id] = $b['nama'];
        }
        $opsiObatJson = json_encode($opsiObat);

        $headers = [
            'tanggal_terapi' => 'Tanggal',
            'dokter_instruksi_id' => 'Dokter Instruksi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Daftar Terapi Obat"
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
                slug="daftar_terapi_obat"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pencatatan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_terapi" value="{{ $v('tanggal_terapi') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_terapi') border-red-500 @enderror" required>
                        @error('tanggal_terapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_terapi" value="{{ $v('waktu_terapi') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('waktu_terapi') border-red-500 @enderror" required>
                        @error('waktu_terapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Atas Instruksi Dokter</label>
                        <select name="dokter_instruksi_id" class="w-full border rounded-sm px-3 py-2 text-sm select2-dokter">
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\Pegawai::aktif()->where('profesi_id', 1)->get() as $p)
                                <option value="{{ $p->pegawai_id }}" {{ (string) $emr_data['dokter_instruksi_id'] === (string) $p->pegawai_id ? 'selected' : '' }}>
                                    {{ $p->nama_pegawai }}
                                </option>
                            @endforeach
                        </select>
                        @error('dokter_instruksi_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ $v('keterangan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= DAFTAR OBAT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Daftar Terapi Obat</span>
                    <span class="text-xs text-slate-600">Baris terpakai: <span id="jumlah-terpakai">0</span> / {{ $maksBaris }}</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse" id="tabel-terapi">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-2 py-2 border w-8">#</th>
                                    <th class="px-2 py-2 border">Obat <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2 border w-24">Jumlah <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2 border w-20">S1</th>
                                    <th class="px-2 py-2 border w-20">S2</th>
                                    <th class="px-2 py-2 border">Aturan Pakai</th>
                                    <th class="px-2 py-2 border w-36">Rute</th>
                                    <th class="px-2 py-2 border w-44">Status <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2 border">Alasan Stop / Tunda</th>
                                    <th class="px-2 py-2 border w-28">Jam Masuk</th>
                                    <th class="px-2 py-2 border w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-terapi"></tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <button type="button" id="btn-tambah-obat"
                            class="px-3 py-1.5 text-xs font-medium border border-slate-300 rounded-sm hover:bg-slate-50"
                            @disabled($isView)>
                            + Tambah Baris
                        </button>
                        <span class="text-xs text-slate-500">Maksimal {{ $maksBaris }} baris.</span>
                    </div>
                    @error('obat_1') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

                    {{-- Template baris (di-clone oleh JS). --}}
                    <template id="template-baris">
                        <tr class="border-b">
                            <td class="px-2 py-1 border text-center text-slate-400"></td>
                            <td class="px-2 py-1 border">
                                <select class="select2-obat w-full border rounded-sm px-2 py-1" data-kolom="obat">
                                    <option value="">-- Pilih obat --</option>
                                </select>
                            </td>
                            <td class="px-2 py-1 border"><input type="number" step="0.01" min="0" data-kolom="jumlah" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border"><input type="text" maxlength="10" data-kolom="s_1" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border"><input type="text" maxlength="10" data-kolom="s_2" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border"><input type="text" data-kolom="aturan_pakai" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border"><select data-kolom="rute" class="w-full border rounded-sm px-2 py-1"></select></td>
                            <td class="px-2 py-1 border"><select data-kolom="status" class="w-full border rounded-sm px-2 py-1"></select></td>
                            <td class="px-2 py-1 border"><input type="text" data-kolom="alasan_stop" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border"><input type="time" data-kolom="jam_masuk" class="w-full border rounded-sm px-2 py-1"></td>
                            <td class="px-2 py-1 border text-center">
                                <button type="button" data-hapus class="text-red-600 hover:text-red-800" title="Hapus baris">&times;</button>
                            </td>
                        </tr>
                    </template>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            (function () {
                const MAKS = {{ $maksBaris }};
                const AWAL = {{ $barisTampil }};
                const OPSI_OBAT = @json($opsiObatJson);
                const OPSI_RUTE = @json($opsiRute);
                const STATUS = @json($statusPemberian);
                const PERLU_ALASAN = @json($perluAlasan);
                const SUFFIX = @json($C::KOLOM);

                const tbody = document.getElementById('tbody-terapi');
                const template = document.getElementById('template-baris');
                const btnTambah = document.getElementById('btn-tambah-obat');
                const labelTerpakai = document.getElementById('jumlah-terpakai');

                // Nilai tersimpan per kolom: { obat_1: '5', jumlah_1: '10', ... }
                const tersimpan = @json((object) (is_array($emr_data) ? array_filter($emr_data, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY) : []));

                function namaVariabel(kolom, baris) { return SUFFIX[kolom] + baris; }

                function isiOpsi(select, opsi, terpilih) {
                    select.innerHTML = '';
                    const kosong = document.createElement('option');
                    kosong.value = ''; kosong.textContent = '-- Pilih --';
                    select.appendChild(kosong);
                    Object.keys(opsi).forEach(function (k) {
                        const o = document.createElement('option');
                        o.value = k; o.textContent = opsi[k];
                        if (String(k) === String(terpilih || '')) o.selected = true;
                        select.appendChild(o);
                    });
                }

                // Select2 WAJIB destroy() sebelum init ulang — kalau tidak,
                // muncul container dropdown ganda dan elemen lama tak bisa
                // dipakai. Dan TIDAK BOLEH `disabled` untuk menonaktifkan baris
                // (Select2 mengunci status disabled saat init).
                function pasangSelect2($el) {
                    if ($el.data('select2')) {
                        $el.select2('destroy');
                    }
                    $el.select2({ width: '100%', allowClear: true });
                }

                function pasangBaris(tr, baris) {
                    tr.dataset.item = baris;

                    const nomor = tr.querySelector('td:first-child');
                    nomor.textContent = baris;

                    const ini = tersimpan[namaVariabel('obat', baris)];
                    const selectObat = tr.querySelector('[data-kolom="obat"]');
                    isiOpsi(selectObat, OPSI_OBAT, ini);
                    pasangSelect2($(selectObat));

                    const selectRute = tr.querySelector('[data-kolom="rute"]');
                    isiOpsi(selectRute, OPSI_RUTE, tersimpan[namaVariabel('rute', baris)]);

                    const selectStatus = tr.querySelector('[data-kolom="status"]');
                    isiOpsi(selectStatus, STATUS, tersimpan[namaVariabel('status', baris)]);

                    tr.querySelectorAll('[data-kolom]').forEach(function (el) {
                        const kolom = el.dataset.kolom;
                        if (kolom === 'obat' || kolom === 'rute' || kolom === 'status') return;
                        el.value = tersimpan[namaVariabel(kolom, baris)] || '';
                        el.name = namaVariabel(kolom, baris);
                    });

                    selectObat.name = namaVariabel('obat', baris);
                    selectRute.name = namaVariabel('rute', baris);
                    selectStatus.name = namaVariabel('status', baris);

                    // Alasan stop hanya aktif bila statusmemerlukan alasan.
                    const inputAlasan = tr.querySelector('[data-kolom="alasan_stop"]');
                    function sinkronAlasan() {
                        const perlu = PERLU_ALASAN.indexOf(selectStatus.value) !== -1;
                        inputAlasan.classList.toggle('bg-amber-50', perlu);
                        inputAlasan.placeholder = perlu ? 'Wajib diisi untuk status ini' : '';
                    }
                    selectStatus.addEventListener('change', sinkronAlasan);
                    sinkronAlasan();

                    selectObat.addEventListener('change', hitungTerpakai);

                    const hapus = tr.querySelector('[data-hapus]');
                    if (hapus) {
                        hapus.addEventListener('click', function () {
                            const s2 = $(selectObat);
                            if (s2.data('select2')) s2.select2('destroy');
                            tr.remove();
                            hitungTerpakai();
                        });
                    }
                }

                // Bangun baris dari template satu per satu.
                function tambahSatu(baris) {
                    const tr = template.content.firstElementChild.cloneNode(true);
                    tbody.appendChild(tr);
                    pasangBaris(tr, baris);
                    hitungTerpakai();
                }

                function hitungTerpakai() {
                    let n = 0;
                    tbody.querySelectorAll('[data-kolom="obat"]').forEach(function (el) {
                        if (el.value) n++;
                    });
                    if (labelTerpakai) labelTerpakai.textContent = n;
                }

                // Render awal: baris Enough untuk data tersimpan + buffer.
                let penanda = AWAL;
                tbody.innerHTML = '';
                for (let i = 1; i <= penanda; i++) tambahSatu(i);

                if (btnTambah) {
                    btnTambah.addEventListener('click', function () {
                        if (tbody.children.length >= MAKS) return;
                        tambahSatu(tbody.children.length + 1);
                    });
                }

                hitungTerpakai();
            })();
        </script>
    @endpush
@endsection