@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Pemesanan</h1>
        <p class="text-sm text-slate-500 mt-1">Perbaiki data pemesanan ({{ $pemesanan->no_pemesanan }}) beserta item barang.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('buat_pesanan.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Pemesanan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbaiki data pemesanan beserta item barang.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('buat_pesanan.update', $pemesanan->pemesanan_id) }}" method="POST" id="formEditPemesanan">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Bagian / Gudang Tujuan -->
                <div>
                    <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bagian / Gudang Tujuan</label>
                    <select id="bagian_id" name="bagian_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <option value="">-- Pilih Bagian / Gudang --</option>
                        @foreach ($gudangs as $g)
                            <option value="{{ $g->bagian_id }}" @selected((string) old('bagian_id', $pemesanan->bagian_id) === (string) $g->bagian_id)>{{ $g->nama_bagian }}</option>
                        @endforeach
                    </select>
                    @error('bagian_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal -->
                <div>
                    <label for="tanggal_pemesanan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Pemesanan</label>
                    <input type="date" id="tanggal_pemesanan" name="tanggal_pemesanan" value="{{ old('tanggal_pemesanan', optional(\Carbon\Carbon::parse($pemesanan->tanggal_pemesanan))->format('Y-m-d')) }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    @error('tanggal_pemesanan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Keterangan -->
            <div class="mt-5">
                <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="2" placeholder="Catatan tambahan pemesanan..."
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('keterangan', $pemesanan->keterangan) }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Items -->
            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800">Item Barang</h3>
                    <button type="button" id="btnTambahItem" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left" id="tabelItem" style="min-width: 1200px;">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Barang</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Distributor</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Jumlah</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Jual</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Subtotal</th>
                                <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">#</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItem" class="divide-y divide-slate-100">
                            @forelse ($pemesanan->details as $d)
                                @php
                                    $pr = (float) $d->jumlah_pesan * (float) $d->harga_beli;
                                @endphp
                                <tr data-item="existing-{{ $d->pemesanan_detail_id }}" data-subtotal="{{ $pr }}" data-supplier="{{ $d->supplier_id }}" data-supplier-label="{{ $d->supplier->nama_supplier ?? '-' }}" data-distributor="{{ $d->distributor_id }}" data-distributor-label="{{ $d->distributor->nama_supplier ?? '-' }}">
                                    <td class="px-3 py-2 align-top">
                                        <select name="barang_id[]" class="barang-select text-sm w-full" style="min-width:200px;">
                                            <option value=""></option>
                                            <option value="{{ $d->barang_id }}" selected>{{ $d->barang->nama_barang ?? '-' }} ({{ $d->barang->textSatuan() ?? '-' }})</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select name="supplier_id[]" class="sup-select text-sm w-full text-slate-700" style="min-width:150px;" disabled>
                                            <option value="">-- Supplier --</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <select name="distributor_id[]" class="dist-select text-sm w-full text-slate-700" style="min-width:150px;" disabled>
                                            <option value="">-- Distributor --</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-top text-center"><span class="text-satuan text-xs text-slate-600">{{ $d->barang->textSatuan() ?? '-' }}</span></td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="number" name="jumlah_pesan[]" min="1" step="any" value="{{ $d->jumlah_pesan }}" class="inp-jumlah text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:90px;">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" name="harga_beli[]" value="{{ number_format((float) $d->harga_beli, 0, ',', '.') }}" inputmode="numeric" class="inp-harga text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:110px;">
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" name="harga_jual[]" value="{{ $d->harga_jual !== null ? number_format((float) $d->harga_jual, 0, ',', '.') : '0' }}" inputmode="numeric" class="inp-harga-jual text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:110px;">
                                    </td>
                                    <td class="px-3 py-2 align-top text-right text-xs font-semibold text-slate-700 whitespace-nowrap"><span class="txt-subtotal">Rp {{ number_format($pr, 0, ',', '.') }}</span></td>
                                    <td class="px-3 py-2 align-top text-center">
                                        <button type="button" class="btn-hapus-item p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="rowPlaceholder" class="text-center">
                                    <td colspan="9" class="px-3 py-6 text-xs text-slate-400">Belum ada item. Klik "Tambah Item" untuk menambahkan barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-slate-50/50">
                            <tr>
                                <td colspan="7" class="px-3 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Total</td>
                                <td class="px-3 py-3 text-right text-sm font-bold text-slate-800" id="totalPemesanan">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('buat_pesanan.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var apiUrl = "{{ route('api.barang.search') }}";
        var tbody = document.getElementById('tbodyItem');
        var placeholder = document.getElementById('rowPlaceholder');
        var totalEl = document.getElementById('totalPemesanan');
        var counter = 0;

        var supplierOptions = @json($suppliers->pluck('nama_supplier', 'supplier_id'));
        var distributorOptions = @json($distributors->pluck('nama_supplier', 'supplier_id'));
        var barangSupplierMap = @json($barangSupplierMap);
        var supplierDistributorMap = @json($supplierDistributorMap);
        var headerSupplier = @json($pemesanan->supplier_id);
        var headerDistributor = @json($pemesanan->distributor_id);

        function suppliersForBarang(barangId) {
            var daftar = [];
            (barangSupplierMap[barangId] || []).forEach(function (supId) {
                if (supplierOptions[supId] !== undefined) {
                    daftar.push(supId);
                }
            });
            return daftar;
        }

        function distributorsForSupplier(supId) {
            var daftar = [];
            (supplierDistributorMap[supId] || []).forEach(function (distId) {
                if (distributorOptions[distId] !== undefined) {
                    daftar.push(distId);
                }
            });
            return daftar;
        }

        function renderRowSupplier(tr) {
            var $tr = $(tr);
            var $sup = $tr.find('select.sup-select');
            var barangId = $tr.find('select.barang-select').val();
            var allowed = barangId ? suppliersForBarang(barangId) : [];

            $sup.find('option[value]').not('[value=""]').remove();
            allowed.forEach(function (supId) {
                $sup.append($('<option>').val(supId).text(supplierOptions[supId]));
            });
            $sup.prop('disabled', !barangId);

            if (!barangId) {
                $sup.val('');
            } else if (tr.dataset.supplier) {
                if (allowed.indexOf(tr.dataset.supplier) !== -1) {
                    $sup.val(tr.dataset.supplier);
                } else if (!$sup.val()) {
                    $sup.append($('<option>').val(tr.dataset.supplier).text(tr.dataset.supplierLabel || 'Supplier'));
                    $sup.val(tr.dataset.supplier);
                }
            } else if (String(tr.dataset.item).indexOf('existing-') === 0 && headerSupplier && allowed.indexOf(String(headerSupplier)) !== -1) {
                $sup.val(String(headerSupplier));
            }

            renderRowDistributor(tr);
        }

        function renderRowDistributor(tr) {
            var $tr = $(tr);
            var $dist = $tr.find('select.dist-select');
            var supId = $tr.find('select.sup-select').val();
            var allowed = supId ? distributorsForSupplier(supId) : [];

            $dist.find('option[value]').not('[value=""]').remove();
            allowed.forEach(function (distId) {
                $dist.append($('<option>').val(distId).text(distributorOptions[distId]));
            });
            $dist.prop('disabled', !supId);

            if (!supId) {
                $dist.val('');
            } else if (tr.dataset.distributor) {
                if (allowed.indexOf(tr.dataset.distributor) !== -1) {
                    $dist.val(tr.dataset.distributor);
                } else if (!$dist.val()) {
                    $dist.append($('<option>').val(tr.dataset.distributor).text(tr.dataset.distributorLabel || 'Distributor'));
                    $dist.val(tr.dataset.distributor);
                }
            } else if (String(tr.dataset.item).indexOf('existing-') === 0 && headerDistributor && allowed.indexOf(String(headerDistributor)) !== -1) {
                $dist.val(String(headerDistributor));
            }
        }

        function rupiah(v) {
            return 'Rp ' + Number(v || 0).toLocaleString('id-ID');
        }

        function updateTotal() {
            var total = 0;
            tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
                total += parseFloat(tr.dataset.subtotal || 0);
            });
            totalEl.textContent = rupiah(total);
        }

        function bindRow(tr, $tr) {
            $tr.find('select.barang-select').on('select2:select', function () {
                var opt = $(this).find('option:selected');
                var satuan = opt.attr('data-satuan') || '-';
                var harga = opt.attr('data-harga-beli') || '0';

                tr.querySelector('.text-satuan').textContent = satuan;
                var $harga = $tr.find('.inp-harga');
                if (!$harga.val() || $harga.val() === '0') {
                    $harga.val(Number(harga).toLocaleString('id-ID'));
                }
                hitungSubtotal(tr);
                renderRowSupplier(tr);
            });

            $tr.find('.sup-select').on('change', function () {
                renderRowDistributor(tr);
            });

            $tr.find('.inp-jumlah').on('input', function () { hitungSubtotal(tr); });
            $tr.find('.inp-harga').on('input', function () {
                var v = this.value.replace(/[^\d]/g, '');
                this.value = v ? Number(v).toLocaleString('id-ID') : '';
                hitungSubtotal(tr);
            });
            $tr.find('.inp-harga-jual').on('input', function () {
                var v = this.value.replace(/[^\d]/g, '');
                this.value = v ? Number(v).toLocaleString('id-ID') : '';
            });

            $tr.find('.btn-hapus-item').on('click', function () {
                $tr.find('select.barang-select').select2('destroy');
                tr.remove();
                if (!tbody.querySelector('tr[data-item]')) {
                    placeholder.style.display = '';
                }
                updateTotal();
            });
        }

        function hitungSubtotal(tr) {
            var jumlah = parseFloat(tr.querySelector('.inp-jumlah').value || 0);
            var harga = parseFloat((tr.querySelector('.inp-harga').value || '0').replace(/[^\d]/g, '')) || 0;
            var sub = jumlah * harga;
            tr.querySelector('.txt-subtotal').textContent = 'Rp ' + Number(sub).toLocaleString('id-ID');
            tr.dataset.subtotal = sub;
            updateTotal();
        }

        // Inisialisasi baris existing
        tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
            var $tr = $(tr);
            var $sel = $tr.find('select.barang-select');
            window.initBarangSelect($sel, apiUrl);
            bindRow(tr, $tr);
            renderRowSupplier(tr);
        });

        function addRow() {
            counter++;
            var tr = document.createElement('tr');
            tr.dataset.item = counter;
            tr.dataset.subtotal = '0';
            tr.innerHTML = [
                '<td class="px-3 py-2 align-top">',
                '   <select name="barang_id[]" class="barang-select text-sm w-full" style="min-width:200px;"></select>',
                '</td>',
                '<td class="px-3 py-2 align-top">',
                '   <select name="supplier_id[]" class="sup-select text-sm w-full text-slate-700" style="min-width:150px;" disabled>',
                '       <option value="">-- Supplier --</option>',
                '   </select>',
                '</td>',
                '<td class="px-3 py-2 align-top">',
                '   <select name="distributor_id[]" class="dist-select text-sm w-full text-slate-700" style="min-width:150px;" disabled>',
                '       <option value="">-- Distributor --</option>',
                '   </select>',
                '</td>',
                '<td class="px-3 py-2 align-top text-center"><span class="text-satuan text-xs text-slate-600">-</span></td>',
                '<td class="px-3 py-2 align-top">',
                '   <input type="number" name="jumlah_pesan[]" min="1" step="any" value="1" class="inp-jumlah text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:90px;">',
                '</td>',
                '<td class="px-3 py-2 align-top">',
                '   <input type="text" name="harga_beli[]" value="0" inputmode="numeric" class="inp-harga text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:110px;">',
                '</td>',
                '<td class="px-3 py-2 align-top">',
                '   <input type="text" name="harga_jual[]" value="0" inputmode="numeric" class="inp-harga-jual text-sm w-full text-right border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none" style="min-width:110px;">',
                '</td>',
                '<td class="px-3 py-2 align-top text-right text-xs font-semibold text-slate-700 whitespace-nowrap"><span class="txt-subtotal">Rp 0</span></td>',
                '<td class="px-3 py-2 align-top text-center">',
                '   <button type="button" class="btn-hapus-item p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Item">',
                '       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
                '   </button>',
                '</td>'
            ].join('');

            placeholder.style.display = 'none';
            tbody.appendChild(tr);

            var $tr = $(tr);
            var $sel = $tr.find('select.barang-select');
            window.initBarangSelect($sel, apiUrl);
            bindRow(tr, $tr);
            updateTotal();
        }

        document.getElementById('btnTambahItem').addEventListener('click', function () { addRow(); });

        document.getElementById('formEditPemesanan').addEventListener('submit', function () {
            tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
                var h = tr.querySelector('.inp-harga');
                if (h) h.value = (h.value || '').replace(/[^\d]/g, '');
                var hj = tr.querySelector('.inp-harga-jual');
                if (hj) hj.value = (hj.value || '').replace(/[^\d]/g, '');
            });
        });

        updateTotal();
    })();
</script>
@endpush