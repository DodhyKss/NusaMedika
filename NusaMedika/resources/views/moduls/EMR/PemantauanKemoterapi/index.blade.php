@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pemantauan Kemoterapi';
        $subtitleForm = 'Pemantauan per jam selama pemberian kemoterapi: cairan, obat, TTV, skala nyeri & efek samping.';
        $routeName = null;
        $routeUrl = url('emr/form/pemantauan_kemoterapi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_pemantauan' => 'Tanggal',
            'jam_pemantauan' => 'Jam',
        ];

        $kolomBaris = [
            'jam' => ['label' => 'Jam', 'type' => 'time', 'cls' => 'w-20'],
            'cairan' => ['label' => 'Cairan (mL)', 'type' => 'number', 'cls' => 'w-20', 'attr' => 'min="0" max="10000" step="0.01"'],
            'obat' => ['label' => 'Obat', 'type' => 'text', 'cls' => 'w-40'],
            'dosis' => ['label' => 'Dosis', 'type' => 'text', 'cls' => 'w-24'],
            'sistolik' => ['label' => 'Sist. (mmHg)', 'type' => 'number', 'cls' => 'w-20', 'attr' => 'min="50" max="300" step="1"'],
            'diastolik' => ['label' => 'Diast. (mmHg)', 'type' => 'number', 'cls' => 'w-20', 'attr' => 'min="20" max="200" step="1"'],
            'nadi' => ['label' => 'Nadi (/mnt)', 'type' => 'number', 'cls' => 'w-16', 'attr' => 'min="20" max="250" step="1"'],
            'pernapasan' => ['label' => 'RR (/mnt)', 'type' => 'number', 'cls' => 'w-16', 'attr' => 'min="5" max="80" step="1"'],
            'suhu' => ['label' => 'Suhu (°C)', 'type' => 'number', 'cls' => 'w-16', 'attr' => 'min="30" max="45" step="0.1"'],
            'skala_nyeri' => ['label' => 'Skala Nyeri', 'type' => 'number', 'cls' => 'w-16', 'attr' => 'min="0" max="10" step="1"'],
            'keluhan' => ['label' => 'Keluhan', 'type' => 'text', 'cls' => 'w-44'],
            'efek_samping' => ['label' => 'Efek Samping', 'type' => 'text', 'cls' => 'w-44'],
        ];

        // Nilai tersimpan per baris (1..MAKS_BARIS) untuk diisi oleh JS.
        $barisTersimpan = [];
        $maksBaris = \App\Http\Controllers\EMR\PemantauanKemoterapi\PemantauanKemoterapiController::MAKS_BARIS;
        $maksPremed = \App\Http\Controllers\EMR\PemantauanKemoterapi\PemantauanKemoterapiController::MAKS_PREMED;
        for ($b = 1; $b <= $maksBaris; $b++) {
            $baris = [];
            foreach ($kolomBaris as $key => $cfg) {
                $baris[$key] = $emr_data[$key.'_'.$b] ?? '';
            }
            $barisTersimpan[$b] = $baris;
        }

        // Error validasi baris (guard server menambah pesan per kolom_N).
        $polaBaris = '/^(jam|cairan|obat|dosis|sistolik|diastolik|nadi|pernapasan|suhu|skala_nyeri|keluhan|efek_samping)_\d+$/';
        $errBaris = array_filter(
            $errors->messages(),
            fn ($k) => preg_match($polaBaris, $k),
            ARRAY_FILTER_USE_KEY
        );
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pemantauan Kemoterapi"
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
                slug="pemantauan_kemoterapi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pemantauan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pemantauan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pemantauan" value="{{ $v('tanggal_pemantauan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pemantauan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam Pemantauan <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_pemantauan" value="{{ $v('jam_pemantauan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_pemantauan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Perawat Ruangan <span class="text-red-500">*</span></label>
                        <input type="text" name="perawat_ruangan" value="{{ $v('perawat_ruangan') !== '' ? $v('perawat_ruangan') : $perawatRuangan }}" maxlength="150"
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly>
                        @error('perawat_ruangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Total Cairan (mL)</label>
                        <input type="number" name="total_cairan" value="{{ $v('total_cairan') }}" step="0.01" min="0" readonly
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50">
                        <p class="text-[11px] text-slate-500 mt-1">Otomatis = jumlah kolom Cairan.</p>
                    </div>
                </div>
            </div>

            {{-- ================= PREMEDIKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Premedikasi (maksimal {{ $maksPremed }} obat)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl">
                        @for ($i = 1; $i <= $maksPremed; $i++)
                            <div class="grid grid-cols-3 gap-2 border border-slate-100 rounded-sm p-3 items-end">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium mb-1">Obat {{ $i }}</label>
                                    <input type="text" name="premed_{{ $i }}" value="{{ $v('premed_'.$i) }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Dosis</label>
                                    <input type="text" name="premed_dosis_{{ $i }}" value="{{ $v('premed_dosis_'.$i) }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                                </div>
                                @error('premed_'.$i) <p class="col-span-3 text-xs text-red-600">{{ $message }}</p> @enderror
                                @error('premed_dosis_'.$i) <p class="col-span-3 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- ================= TABEL PEMANTAUAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-emerald-700 font-medium text-[15px]">Pemantauan per Jam</span>
                    <span class="text-xs text-slate-600">Baris tampil: <span id="jumlah-baris">0</span> / {{ $maksBaris }}</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    @if ($errBaris)
                        <div class="mb-3 border border-red-300 bg-red-50 text-red-700 text-xs rounded-sm p-3">
                            <p class="font-semibold mb-1">Isian tabel pemantauan belum lengkap:</p>
                            <ul class="list-disc ml-5 space-y-0.5">
                                @foreach ($errBaris as $key => $messages)
                                    <li>{{ $key }}: {{ implode(' ', $messages) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-xs text-slate-500 mb-2">Bila satu baris diisi (kolom Jam ada), seluruh kolom baris itu wajib terisi. Minimal satu baris wajib diisi.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse" id="tabel-pemantauan">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-2 py-2 border w-8">#</th>
                                    @foreach ($kolomBaris as $key => $cfg)
                                        <th class="px-2 py-2 border {{ $cfg['cls'] }}">{{ $cfg['label'] }} <span class="text-red-500">*</span></th>
                                    @endforeach
                                    <th class="px-2 py-2 border w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-pemantauan"></tbody>
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

                    <template id="template-baris">
                        <tr class="border-b">
                            <td class="px-2 py-1 border text-center text-slate-400"></td>
                            @foreach ($kolomBaris as $key => $cfg)
                                <td class="px-2 py-1 border">
                                    <input type="{{ $cfg['type'] }}" data-kolom="{{ $key }}"
                                        {!! $cfg['attr'] ?? '' !!} maxlength="{{ in_array($key, ['obat', 'dosis']) ? ($key === 'obat' ? 150 : 100) : 500 }}"
                                        class="w-full border rounded-sm px-2 py-1 {{ $cfg['cls'] }}">
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
                const KOLOM = @json(array_keys($kolomBaris));
                const TERSIMPAN = @json($barisTersimpan);
                const tbody = document.getElementById('tbody-pemantauan');
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
                        el.value = (TERSIMPAN[baris] && TERSIMPAN[baris][kolom] != null) ? TERSIMPAN[baris][kolom] : '';
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
