@extends('layouts.iframe')

@section('content')
    @php
        $isView = $isView ?? false;
        $isEdit = isset($edit) && $edit && ! $isView;

        $actionUrl = $isEdit
            ? route('emr.form.update', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit->order_laboratorium_id])
            : route('emr.form.store', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);

        $deleteUrl = $isEdit
            ? route('emr.form.destroy', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit->order_laboratorium_id])
            : '';

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Order Laboratorium Baru';
        $subtitleForm = 'Perintahkan pemeriksaan laboratorium untuk pasien.';
        if ($isEdit) {
            $titleForm = 'Edit Order Laboratorium';
            $subtitleForm = 'Perbarui order laboratorium yang masih menunggu.';
        } elseif ($isView) {
            $titleForm = 'Detail Order Laboratorium';
            $subtitleForm = 'Detail order laboratorium pasien.';
        }

        $tindakanOptions = '<option value=""></option>';
        foreach ($tindakans as $tk) {
            $tindakanOptions .= '<option value="'.$tk->tindakan_id.'" data-bagian="'.$tk->bagian_id.'" data-satuan="'.e($tk->satuan_hasil ?? '').'" data-nilai-normal="'.e($tk->nilai_normal ?? '').'">'.e($tk->nama_tindakan).' ('.$tk->kode_tindakan.')</option>';
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Order Laboratorium"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="collect()"
        routeName=""
        routeUrl="{{ url('emr/form/laboratorium') }}"
        :registrasiDetailId="$registrasi_detail->registrasi_detail_id"
        :formAction="$actionUrl"
        :isEdit="$isEdit"
        :deleteAction="$deleteUrl"
        :emrId="($isEdit || $isView) ? $edit->order_laboratorium_id : ''"
        :printUrl="''"
        :isView="$isView"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']"
    >
        <x-slot name="listRiwayat">
            <div class="mb-4 relative">
                <input type="text" id="searchInputOrder" placeholder="Cari di riwayat..." class="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @forelse ($orders as $order)
                @php
                    $isActive = isset($edit) && $edit && $edit->order_laboratorium_id == $order->order_laboratorium_id;
                    $itemList = $order->details->pluck('nama_tindakan')->implode(', ');
                @endphp
                <div class="riwayat-item bg-white rounded-xl border {{ $isActive ? 'border-blue-400 ring-2 ring-blue-500/10' : 'border-slate-200' }} shadow-sm overflow-hidden mb-3">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between {{ $isActive ? 'bg-blue-50/60' : 'bg-slate-50/50' }}">
                        <div class="flex flex-wrap items-center gap-1">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">{{ $order->no_order }}</span>
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $order->status_class }}">{{ $order->status_label }}</span>
                            @if ($order->prioritas)
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $order->prioritas === 'CITO' ? 'bg-rose-100 text-rose-700' : ($order->prioritas === 'SEGERA' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600') }}">{{ $order->prioritas }}</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="px-4 py-3 text-xs text-slate-600">
                        <p class="font-medium text-slate-700">{{ $order->dokter?->nama_pegawai ?? '-' }}</p>
                        @if ($itemList)
                            <p class="mt-1 text-slate-500">{{ Str::limit($itemList, 90) }}</p>
                        @endif
                        @if ((int) $order->status_order === 2)
                            <div class="mt-2 space-y-1 border-t border-slate-100 pt-2">
                                @foreach ($order->details as $dtl)
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $dtl->flag_abnormal ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                        <span class="font-medium">{{ $dtl->nama_tindakan }}</span>
                                        <span class="ml-auto">{{ $dtl->hasil ?? 'Hasil kosong' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="px-4 py-2 border-t border-slate-100 flex items-center gap-1 justify-end">
                        @if ($aksesCrud['read'])
                            <a href="{{ url('emr/form/laboratorium/'.$registrasi_detail->registrasi_detail_id.'/'.$order->order_laboratorium_id.'?action=view') }}" class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Lihat">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                        @endif
                        @if ($aksesCrud['update'] && (int) $order->status_order === 0)
                            <a href="{{ url('emr/form/laboratorium/'.$registrasi_detail->registrasi_detail_id.'/'.$order->order_laboratorium_id) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                        @endif
                        @if ($aksesCrud['delete'] && (int) $order->status_order === 0)
                            <form action="{{ route('emr.form.destroy', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $order->order_laboratorium_id]) }}" method="POST" data-confirm-message="Apakah Anda yakin ingin membatalkan order ini?" data-confirm-danger="true" data-confirm-title="Batalkan" class="inline">
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
                    <p class="text-sm text-slate-500">Belum ada order laboratorium</p>
                </div>
            @endforelse
        </x-slot>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 space-y-6">
                {{-- Bagian Tujuan & Prioritas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bagian_tujuan_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Bagian Tujuan</label>
                        <select id="bagian_tujuan_id" name="bagian_tujuan_id" {{ $isView ? 'disabled' : '' }} class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @foreach ($bagianList as $b)
                                <option value="{{ $b->bagian_id }}" @selected((int) old('bagian_tujuan_id', $edit?->bagian_tujuan_id ?? '') === (int) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="prioritas" class="block text-sm font-semibold text-slate-700 mb-1.5">Prioritas</label>
                        <select id="prioritas" name="prioritas" {{ $isView ? 'disabled' : '' }} class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @foreach (\App\Models\OrderLaboratorium::PRIORITAS as $prio)
                                <option value="{{ $prio }}" @selected(old('prioritas', $edit?->prioritas ?? null) === $prio)>{{ $prio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Item Pemeriksaan --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800">Item Pemeriksaan</h3>
                        <button type="button" id="btnTambahItem" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors {{ $isView ? 'hidden' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Item
                        </button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left" style="min-width: 700px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pemeriksaan</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nilai Normal</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:44px;">#</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItem" class="divide-y divide-slate-100">
                                <tr id="rowPlaceholder" class="text-center">
                                    <td colspan="4" class="px-3 py-6 text-xs text-slate-400">Belum ada item. Klik "Tambah Item" untuk memilih pemeriksaan.</td>
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
                        placeholder="Catatan tambahan order...">{{ old('keterangan', $edit?->keterangan ?? '') }}</textarea>
                </div>

                {{-- Hasil (hanya tampil saat order Selesai) --}}
                @if ($isView && (int) $edit->status_order === 2)
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 mb-3">Hasil Pemeriksaan</h3>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pemeriksaan</th>
                                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hasil</th>
                                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nilai Normal</th>
                                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($edit->details as $dtl)
                                        <tr>
                                            <td class="px-3 py-2.5 font-medium text-slate-700">{{ $dtl->nama_tindakan }}</td>
                                            <td class="px-3 py-2.5 {{ $dtl->flag_abnormal ? 'font-bold text-red-600' : 'text-slate-700' }}">{{ $dtl->hasil ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-slate-500">{{ $dtl->satuan_hasil ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-slate-500">{{ $dtl->nilai_normal ?? '-' }}</td>
                                            <td class="px-3 py-2.5 text-center">
                                                @if ($dtl->flag_abnormal)
                                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">ABNORMAL</span>
                                                @elseif ($dtl->hasil)
                                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">NORMAL</span>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($edit->petugasPelaksana)
                            <p class="mt-3 text-xs text-slate-500">Ditandatangani / diproses oleh: <span class="font-semibold text-slate-700">{{ $edit->petugasPelaksana->nama_pegawai }}</span></p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </x-emr-split-layout>

    @php
        $existingItemsJson = $editDetails->map(fn ($d) => [
            'tindakan_id' => $d->tindakan_id ? (string) $d->tindakan_id : '',
        ])->values();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tbody = document.getElementById('tbodyItem');
            var placeholder = document.getElementById('rowPlaceholder');
            var counter = 0;
            var isView = {{ $isView ? 'true' : 'false' }};
            var existingItems = @json($existingItemsJson);
            var optionsHtml = @json($tindakanOptions);
            var bagianSelect = document.getElementById('bagian_tujuan_id');

            function filterOptions($sel) {
                if (!bagianSelect) return;
                var bagianId = bagianSelect.value;
                $sel.find('option').each(function () {
                    if (!this.value) return;
                    var bagian = $(this).attr('data-bagian') || '';
                    this.disabled = bagianId && bagian !== bagianId;
                });
            }

            function renderMeta($tr, item) {
                $tr.find('.meta-satuan').text(item.satuan || '-');
                $tr.find('.meta-normal').text(item.nilai_normal || '-');
            }

            function addRow(item) {
                counter++;
                item = item || {};

                var tr = document.createElement('tr');
                tr.dataset.item = counter;
                tr.innerHTML = [
                    '<td class="px-3 py-2 align-top">',
                    '   <select name="tindakan_id[]" class="tindakan-select text-sm w-full" style="min-width:240px;"' + (isView ? ' disabled' : '') + '></select>',
                    '</td>',
                    '<td class="px-3 py-2 align-top"><span class="meta-satuan text-sm text-slate-500">-</span></td>',
                    '<td class="px-3 py-2 align-top"><span class="meta-normal text-sm text-slate-500">-</span></td>',
                    '<td class="px-3 py-2 align-top text-center">',
                    (isView ? '' : '   <button type="button" class="btn-hapus-item p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Item"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>'),
                    '</td>'
                ].join('');

                placeholder.style.display = 'none';
                tbody.appendChild(tr);

                var $tr = $(tr);
                var $sel = $tr.find('select.tindakan-select');
                $sel.append(optionsHtml);
                $sel.val(item.tindakan_id ? String(item.tindakan_id) : null);
                filterOptions($sel);

                if (!isView) {
                    $sel.select2({ placeholder: 'Cari Pemeriksaan...', allowClear: true, width: '100%' });
                }

                $sel.on('change', function () {
                    var opt = $sel.find('option:selected');
                    renderMeta($tr, item = { satuan: opt.attr('data-satuan') || '', nilai_normal: opt.attr('data-nilai-normal') || '' });
                    if ($sel.val()) {
                        placeholder.style.display = 'none';
                    }
                });

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

                $sel.trigger('change');
            }

            document.getElementById('btnTambahItem').addEventListener('click', function () {
                if (isView) return;
                addRow();
            });

            if (bagianSelect) {
                bagianSelect.addEventListener('change', function () {
                    document.querySelectorAll('select.tindakan-select').forEach(function (sel) {
                        filterOptions($(sel));
                        if (sel.value && sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].disabled) {
                            $(sel).val(null).trigger('change');
                            try { $(sel).select2('close'); } catch (e) {}
                        }
                    });
                });
            }

            if (existingItems.length) {
                existingItems.forEach(function (item) { addRow(item); });
            } else if (isView) {
                addRow();
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('searchInputOrder').addEventListener('input', function () {
                var filter = this.value.toLowerCase();
                document.querySelectorAll('.riwayat-item').forEach(function (item) {
                    item.style.display = item.textContent.toLowerCase().includes(filter) ? '' : 'none';
                });
            });
        });
    </script>
@endsection