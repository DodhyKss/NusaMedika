@extends('layouts.iframe')

@section('content')
    @php
        $C = \App\Http\Controllers\EMR\RekonsiliasiObat\RekonsiliasiObatController::class;
        $maksBaris = $C::MAKS_BARIS;
        $opsiTab = $C::OPSI_TAB;
        $grupDef = $C::GRUP;

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Rekonsiliasi Obat';
        $subtitleForm = 'Rekonsiliasi obat saat admisi dan saat pulang. Tiga grup baris: riwayat obat, obat yang sedang diresepkan, dan obat yang dibawa pulang.';
        $routeName = null;
        $routeUrl = url('emr/form/rekonsiliasi_obat');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $opsiObatJson = json_encode($barangMap);
        // Daftar dokter untuk kolom `dokter` pada grup resep & pulang.
        $dokterList = [];
        foreach (\App\Models\Pegawai::aktif()->where('profesi_id', 1)->get() as $p) {
            $dokterList[] = ['id' => $p->pegawai_id, 'nama' => $p->nama_pegawai];
        }
        $dokterListJson = json_encode($dokterList);
        $dataJson = json_encode(is_array($emr_data) ? $emr_data : []);

        // Label kolom per grup.
        $labelKolom = [
            'nama_obat' => 'Nama Obat',
            'rute' => 'Rute',
            'dosis' => 'Dosis',
            'satuan' => 'Satuan',
            'freq_1' => 'Freq. 1',
            'freq_2' => 'Freq. 2',
            'dokter' => 'Dokter',
            'tgl_mulai' => 'Tgl Mulai',
            'keterangan' => 'Keterangan',
            'lanjut' => 'Lanjut',
        ];

        $headers = [
            'rek_tab_aktif' => 'Tab',
            'catatan_rekonsiliasi' => 'Catatan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Rekonsiliasi Obat"
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
                slug="rekonsiliasi_obat"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= PILIH GRUP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tahap Rekonsiliasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tahap <span class="text-red-500">*</span></label>
                        <select name="rek_tab_aktif" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @foreach ($opsiTab as $k => $label)
                                <option value="{{ $k }}" {{ $v('rek_tab_aktif') === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('rek_tab_aktif') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Rekonsiliasi</label>
                        <input type="text" name="catatan_rekonsiliasi" value="{{ $v('catatan_rekonsiliasi') }}"
                            placeholder="mis. daftar obat yang dihentikan" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('catatan_rekonsiliasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            @foreach ($grupDef as $grup => $kolom)
                @php $judul = $grup === 'riwayat' ? 'Riwayat Obat Sebelum Admisi' : ($grup === 'resep' ? 'Obat Diresepkan Saat Ini' : 'Obat yang Dibawa Pulang'); @endphp
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-cyan-600 font-medium text-[15px]">{{ $judul }}</span>
                        <button type="button" class="btn-tambah px-2 py-1 text-xs border border-slate-300 rounded-sm hover:bg-slate-50"
                            data-grup="{{ $grup }}" @disabled($isView)>+ Tambah Baris</button>
                    </div>
                    <div class="p-5 bg-white text-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-left">
                                        <th class="px-2 py-2 border w-8">#</th>
                                        @foreach ($kolom as $k)
                                            <th class="px-2 py-2 border {{ $k === 'nama_obat' ? 'min-w-[220px]' : 'w-32' }}">
                                                {{ $labelKolom[$k] }}
                                                @if ($k === 'nama_obat' || ($grup !== 'riwayat' && in_array($k, ['rute', 'dosis', 'freq_1', 'satuan', 'dokter', 'tgl_mulai'], true)))
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </th>
                                        @endforeach
                                        <th class="px-2 py-2 border w-10"></th>
                                    </tr>
                                </thead>
                                <tbody data-tbody="{{ $grup }}"></tbody>
                            </table>
                        </div>
                    </div>

                    <template data-template="{{ $grup }}">
                        <tr>
                            <td class="px-2 py-1 border text-center text-slate-400"></td>
                            @foreach ($kolom as $k)
                                <td class="px-2 py-1 border">
                                    @if ($k === 'nama_obat')
                                        <input type="text" data-kolom="nama_obat" list="daftar-obat-{{ $grup }}"
                                            class="w-full border rounded-sm px-2 py-1" placeholder="id barang / R: racikan">
                                    @elseif ($k === 'lanjut')
                                        <select data-kolom="lanjut" class="w-full border rounded-sm px-2 py-1">
                                            <option value="">--</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    @elseif ($k === 'tgl_mulai')
                                        <input type="date" data-kolom="tgl_mulai" class="w-full border rounded-sm px-2 py-1">
                                    @elseif ($k === 'dokter')
                                        <select data-kolom="dokter" class="w-full border rounded-sm px-2 py-1"></select>
                                    @else
                                        <input type="text" data-kolom="{{ $k }}" class="w-full border rounded-sm px-2 py-1">
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-2 py-1 border text-center">
                                <button type="button" data-hapus class="text-red-600 hover:text-red-800" title="Hapus baris">&times;</button>
                            </td>
                        </tr>
                    </template>

                    <datalist id="daftar-obat-{{ $grup }}">
                        @foreach ($barangMap as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </datalist>
                </div>
            @endforeach

            {{-- ================= EDUKASI PULANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Edukasi Obat Saat Pulang</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ([
                            'pulang_edukasi_aturan_minum' => 'Aturan minum',
                            'pulang_edukasi_dosis' => 'Dosis',
                            'pulang_edukasi_cara_penggunaan' => 'Cara penggunaan',
                            'pulang_edukasi_penyimpanan' => 'Penyimpanan',
                        ] as $variabel => $label)
                            <label class="flex items-center gap-2 border rounded-sm px-3 py-2">
                                <input type="hidden" name="{{ $variabel }}" value="off">
                                <input type="checkbox" name="{{ $variabel }}" value="on" class="form-checkbox"
                                    {{ ($emr_data[$variabel] ?? '') === 'on' ? 'checked' : '' }}>
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            (function () {
                const MAKS = {{ $maksBaris }};
                const AWAL = {{ \App\Http\Controllers\EMR\RekonsiliasiObat\RekonsiliasiObatController::BARIS_AWAL }};
                const GRUP = @json($grupDef);
                const DATA = @json($dataJson);
                const OPSI_OBAT = @json($opsiObatJson);
                const DOKTER = @json($dokterListJson);

                function namaVar(grup, kolom, baris) { return grup + '_' + kolom + '_' + baris; }

                function pasangBaris(tr, grup, baris) {
                    tr.dataset.item = baris;
                    tr.querySelector('td:first-child').textContent = baris;

                    // Dropdown dokter diisi dari master (hanya kolom `dokter`).
                    const selDokter = tr.querySelector('[data-kolom="dokter"]');
                    if (selDokter) {
                        selDokter.innerHTML = '<option value="">-- Pilih --</option>';
                        DOKTER.forEach(function (d) {
                            const o = document.createElement('option');
                            o.value = d.id; o.textContent = d.nama;
                            selDokter.appendChild(o);
                        });
                    }

                    tr.querySelectorAll('[data-kolom]').forEach(function (el) {
                        const kolom = el.dataset.kolom;
                        const kunci = namaVar(grup, kolom, baris);
                        el.name = kunci;
                        const nilai = DATA[kunci];
                        if (el.tagName === 'SELECT') {
                            el.value = nilai || '';
                        } else {
                            el.value = nilai || '';
                        }
                    });

                    const hapus = tr.querySelector('[data-hapus]');
                    if (hapus) {
                        hapus.addEventListener('click', function () { tr.remove(); });
                    }
                }

                function hitungBarisAwal(grup) {
                    let maks = 0;
                    for (let i = 1; i <= MAKS; i++) {
                        if (DATA[namaVar(grup, 'nama_obat', i)]) maks = i;
                    }
                    return Math.max(AWAL, Math.min(MAKS, maks + 1));
                }

                ['riwayat', 'resep', 'pulang'].forEach(function (grup) {
                    const tbody = document.querySelector('[data-tbody="' + grup + '"]');
                    const tpl = document.querySelector('[data-template="' + grup + '"]');
                    const btn = document.querySelector('.btn-tambah[data-grup="' + grup + '"]');
                    if (!tbody || !tpl) return;

                    const sampai = hitungBarisAwal(grup);
                    for (let i = 1; i <= sampai; i++) {
                        const tr = tpl.content.firstElementChild.cloneNode(true);
                        tbody.appendChild(tr);
                        pasangBaris(tr, grup, i);
                    }

                    if (btn) {
                        btn.addEventListener('click', function () {
                            if (tbody.children.length >= MAKS) return;
                            const tr = tpl.content.firstElementChild.cloneNode(true);
                            tbody.appendChild(tr);
                            pasangBaris(tr, grup, tbody.children.length);
                        });
                    }
                });
            })();
        </script>
    @endpush
@endsection