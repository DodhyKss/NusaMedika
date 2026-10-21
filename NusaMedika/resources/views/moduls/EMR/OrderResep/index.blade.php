@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = isset($edit) && $edit && ! $isView;

        $actionUrl = $isEdit
            ? route('emr.form.update', ['form_name' => 'order_resep', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit->peresepan_obat_id])
            : route('emr.form.store', ['form_name' => 'order_resep', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.form.destroy', ['form_name' => 'order_resep', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit->peresepan_obat_id])
            : '';

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Resep Obat Baru';
        $subtitleForm = 'Tulis resep obat untuk pasien.';
        if ($isEdit) {
            $titleForm = 'Edit Resep Obat';
            $subtitleForm = 'Perbarui resep pasien.';
        } elseif ($isView) {
            $titleForm = 'Detail Resep Obat';
            $subtitleForm = 'Detail resep pasien.';
        }

        $barangOptions = '';
        foreach ($barangs as $b) {
            $barangOptions .= '<option value="'.$b->barang_id.'" data-satuan="'.e($b->textSatuan()).'">'.e($b->nama_barang).'</option>';
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Resep"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="collect()"
        routeName=""
        routeUrl="{{ url('emr/form/order_resep') }}"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$actionUrl"
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl"
        :emrId="($isEdit || $isView) ? $edit->peresepan_obat_id : ''"
        :printUrl="''"
        :isView="$isView"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']"
    >
        <x-slot name="listRiwayat">
            <div class="mb-4 relative">
                <input type="text" id="searchInputResep" placeholder="Cari di riwayat..." class="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @forelse ($peresepans as $resep)
                @php
                    $isActive = isset($edit) && $edit && $edit->peresepan_obat_id == $resep->peresepan_obat_id;
                    $itemList = $resep->details->map(fn ($d) => $d->barang?->nama_barang ?? ('#'.$d->barang_id))->implode(', ');
                @endphp
                <div class="riwayat-item bg-white rounded-xl border {{ $isActive ? 'border-blue-400 ring-2 ring-blue-500/10' : 'border-slate-200' }} shadow-sm overflow-hidden mb-3">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between {{ $isActive ? 'bg-blue-50/60' : 'bg-slate-50/50' }}">
                        <div>
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">{{ $resep->no_resep }}</span>
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold ml-1 {{ $resep->status_resep == 1 ? 'bg-emerald-100 text-emerald-700' : ($resep->status_resep == 2 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $resep->status_label }}</span>
                        </div>
                        <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($resep->tanggal_resep)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="px-4 py-3 text-xs text-slate-600">
                        <p class="font-medium text-slate-700">{{ $resep->dokter?->nama_pegawai ?? '-' }}</p>
                        @if ($itemList)
                            <p class="mt-1 text-slate-500">{{ Str::limit($itemList, 90) }}</p>
                        @endif
                    </div>
                    <div class="px-4 py-2 border-t border-slate-100 flex items-center gap-1 justify-end">
                        @if ($aksesCrud['read'])
                            <a href="{{ url('emr/form/order_resep/'.$registrasi_detail->registrasi_detail_id.'/'.$resep->peresepan_obat_id.'?action=view') }}" class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                        @endif
                        @if ($aksesCrud['update'] && (int) $resep->status_resep === 0)
                            <a href="{{ url('emr/form/order_resep/'.$registrasi_detail->registrasi_detail_id.'/'.$resep->peresepan_obat_id) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        @endif
                        @if ($aksesCrud['delete'] && (int) $resep->status_resep === 0)
                            <form action="{{ route('emr.form.destroy', ['form_name' => 'order_resep', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $resep->peresepan_obat_id]) }}" method="POST" data-confirm-message="Apakah Anda yakin ingin membatalkan resep ini?" data-confirm-danger="true" data-confirm-title="Batalkan" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Batal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-center opacity-50 bg-white rounded-xl border border-slate-200 p-6">
                    <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-sm text-slate-500">Belum ada resep</p>
                </div>
            @endforelse

            @if ($peresepans->hasPages())
                <div class="mt-4">
                    {{ $peresepans->links('components.pagination') }}
                </div>
            @endif
        </x-slot>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 space-y-6">
                {{-- Item Obat --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800">Item Obat</h3>
                        <button type="button" id="btnTambahItem" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Item
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left" style="min-width: 900px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Obat</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:80px;">Jumlah</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">S1</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">S2</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:180px;">Aturan Pakai</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:150px;">Rute Pemberian</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:44px;">#</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItem" class="divide-y divide-slate-100">
                                <tr id="rowPlaceholder" class="text-center">
                                    <td colspan="7" class="px-3 py-6 text-xs text-slate-400">Belum ada item. Klik "Tambah Item" untuk menulis resep.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label for="keterangan" class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="2" {{ $isView ? 'readonly' : '' }}
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 {{ $isView ? 'bg-slate-50 cursor-not-allowed text-slate-600' : 'focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors' }} text-sm"
                        placeholder="Catatan tambahan resep...">{{ old('keterangan', $edit?->keterangan ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </x-emr-split-layout>

    @php
        $existingItemsJson = $editDetails->map(fn ($d) => [
            'barang_id' => (string) $d->barang_id,
            'jumlah' => $d->jumlah,
            's_1' => $d->s_1,
            's_2' => $d->s_2,
            'aturan_pakai' => $d->aturan_pakai,
            'rute_pemberian' => $d->rute_pemberian,
        ])->values();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var apiUrl = "{{ route('api.barang.search') }}";
            var tbody = document.getElementById('tbodyItem');
            var placeholder = document.getElementById('rowPlaceholder');
            var counter = 0;
            var isView = {{ $isView ? 'true' : 'false' }};
            var existingItems = @json($existingItemsJson);

            function addRow(item) {
                counter++;
                item = item || {};

                var tr = document.createElement('tr');
                tr.dataset.item = counter;
                tr.innerHTML = [
                    '<td class="px-3 py-2 align-top">',
                    '   <select name="barang_id[]" class="barang-select text-sm w-full" style="min-width:220px;"' + (isView ? ' disabled' : '') + '></select>',
                    '</td>',
                    '<td class="px-3 py-2 align-top">',
                    '   <input type="number" name="jumlah[]" min="1" step="any" value="' + (item.jumlah || '1') + '" class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700" style="min-width:70px;"' + (isView ? ' readonly' : '') + '>',
                    '</td>',
                    '<td class="px-3 py-2 align-top">',
                    '   <input type="text" name="s_1[]" value="' + (item.s_1 || '') + '" placeholder="3x sehari" class="text-sm w-full border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700"' + (isView ? ' readonly' : '') + '>',
                    '</td>',
                    '<td class="px-3 py-2 align-top">',
                    '   <input type="text" name="s_2[]" value="' + (item.s_2 || '') + '" placeholder="1 tablet" class="text-sm w-full border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700"' + (isView ? ' readonly' : '') + '>',
                    '</td>',
                    '<td class="px-3 py-2 align-top">',
                    '   <input type="text" name="aturan_pakai[]" value="' + (item.aturan_pakai || '') + '" placeholder="Sesudah makan" class="text-sm w-full border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700"' + (isView ? ' readonly' : '') + '>',
                    '</td>',
                    '<td class="px-3 py-2 align-top">',
                    '   <input type="text" name="rute_pemberian[]" value="' + (item.rute_pemberian || '') + '" placeholder="Oral" class="text-sm w-full border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700"' + (isView ? ' readonly' : '') + '>',
                    '</td>',
                    '<td class="px-3 py-2 align-top text-center">',
                    (isView ? '' : '   <button type="button" class="btn-hapus-item p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Item"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>'),
                    '</td>'
                ].join('');

                placeholder.style.display = 'none';
                tbody.appendChild(tr);

                var $tr = $(tr);
                var $sel = $tr.find('select.barang-select');

                if (loadOptionsInline($sel, item.barang_id)) {
                    $sel.select2({ placeholder: 'Cari Obat...', allowClear: true, width: '100%' });
                } else {
                    window.initBarangSelect($sel, apiUrl);
                    if (item.barang_id) {
                        $sel.val(item.barang_id).trigger('change');
                    }
                }

                $tr.find('.btn-hapus-item').on('click', function () {
                    try {
                        if ($sel.data('select2')) {
                            $sel.select2('destroy');
                        }
                    } catch (e) {}
                    tr.remove();
                    if (!tbody.querySelector('tr[data-item]')) {
                        placeholder.style.display = '';
                    }
                });
            }

            function loadOptionsInline($select, barangId) {
                var preload = @json($barangs);
                if (!preload.length) return false;
                $select.find('option[value]').not('[value=""]').remove();
                $select.append($('<option value=""></option>'));
                preload.forEach(function (b) {
                    var opt = new Option(b.nama_barang, b.barang_id);
                    $(opt).attr('data-satuan', b.textSatuan || '');
                    $select.append(opt);
                });
                $select.val(barangId || null);
                return true;
            }

            document.getElementById('btnTambahItem').addEventListener('click', function () {
                if (isView) return;
                addRow();
            });

            existingItems.forEach(function (item) { addRow(item); });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('searchInputResep').addEventListener('input', function () {
                var filter = this.value.toLowerCase();
                document.querySelectorAll('.riwayat-item').forEach(function (item) {
                    item.style.display = item.textContent.toLowerCase().includes(filter) ? '' : 'none';
                });
            });
        });
    </script>
@endsection