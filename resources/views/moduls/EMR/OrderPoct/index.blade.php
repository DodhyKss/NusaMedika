@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = isset($edit) && $edit && ! $isView;
        $slug = 'order_poct';
        $orderId = ($isEdit || $isView) && $edit ? $edit->order_poct_id : null;

        $actionUrl = $isEdit
            ? route('emr.form.update', ['form_name' => $slug, 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $orderId])
            : route('emr.form.store', ['form_name' => $slug, 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.form.destroy', ['form_name' => $slug, 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $orderId])
            : '';

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Order POCT Baru';
        $subtitleForm = 'Perintahkan pemeriksaan POCT (point of care testing) untuk pasien.';
        if ($isEdit) {
            $titleForm = 'Edit Order POCT';
            $subtitleForm = 'Perbarui order yang masih menunggu.';
        } elseif ($isView) {
            $titleForm = 'Detail Order POCT';
            $subtitleForm = 'Detail order patologi anatomi pasien.';
        }

        // Opsi tindakan + group asalnya, untuk cascade "jenis pesanan" -> item.
        $opsiTindakan = [];
        foreach ($tindakans as $tk) {
            $opsiTindakan[] = [
                'id' => $tk->tindakan_id,
                'nama' => $tk->nama_tindakan,
                'kode' => $tk->kode_tindakan,
                'bagian' => $tindakanBagianMap[$tk->tindakan_id] ?? null,
                'tarif' => $tarifTindakan[$tk->tindakan_id] ?? 0,
            ];
        }

        // Baris tersimpan (untuk edit).
        $barisTersimpan = [];
        foreach ($editDetails ?? collect() as $d) {
            $barisTersimpan[] = [
                'tindakan_id' => $d->tindakan_id,
                'spesimen' => $d->spesimen,
                'harga' => $d->harga,
            ];
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Order POCT"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="collect()"
        routeName=""
        :routeUrl="url('emr/form/order_poct')"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$actionUrl"
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl"
        :emrId="$orderId ?? ''"
        :printUrl="''"
        :isView="$isView"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']"
    >
        <x-slot name="listRiwayat">
            <div class="space-y-2">
                @forelse ($orders as $o)
                    <a href="{{ url('emr/form/order_poct/'.$registrasi_detail->registrasi_detail_id.'/'.$o->order_poct_id) }}"
                        class="block border rounded-sm px-3 py-2 text-xs hover:bg-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ $o->no_order }}</span>
                            <span class="px-2 py-0.5 rounded-full {{ $o->status_class }}">{{ $o->status_label }}</span>
                        </div>
                        <div class="text-slate-500 mt-1">
                            {{ optional($o->tanggal_order)->format('d/m/Y H:i') }}
                            · {{ $o->prioritas }}
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-slate-400">Belum ada order patologi.</p>
                @endforelse
            </div>
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            {{-- ================= HEADER ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Order</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pemeriksaan"
                            value="{{ old('tanggal_pemeriksaan', $isEdit && $edit->tanggal_order ? $edit->tanggal_order->format('Y-m-d') : '') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm" required>
                        @error('tanggal_pemeriksaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Unit Tujuan <span class="text-red-500">*</span></label>
                        <select name="bagian_tujuan_id" id="bagian_tujuan_id" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($bagianList as $b)
                                <option value="{{ $b->bagian_id }}"
                                    {{ (string) old('bagian_tujuan_id', $isEdit ? $edit->bagian_tujuan_id : '') === (string) $b->bagian_id ? 'selected' : '' }}>
                                    {{ $b->nama_bagian }}
                                </option>
                            @endforeach
                        </select>
                        @error('bagian_tujuan_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Diagnosa Medis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_poct" rows="2"
                            class="w-full border rounded-sm px-3 py-2 text-sm" required>{{ old('diagnosa_poct') }}</textarea>
                        @error('diagnosa_poct') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Indikasi Pemeriksaan</label>
                        <input type="text" name="indikasi" value="{{ old('indikasi', $isEdit ? $edit->indikasi : '') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('indikasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Prioritas <span class="text-red-500">*</span></label>
                        <select name="prioritas" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @foreach (['BIASA', 'CITO'] as $o)
                                <option value="{{ $o }}" {{ old('prioritas', $isEdit ? $edit->prioritas : 'BIASA') === $o ? 'selected' : '' }}>{{ $o }}</option>
                            @endforeach
                        </select>
                        @error('prioritas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan</label>
                        <input type="text" name="catatan" value="{{ old('catatan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= ITEM PEMERIKSAAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Item Pemeriksaan</span>
                    <button type="button" id="btn-tambah-item" class="px-2 py-1 text-xs border border-slate-300 rounded-sm hover:bg-slate-50"
                        @disabled($isView)>+ Tambah Baris</button>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse" id="tabel-item">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-2 py-2 border w-8">#</th>
                                    <th class="px-2 py-2 border">Pemeriksaan <span class="text-red-500">*</span></th>
                                    <th class="px-2 py-2 border w-40">Keterangan</th>
                                    <th class="px-2 py-2 border w-32">Harga</th>
                                    <th class="px-2 py-2 border w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-item"></tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Pilih "Unit Tujuan" lebih dulu untuk memfilter item yang boleh dipilih.</p>
                </div>

                <template id="template-item">
                    <tr>
                        <td class="px-2 py-1 border text-center text-slate-400"></td>
                        <td class="px-2 py-1 border"><select data-kolom="tindakan" class="w-full border rounded-sm px-2 py-1"></select></td>
                        <td class="px-2 py-1 border"><input type="text" data-kolom="spesimen" class="w-full border rounded-sm px-2 py-1"></td>
                        <td class="px-2 py-1 border"><input type="text" data-kolom="harga" readonly class="w-full border rounded-sm px-2 py-1 bg-slate-50"></td>
                        <td class="px-2 py-1 border text-center"><button type="button" data-hapus class="text-red-600">&times;</button></td>
                    </tr>
                </template>
            </div>

            {{-- ================= DETAIL (mode lihat) ================= --}}
            @if ($isView && count($barisTersimpan) > 0)
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-medium text-[15px]">Rincian Item</span>
                    </div>
                    <div class="p-5 bg-white text-sm">
                        <table class="w-full text-xs border-collapse">
                            <thead><tr class="bg-slate-100 text-left">
                                <th class="px-2 py-2 border">Pemeriksaan</th>
                                <th class="px-2 py-2 border">Spesimen</th>
                                <th class="px-2 py-2 border">Harga</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($editDetails as $d)
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $d->nama_tindakan }}</td>
                                        <td class="px-2 py-1 border">{{ $d->spesimen ?? '-' }}</td>
                                        <td class="px-2 py-1 border">{{ number_format((float) $d->harga, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            (function () {
                const OPSI_TINDAKAN = @json($opsiTindakan);
                const SPESIMEN = @json(\App\Http\Controllers\EMR\OrderPatologi\OrderPatologiController::OPSI_SPESIMEN);
                const TERSIMPAN = @json($barisTersimpan);
                // Peta group -> daftar tindakan, diisi dari server.
                const GRUP_TINDAKAN = @json($grupTindakan ?? []);

                const tbody = document.getElementById('tbody-item');
                const template = document.getElementById('template-item');
                const btnTambah = document.getElementById('btn-tambah-item');
                const selectGroup = document.getElementById('bagian_tujuan_id');

                let nomor = 0;

                function pasangBaris(tr, awal) {
                    nomor++;
                    tr.dataset.item = nomor;
                    tr.querySelector('td:first-child').textContent = nomor;

                    const selTindakan = tr.querySelector('[data-kolom="tindakan"]');
                    const selSpesimen = tr.querySelector('[data-kolom="spesimen"]');
                    const inputHarga = tr.querySelector('[data-kolom="harga"]');

                    selTindakan.name = 'tindakan_id[' + nomor + ']';
                    selSpesimen.name = 'spesimen[' + nomor + ']';
                    inputHarga.removeAttribute('name');

                    // Keterangan bebas per baris (POCT tidak punya master spesimen).
                    selSpesimen.value = (awal && awal.spesimen) ? awal.spesimen : '';

                    // Tindakan difilter sesuai group terpilih.
                    isiTindakan(selTindakan, awal);

                    selTindakan.addEventListener('change', function () {
                        const t = OPSI_TINDAKAN.find(function (x) { return String(x.id) === selTindakan.value; });
                        inputHarga.value = t ? (t.tarif || 0).toLocaleString('id-ID') : '';
                    });

                    tr.querySelector('[data-hapus]').addEventListener('click', function () { tr.remove(); });
                }

                function isiTindakan(sel, awal) {
                    // Unit tujuan sebuah tindakan diturunkan dari Master Group
                    // Tindakan (tindakan tidak lagi punya kolom bagian_id).
                    const unitDipilih = selectGroup ? selectGroup.value : '';
                    const diizinkan = unitDipilih
                        ? OPSI_TINDAKAN.filter(function (t) { return String(t.bagian) === String(unitDipilih); })
                            .map(function (t) { return String(t.id); })
                        : null;

                    sel.innerHTML = '<option value="">-- Pilih pemeriksaan --</option>';
                    OPSI_TINDAKAN.forEach(function (t) {
                        // Bila group punya daftar tindakan, hanya itu yang boleh.
                        if (diizinkan && diizinkan.indexOf(String(t.id)) === -1) return;
                        const o = document.createElement('option');
                        o.value = t.id;
                        o.textContent = t.nama + ' (' + t.kode + ')';
                        if (awal && String(awal.tindakan_id) === String(t.id)) o.selected = true;
                        sel.appendChild(o);
                    });
                }

                function tambah(awal) {
                    const tr = template.content.firstElementChild.cloneNode(true);
                    tbody.appendChild(tr);
                    pasangBaris(tr, awal);
                }

                // Muat baris tersimpan bila edit; kalau tidak, satu baris kosong.
                if (TERSIMPAN.length > 0) {
                    TERSIMPAN.forEach(function (b) { tambah(b); });
                } else {
                    tambah(null);
                }

                if (btnTambah) {
                    btnTambah.addEventListener('click', function () { tambah(null); });
                }
                if (selectGroup) {
                    selectGroup.addEventListener('change', function () {
                        tbody.innerHTML = '';
                        nomor = 0;
                        tambah(null);
                    });
                }
            })();
        </script>
    @endpush
@endsection