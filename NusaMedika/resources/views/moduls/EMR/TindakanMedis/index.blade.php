@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Form Tindakan Medis';
        $subtitleForm = 'Permintaan tindakan atas persetujuan dokter beserta hasil dan pemakaian obat/BMHP.';
        $routeName = null;
        $routeUrl = url('emr/form/tindakan_medis');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Baris obat/BMHP HANYA lahir dari modal "Tambah Obat/BMHP" (pilih satu
        // batch per baris), jadi jangan pernah membuat baris kosong otomatis.
        $barisObat = is_array($barisObat ?? null) ? $barisObat : [];
        $counterAwal = $barisObat === [] ? 0 : max(array_keys($barisObat));
        $pakaiObat = ($emr_data['pemakaian_obat_bmhp'] ?? 'Tidak') === 'Ya';
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Tindakan Medis"
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
                slug="tindakan_medis"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['tanggal_tindakan' => 'Tanggal', 'waktu_tindakan' => 'Jam', 'jenis_permintaan' => 'Permintaan']"
                :badges="[
                    'jenis_permintaan' => [
                        'CITO' => ['label' => 'CITO', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                        'BIASA' => ['label' => 'BIASA', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                    ],
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= Permintaan Tindakan ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Permintaan Tindakan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Atas Persetujuan Dokter --}}
                        <x-select_dokter
                            :selected="old('dokter_persetujuan_id', $emr_data['dokter_persetujuan_id'] ?? '')"
                            name="dokter_persetujuan_id"
                            id="dokter_persetujuan_id"
                            label="Atas Persetujuan Dokter"
                            placeholder="-- Pilih Dokter --"
                            required
                        />

                        {{-- Tindakan (Master Tindakan) --}}
                        <div>
                            <label for="tindakan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tindakan <span class="text-red-500">*</span>
                            </label>
                            <select id="tindakan_id" name="tindakan_id"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                <option value="">-- Pilih Tindakan --</option>
                                @foreach ($tindakans as $t)
                                    <option value="{{ $t->tindakan_id }}" @selected((int) old('tindakan_id', $emr_data['tindakan_id'] ?? 0) === (int) $t->tindakan_id)>
                                        {{ $t->nama_tindakan }} ({{ $t->kode_tindakan }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-slate-400">Daftar dari Master Tindakan.</p>
                            @error('tindakan_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Jenis Permintaan --}}
                        <div>
                            <label for="jenis_permintaan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Jenis Permintaan <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_permintaan" name="jenis_permintaan"
                                    class="w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                @foreach (['BIASA' => 'Biasa', 'CITO' => 'CITO'] as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('jenis_permintaan', $emr_data['jenis_permintaan'] ?? 'BIASA') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('jenis_permintaan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <label for="tanggal_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tanggal Tindakan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_tindakan" name="tanggal_tindakan"
                                   value="{{ old('tanggal_tindakan', $emr_data['tanggal_tindakan'] ?? '') }}"
                                   class="w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_tindakan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jam --}}
                        <div>
                            <label for="waktu_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Jam Tindakan <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_tindakan" name="waktu_tindakan" step="60"
                                   value="{{ old('waktu_tindakan', substr((string) ($emr_data['waktu_tindakan'] ?? ''), 0, 5)) }}"
                                   class="w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_tindakan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= Pasca Tindakan ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <span class="text-slate-600 font-medium text-[15px]">Kondisi Pasca Tindakan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="hasil_kondisi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Hasil/Kondisi Pasca Tindakan
                        </label>
                        <textarea id="hasil_kondisi" name="hasil_kondisi" rows="3"
                                  placeholder="Contoh: Tindakan selesai tanpa komplikasi, vital sign dalam batas normal"
                                  class="w-full text-sm border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('hasil_kondisi', $emr_data['hasil_kondisi'] ?? '') }}</textarea>
                        @error('hasil_kondisi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                                  placeholder="Catatan tambahan bila diperlukan"
                                  class="w-full text-sm border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('keterangan', $emr_data['keterangan'] ?? '') }}</textarea>
                        @error('keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= Pemakaian Obat / BMHP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-yellow-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-yellow-700 font-medium text-[15px]">Pemakaian Obat / BMHP</span>
                    @unless ($isView)
                        <button type="button" id="btnTambahObat"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"></path></svg>
                            Cari &amp; Tambah Obat/BMHP
                        </button>
                    @endunless
                </div>

                <div class="p-5 bg-white space-y-4 text-sm">
                    {{-- Radio Ya / Tidak --}}
                    <div class="flex gap-6">
                        @foreach (['Ya', 'Tidak'] as $pilihan)
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="pemakaian_obat_bmhp" value="{{ $pilihan }}" class="pakai-obat text-amber-600 focus:ring-amber-500 w-4 h-4"
                                       @checked(old('pemakaian_obat_bmhp', $emr_data['pemakaian_obat_bmhp'] ?? 'Tidak') === $pilihan)>
                                <span class="text-slate-700">{{ $pilihan }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('pemakaian_obat_bmhp')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Panel baris obat/BMHP: Jenis -> Barang -> Batch -> Jumlah --}}
                    <div id="panelObat" class="{{ $pakaiObat ? '' : 'hidden' }} space-y-3">
                        {{-- Kosong: baris dibuat lewat modal, bukan baris kosong otomatis. --}}
                        <div id="panelObatKosong" class="{{ count($barisObat) ? 'hidden' : '' }} py-6 text-center bg-slate-50 border border-dashed border-slate-300 rounded-lg">
                            <p class="text-sm text-slate-500">Belum ada obat/BMHP yang dipilih.</p>
                            <p class="text-xs text-slate-400 mt-1">Klik "Cari &amp; Tambah Obat/BMHP" untuk memilih jenis barang, lalu obat/BMHP dan nomor batch-nya.</p>
                        </div>

                        <div id="wrapTabelObat" class="overflow-x-auto rounded-lg border border-slate-200 {{ count($barisObat) ? '' : 'hidden' }}">
                            <table class="w-full text-left" style="min-width: 860px;">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:40px;">No</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">Jenis</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Obat / BMHP</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:200px;">No. Batch</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:110px;">Jumlah</th>
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:40px;">#</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyObat" class="divide-y divide-slate-100">
                                    @foreach ($barisObat as $index => $item)
                                        <tr data-item="{{ $index }}">
                                            <td class="px-3 py-2 text-center text-slate-400 align-middle">{{ $loop->iteration }}</td>

                                            {{-- 1. Jenis Barang (Obat / BMHP) --}}
                                            <td class="px-3 py-2 align-top">
                                                <select name="jenis_barang_{{ $index }}" data-role="jenis"
                                                        class="jenis-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                                    <option value="">-- Pilih --</option>
                                                    @foreach ($jenisBarangs as $jb)
                                                        <option value="{{ $jb->barang_jenis_id }}" @selected((int) $item['jenis_barang_id'] === (int) $jb->barang_jenis_id)>{{ $jb->nama_jenis_barang }}</option>
                                                    @endforeach
                                                </select>
                                                @error('jenis_barang_'.$index)
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            {{-- 2. Obat / BMHP, hanya yang sesuai jenis terpilih --}}
                                            <td class="px-3 py-2 align-top">
                                                <select name="obat_{{ $index }}" data-role="barang"
                                                        class="barang-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                                    @include('moduls.EMR.TindakanMedis.partials.opsiObat', [
                                                        'jenisId' => (int) $item['jenis_barang_id'],
                                                        'terpilih' => (string) $item['barang_id'],
                                                        'barangMap' => $barangMap,
                                                        'jenisNama' => $jenisBarangs->pluck('nama_jenis_barang', 'barang_jenis_id'),
                                                    ])
                                                </select>
                                                @error('obat_'.$index)
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            {{-- 3. Nomor Batch + sisa stok, hanya yang tersedia --}}
                                            <td class="px-3 py-2 align-top">
                                                <select name="batch_{{ $index }}" data-role="batch"
                                                        class="batch-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                                    @include('moduls.EMR.TindakanMedis.partials.opsiBatch', [
                                                        'barangId' => (string) $item['barang_id'],
                                                        'terpilih' => (string) $item['no_batch'],
                                                        'stokMap' => $stokMap,
                                                        'tampilkanSisa' => ! $isView,
                                                    ])
                                                </select>
                                                {{-- Sisa stok tidak ditampilkan pada aksi Lihat --}}
                                                <div class="mt-1 text-[11px] leading-4" data-role="info">
                                                    @if (! $isView && $item['no_batch'] !== '')
                                                        <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 rounded px-1.5 py-0.5 font-medium">
                                                            sisa {{ rtrim(rtrim(number_format($item['stok'], 2, ',', '.'), '0'), ',') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @error('batch_'.$index)
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            {{-- 4. Jumlah --}}
                                            <td class="px-3 py-2 align-top">
                                                <input type="number" step="0.01" min="0" name="jumlah_{{ $index }}" data-role="jumlah"
                                                       value="{{ old('jumlah_'.$index, $item['jumlah']) }}" placeholder="0"
                                                       class="jumlah-obat w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                                @error('jumlah_'.$index)
                                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            <td class="px-3 py-2 align-top text-center">
                                                @unless ($isView)
                                                    <button type="button" data-role="hapus"
                                                            class="btn-hapus-obat p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Baris">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                @endunless
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-slate-400">Maksimal 20 baris. Pilih jenis barang, lalu obat/BMHP, lalu nomor batch yang tersedia. Jumlah tidak boleh melebihi sisa stok batch tersebut.</p>
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    {{-- ===================== MODAL PILIH OBAT/BMHP =====================
         Alur: pilih Jenis Barang -> cari & pilih Obat/BMHP (Select2 AJAX) ->
         pilih satu nomor batch dari tabel -> baris baru masuk ke form.
         Satu batch per baris, jadi "Simpan" menambah SATU baris. --}}
    <div id="modalObat" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Obat / BMHP</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih satu nomor batch untuk satu baris pemakaian.</p>
                </div>
                <button type="button" data-modal-tutup class="p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 rounded-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto">
                {{-- Langkah 1: jenis barang --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        1. Jenis Barang <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3" id="modalJenisBarang">
                        @foreach ($jenisBarangs as $jb)
                            <label class="inline-flex items-center gap-2 px-3 py-2 border border-slate-300 rounded-lg cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="modal_jenis_barang" value="{{ $jb->barang_jenis_id }}" class="text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="text-sm text-slate-700">{{ $jb->nama_jenis_barang }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Langkah 2: cari obat/BMHP --}}
                <div>
                    <label for="modalBarang" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        2. Obat / BMHP <span class="text-red-500">*</span>
                    </label>
                    <select id="modalBarang" class="w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"></select>
                    <p class="mt-1 text-xs text-slate-400">Ketik nama atau kode untuk mencari.</p>
                </div>

                {{-- Langkah 3: pilih satu batch --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        3. Nomor Batch <span class="text-red-500">*</span>
                    </label>
                    <div id="modalBatchKosong" class="py-5 text-center bg-slate-50 border border-dashed border-slate-300 rounded-lg text-xs text-slate-400">
                        Pilih jenis barang &amp; obat/BMHP terlebih dahulu.
                    </div>
                    <div id="modalBatchTabel" class="hidden overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:44px;">Pilih</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Batch</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">Tgl. Expired</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right" style="width:110px;">Sisa Stok</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right" style="width:140px;">Jumlah Ambil</th>
                                </tr>
                            </thead>
                            <tbody id="modalBatchBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>

                    {{-- Jumlah Ambil diisi langsung pada kolom tabel di atas;
                         tidak ada input jumlah tambahan di bawah tabel. --}}
                    <p id="modalJumlahInfo" class="mt-2 text-xs text-slate-400">Pilih nomor batch terlebih dahulu.</p>
                </div>
            </div>

            <div class="px-5 py-3.5 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" data-modal-tutup class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                <button type="button" id="btnSimpanObat"
                        class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Simpan ke Form
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var panel     = document.getElementById('panelObat');
        var tbody     = document.getElementById('tbodyObat');
        var btnTambah = document.getElementById('btnTambahObat');
        if (!panel || !tbody) return;

        var isView  = {{ $isView ? 'true' : 'false' }};
        var counter = {{ $counterAwal }};

        var URL_BARANG = @json(route('api.barang.search'));
        var stokMap    = @json($stokMap);
        var jenisNama  = @json($jenisBarangs->pluck('nama_jenis_barang', 'barang_jenis_id'));

        var BATAS_BARIS = 20;
        var ADA_SELECT2 = !isView && (typeof $ !== 'undefined') && (typeof $.fn.select2 === 'function');

        // Tampil/sembunyi memakai INLINE STYLE, bukan class `hidden`.
        //
        // Tailwind v4 menaruh `.hidden` (display:none) SEBELUM `.inline-flex`
        // (display:inline-flex) di hasil CSS. Keduanya specificity sama, jadi
        // aturan yang belakangan menang: `inline-flex` selalu mengalahkan
        // `hidden` pada elemen yang punya kedua class itu — tombol "Tambah"
        // tetap terlihat walau `hidden` sudah ditambahkan.
        function tampilkan(el, tampil, display) {
            if (!el) return;

            // Class `hidden` ikut dilepas/dipasang, karena setting inline style
            // ke '' saja TIDAK cukup: bila markup masih memuat class `hidden`,
            // elemen tetap `display:none` dari aturan class tersebut.
            el.classList.toggle('hidden', !tampil);
            el.style.display = tampil ? (display || '') : 'none';
        }

        function esc(v) {
            var d = document.createElement('div');
            d.textContent = v == null ? '' : String(v);
            return d.innerHTML;
        }

        function formatStok(v) {
            var s = Number(v || 0).toFixed(2);
            return s.indexOf('.') > -1 ? s.replace(/0+$/, '').replace(/\.$/, '') : s;
        }

        function formatTanggal(iso) {
            if (!iso) return '-';
            var t = String(iso).split('-');
            return t.length === 3 ? t[2] + '/' + t[1] + '/' + t[0] : String(iso);
        }

        function batchList(barangId) {
            var batches = stokMap[String(barangId)] || {};
            return Object.keys(batches)
                .map(function (noBatch) {
                    return { noBatch: noBatch, jumlah: Number(batches[noBatch].jumlah), tgl_expired: batches[noBatch].tgl_expired, kedaluwarsa: !!batches[noBatch].kedaluwarsa };
                })
                // Urutan FEFO: yang paling mendekati kedaluwarsa lebih dulu.
                .sort(function (a, b) {
                    if (!a.tgl_expired) return 1;
                    if (!b.tgl_expired) return -1;
                    return a.tgl_expired < b.tgl_expired ? -1 : 1;
                });
        }

        // Peringatan memakai komponen confirm-alert (window.nusaConfirm) supaya
        // konsisten dengan dialog lain di aplikasi. Fallback ke alert() kalau
        // komponen belum termuat.
        //
        // PENTING: jangan tulis tag komponen blade di dalam blok <script>,
        // termasuk di komentar — blade mengompilasinya sehingga markup komponen
        // ikut disisipkan ke dalam script dan membuat script tidak berjalan.
        function peringatan(judul, pesan, danger) {
            if (typeof window.nusaConfirm === 'function') {
                window.nusaConfirm({
                    title: judul,
                    message: pesan,
                    confirmText: 'Mengerti',
                    cancelText: 'Tutup',
                    danger: !!danger
                });
                return;
            }

            window.alert(pesan);
        }

        function stokTersedia(barangId, noBatch) {
            var b = (stokMap[String(barangId)] || {})[noBatch];
            return (!b || Number(b.jumlah) <= 0) ? null : Number(b.jumlah);
        }

        // HTML <option> untuk dropdown nomor batch (SELECT BIASA, tanpa Select2).
        // Batch kedaluwarsa tetap ditampilkan tapi ditandai dan tidak bisa dipilih.
        function opsiBatchHtml(barangId, terpilih) {
            var html = '<option value="">-- Pilih Batch --</option>';

            batchList(barangId).forEach(function (b) {
                if (b.jumlah <= 0) return;

                var label = b.noBatch + ' \u2014 sisa ' + formatStok(b.jumlah) +
                            (b.tgl_expired ? ' \u00b7 exp ' + formatTanggal(b.tgl_expired) : '') +
                            (b.kedaluwarsa ? ' (Kedaluwarsa)' : '');

                html += '<option value="' + esc(b.noBatch) + '"' +
                        (String(b.noBatch) === String(terpilih) ? ' selected' : '') +
                        (b.kedaluwarsa && String(b.noBatch) !== String(terpilih) ? ' disabled' : '') + '>' +
                        esc(label) + '</option>';
            });

            return html;
        }

        // ------------------------------------------------------------------
        // Select2 untuk dropdown BARANG (Ajax), mencari per jenis barang.
        //
        // Aturan penting supaya tidak muncul "double input" / dropdown macet:
        //  1) SELALU destroy sebelum init ulang — Select2 yang sudah terpasang
        //     mengabaikan pemanggilan select2() berikutnya sehingga muncul
        //     container dropdown kedua di samping elemen asli.
        //  2) JANGAN memakai `disabled` untuk menonaktifkan baris — Select2
        //     menyimpan status disabled saat di-init sehingga mengaktifkan
        //     kembali elemen asli tidak berefek. Nonaktifkan lewat `name`.
        //
        // Dropdown NOMOR BATCH sengaja memakai <select> biasa (bukan Select2):
        // hanya satu batch per baris, jadi daftar pendek tidak perlu pencarian.
        // ------------------------------------------------------------------
        function pasangBarangAjax($el, jenisId, placeholder) {
            if (!ADA_SELECT2) return;

            if ($el.data('select2')) $el.select2('destroy');

            $el.select2({
                placeholder: placeholder || 'Cari...',
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
                delay: 250,
                minimumInputLength: 1,
                ajax: {
                    url: URL_BARANG,
                    dataType: 'json',
                    data: function (params) {
                        // Jenis barang ikut dikirim supaya cascade tetap berlaku.
                        return { q: params.term || '', jenis_barang_id: jenisId || '', limit: 50 };
                    },
                    processResults: function (data) { return { results: data.results || [] }; },
                    cache: true
                }
            });
        }

        // ------------------------------------------------------------------
        // Satu baris obat/BMHP di form
        // ------------------------------------------------------------------
        function pasangBaris(tr, index) {
            tr.dataset.item = index;

            var $jenis  = $(tr).find('[data-role="jenis"]');
            var $barang = $(tr).find('[data-role="barang"]');
            var $batch  = $(tr).find('[data-role="batch"]');
            var $info   = $(tr).find('[data-role="info"]');
            var $jumlah = $(tr).find('[data-role="jumlah"]');
            var $hapus  = $(tr).find('[data-role="hapus"]');

            // --- 2. Barang: Select2 AJAX. Daftarnya diambil dari server, jadi
            //        baris yang sudah ada cukup menampilkan nilai terpilih.
            function isiBarang(jenisId, terpilih, teks) {
                var html = '<option value="">-- Pilih Obat/BMHP --</option>';
                if (terpilih) html += '<option value="' + esc(terpilih) + '" selected>' + esc(teks || '') + '</option>';

                $barang.empty().append(html);
                if ($barang.data('select2')) $barang.val(terpilih || null).trigger('change.select2');
                pasangBarangAjax($barang, jenisId, 'Cari ' + (jenisNama[String(jenisId)] || 'Obat/BMHP') + '...');
            }

            // --- 3. Batch: SELECT BIASA, isi dari daftar stok barang terpilih.
            function isiBatch(barangId, terpilih) {
                $batch.html(opsiBatchHtml(barangId, terpilih));
                if (terpilih) $batch.val(String(terpilih));
            }

            function setInfo(barangId, noBatch) {
                if (!barangId || !noBatch) { $info.empty(); return; }

                var sisa = stokTersedia(barangId, noBatch);
                $info.html(sisa === null
                    ? '<span class="inline-block bg-red-50 text-red-600 border border-red-200 rounded px-1.5 py-0.5">batch tidak tersedia</span>'
                    : '<span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 rounded px-1.5 py-0.5 font-medium">sisa ' + esc(formatStok(sisa)) + '</span>');
            }

            $jenis.on('change', function () {
                isiBarang($(this).val(), '', '');
                isiBatch('', '');
                setInfo('', '');
                $jumlah.val('');
            });

            $barang.on('change', function () {
                isiBatch($(this).val(), '');
                setInfo($(this).val(), '');
                $jumlah.val('');
            });

            $batch.on('change', function () {
                setInfo($barang.val(), $(this).val());
            });

            // Jumlah tidak boleh melebihi sisa stok batch terpilih.
            $jumlah.on('blur', function () {
                if (this.value === '') return;
                var sisa = stokTersedia($barang.val(), $batch.val());
                if (sisa !== null && Number(this.value) > sisa) {
                    peringatan('Jumlah Terlalu Besar', 'Jumlah melebihi sisa stok batch (' + formatStok(sisa) + ').');
                    this.value = formatStok(sisa);
                }
            });

            if ($hapus.length) {
                $hapus.on('click', function () {
                    if ($barang.data('select2')) $barang.select2('destroy');
                    tr.remove();
                    nomorUrut();
                    perbaruiPanel();
                });
            }

            // Tampilkan sisa stok untuk batch yang sudah terpilih (mode edit).
            // Pada aksi Lihat badge sisa tidak ditampilkan sama sekali.
            if (!isView && $batch.val()) setInfo($barang.val(), $batch.val());
        }

        function nomorUrut() {
            tbody.querySelectorAll('tr[data-item]').forEach(function (tr, i) {
                // Guard: sel kolom nomor mungkin belum ada pada baris yang baru
                // dibuat, jadi penomoran tidak boleh menghentikan proses.
                var selNomor = tr.querySelector('td');
                if (selNomor) selNomor.textContent = String(i + 1);
            });
        }

        function perbaruiPanel() {
            var ada = tbody.querySelectorAll('tr[data-item]').length > 0;
            tampilkan(document.getElementById('panelObatKosong'), !ada);
            tampilkan(document.getElementById('wrapTabelObat'), ada);
        }

        // Saat "Tidak", atribut `name` DILAPAS supaya nilai tidak terkirim.
        // Jangan pakai `disabled` — lihat catatan di pasangBarangAjax().
        function syncPanel() {
            var dipilih = document.querySelector('input.pakai-obat:checked');
            var pakai = !!(dipilih && dipilih.value === 'Ya');

            tampilkan(panel, pakai);

            // Tombol "Cari & Tambah Obat/BMHP" HANYA aktif saat "Ya" dipilih:
            // disembunyikan sekaligus dinonaktifkan sebagai pengaman.
            tampilkan(btnTambah, pakai && !isView);
            if (btnTambah) btnTambah.disabled = !(pakai && !isView);

            tbody.querySelectorAll('select[data-role], input[data-role]').forEach(function (el) {
                if (!el.dataset.namaAsli) el.dataset.namaAsli = el.getAttribute('name') || '';
                if (pakai) el.setAttribute('name', el.dataset.namaAsli);
                else el.removeAttribute('name');
            });

            perbaruiPanel();
        }

        document.querySelectorAll('input.pakai-obat').forEach(function (r) {
            r.addEventListener('change', syncPanel);
        });

        tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
            pasangBaris(tr, tr.dataset.item);
        });

        // ==================================================================
        // MODAL PILIH OBAT/BMHP
        // ==================================================================
        var modal        = document.getElementById('modalObat');
        var modalBarang  = $('#modalBarang');
        var modalJenis   = document.getElementById('modalJenisBarang');
        var modalKosong  = document.getElementById('modalBatchKosong');
        var modalTabel   = document.getElementById('modalBatchTabel');
        var modalBody    = document.getElementById('modalBatchBody');
        var btnSimpan    = document.getElementById('btnSimpanObat');
        var infoJumlah   = document.getElementById('modalJumlahInfo');

        // Jumlah disimpan per nomor batch (satu baris = satu batch).
        var pilihan = { jenisId: '', barangId: '', teks: '', noBatch: '', jumlah: {} };

        // Kunci kolom jumlah pada tabel: hanya baris yang batch-nya SEDANG
        // DIPILIH yang boleh diisi, agar user tidak salah mengisi jumlah untuk
        // batch lain. Kolom ini SATU-SATUNYA tempat mengisi jumlah (tidak ada
        // input jumlah tambahan di bawah tabel).
        function kunciJumlahTabel() {
            modalBody.querySelectorAll('.modal-jumlah').forEach(function (inp) {
                var noBatch = inp.getAttribute('data-jumlah-batch');
                var meta = (stokMap[String(pilihan.barangId)] || {})[noBatch];
                var kedaluwarsa = !!(meta && meta.kedaluwarsa);

                inp.disabled = kedaluwarsa || noBatch !== pilihan.noBatch;
                inp.classList.toggle('bg-slate-100', inp.disabled);
            });
        }

        // Info sisa stok batch terpilih. Jumlah diisi langsung pada kolom
        // "Jumlah Ambil" di tabel, jadi tidak ada input jumlah terpisah.
        function syncJumlah() {
            var ada = !!(pilihan.barangId && pilihan.noBatch);

            btnSimpan.disabled = !ada;
            kunciJumlahTabel();

            if (!ada) {
                infoJumlah.textContent = 'Pilih nomor batch terlebih dahulu.';
                return;
            }

            var sisa = stokTersedia(pilihan.barangId, pilihan.noBatch);
            infoJumlah.innerHTML = 'Sisa stok batch <strong>' + pilihan.noBatch + '</strong>: ' +
                '<strong class="text-emerald-700">' + esc(formatStok(sisa)) + '</strong>';
        }

        // Cari baris form yang sudah memakai batch tersebut.
        // kembalikan: null | { tr, samaBarang, nomorBaris }
        //
        // Dua kasus ditangani:
        //  - barang sama persis  -> jumlah digabung ke baris itu
        //  - barang berbeda, tapi nomor batch sama -> ditolak, karena nomor
        //    batch bersifat unik untuk satu fisik barang
        function cariBarisBatch(noBatch, barangId) {
            var hasil = null;

            tbody.querySelectorAll('tr[data-item]').forEach(function (tr, i) {
                if (hasil) return;
                var $b = $(tr).find('[data-role="barang"]');
                var $t = $(tr).find('[data-role="batch"]');
                if (!$t.val() || String($t.val()) !== String(noBatch)) return;

                hasil = {
                    tr: tr,
                    samaBarang: !!barangId && $b.val() && String($b.val()) === String(barangId),
                    nomorBaris: i + 1
                };
            });

            return hasil;
        }

        function bukaModal() {
            // Pengaman: modal hanya boleh dibuka saat "Pemakaian Obat/BMHP" = Ya.
            var dipilih = document.querySelector('input.pakai-obat:checked');
            if (!dipilih || dipilih.value !== 'Ya') {
                return peringatan('Pilih "Ya" Dulu', 'Tombol ini hanya aktif bila Pemakaian Obat/BMHP dipilih "Ya".');
            }

            // Jenis barang yang terakhir dipakai DIPERTAHANKAN: radio di reset
            // (bukan hanya state JS-nya), lalu nilainya dibaca balik ke
            // `pilihan.jenisId`.
            //
            // Penting: kalau hanya state JS yang di-reset sedangkan radio tetap
            // tercentang, pengguna melihat "Obat" sudah terpilih lalu langsung
            // memilih barang — padahal `pilihan.jenisId` masih kosong, sehingga
            // muncul peringatan palsu "Jenis Barang Belum Dipilih".
            var radioJenis = modalJenis.querySelector('input[name="modal_jenis_barang"]:checked');
            var jenisDipakai = radioJenis ? radioJenis.value : '';

            modalJenis.querySelectorAll('input[name="modal_jenis_barang"]').forEach(function (r) {
                r.checked = (r.value === jenisDipakai);
            });

            pilihan = { jenisId: jenisDipakai, barangId: '', teks: '', noBatch: '', jumlah: {} };
            btnSimpan.disabled = true;
            modalBody.innerHTML = '';
            tampilkan(modalTabel, false);
            tampilkan(modalKosong, true);
            modalKosong.textContent = jenisDipakai
                ? 'Pilih obat/BMHP ' + jenisNama[jenisDipakai] + ' di kolom pencarian.'
                : 'Pilih jenis barang & obat/BMHP terlebih dahulu.';

            tampilkan(modal, true, 'flex');

            // Reset pencarian barang, tetap memakai jenis yang terakhir dipakai.
            modalBarang.empty().append('<option value=""></option>');
            if (modalBarang.data('select2')) modalBarang.val(null).trigger('change.select2');
            pasangBarangAjax(
                modalBarang,
                jenisDipakai,
                'Cari ' + (jenisNama[jenisDipakai] || 'Obat/BMHP') + '...'
            );
            if (modalBarang.data('select2')) modalBarang.find('.select2-selection').find('input').val('').trigger('input.select2');
        }

        function tutupModal() {
            tampilkan(modal, false);
        }

        modal.querySelectorAll('[data-modal-tutup]').forEach(function (b) {
            b.addEventListener('click', tutupModal);
        });
        modal.addEventListener('click', function (e) { if (e.target === modal) tutupModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupModal(); });

        // Langkah 1 -> 2: jenis dipilih, barulah pencarian barang aktif.
        modalJenis.querySelectorAll('input[name="modal_jenis_barang"]').forEach(function (r) {
            r.addEventListener('change', function () {
                pilihan.jenisId = this.value;
                modalBarang.empty().append('<option value=""></option>');
                if (modalBarang.data('select2')) modalBarang.val(null).trigger('change.select2');
                // Placeholder mengikuti jenis terpilih.
                pasangBarangAjax(modalBarang, pilihan.jenisId, 'Cari ' + (jenisNama[String(this.value)] || 'Obat/BMHP') + '...');
                if (modalBarang.data('select2')) modalBarang.find('.select2-selection').find('input').val('').trigger('input.select2');

                pilihan.barangId = '';
                pilihan.noBatch = '';
                pilihan.jumlah = {};
                modalBody.innerHTML = '';
                tampilkan(modalTabel, false);
                tampilkan(modalKosong, true);
                modalKosong.textContent = 'Pilih obat/BMHP di kolom pencarian.';
                syncJumlah();
            });
        });

        // Langkah 2 -> 3: barang dipilih, tampilkan daftar batch-nya.
        modalBarang.on('change', function () {
            var id = $(this).val();
            pilihan.barangId = id || '';
            pilihan.teks = id ? ($(this).find('option:selected').text() || '') : '';
            pilihan.noBatch = '';

            pilihan.noBatch = '';
            pilihan.jumlah = {};
            modalBody.innerHTML = '';
            syncJumlah();

            var batches = id ? batchList(id) : [];
            if (!batches.length) {
                tampilkan(modalTabel, false);
                tampilkan(modalKosong, true);
                modalKosong.textContent = id
                    ? 'Barang ini belum punya batch dengan stok di lokasi pasien.'
                    : 'Pilih obat/BMHP terlebih dahulu.';
                return;
            }

            var rows = batches.map(function (b) {
                var disabled = b.kedaluwarsa ? 'disabled' : '';
                var rowCls = b.kedaluwarsa ? 'bg-red-50/40 text-slate-400' : '';
                var badge = b.kedaluwarsa
                    ? ' <span class="inline-block bg-red-100 text-red-700 border border-red-200 rounded px-1.5 py-0.5 text-[10px] font-semibold align-middle">Kedaluwarsa</span>'
                    : '';

                // Kolom jumlah per baris batch: disabled bila batch kedaluwarsa.
                var jumlah = pilihan.jumlah[b.noBatch];
                var inputJml = '<input type="number" step="0.01" min="0" max="' + esc(formatStok(b.jumlah)) + '" ' +
                    'data-jumlah-batch="' + esc(b.noBatch) + '" value="' + esc(jumlah === undefined ? '' : jumlah) + '" ' +
                    'placeholder="0" ' + disabled +
                    ' class="modal-jumlah w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white outline-none ' + (disabled ? 'bg-slate-100' : '') + '">';

                return '<tr class="' + rowCls + '" data-no-batch="' + esc(b.noBatch) + '">' +
                    '<td class="px-3 py-2 text-center"><input type="radio" name="modal_batch" value="' + esc(b.noBatch) + '" class="modal-batch-pilih text-blue-600 focus:ring-blue-500 w-4 h-4" ' + disabled + '></td>' +
                    '<td class="px-3 py-2 font-mono text-xs">' + esc(b.noBatch) + badge + '</td>' +
                    '<td class="px-3 py-2 text-xs">' + (b.tgl_expired ? esc(formatTanggal(b.tgl_expired)) : '-') + '</td>' +
                    '<td class="px-3 py-2 text-xs text-right font-semibold">' + esc(formatStok(b.jumlah)) + '</td>' +
                    '<td class="px-3 py-2">' + inputJml + '</td>' +
                '</tr>';
            }).join('');

            modalBody.innerHTML = rows;
            tampilkan(modalKosong, false);
            tampilkan(modalTabel, true);

            modalBody.querySelectorAll('.modal-batch-pilih').forEach(function (r) {
                r.addEventListener('change', function () {
                    pilihan.noBatch = this.value;
                    syncJumlah();
                });
            });

            // Jumlah yang diketik pada kolom tabel disimpan per batch, dan
            // dibatasi tidak boleh melebihi sisa stok batch tersebut.
            modalBody.querySelectorAll('.modal-jumlah').forEach(function (inp) {
                var noBatch = inp.getAttribute('data-jumlah-batch');
                var meta = (stokMap[String(pilihan.barangId)] || {})[noBatch];

                inp.addEventListener('input', function () {
                    pilihan.jumlah[noBatch] = inp.value;
                });

                inp.addEventListener('blur', function () {
                    if (inp.value === '' || !meta) return;

                    if (Number(inp.value) > Number(meta.jumlah)) {
                        peringatan('Jumlah Terlalu Besar', 'Jumlah melebihi sisa stok batch (' + formatStok(meta.jumlah) + ').');
                        inp.value = formatStok(meta.jumlah);
                        pilihan.jumlah[noBatch] = inp.value;
                    }
                });
            });
        });

        // Simpan -> menambah SATU baris ke form.
        btnSimpan.addEventListener('click', function () {
            if (!pilihan.jenisId) {
                return peringatan('Jenis Barang Belum Dipilih', 'Pilih jenis barang terlebih dahulu.');
            }

            if (!pilihan.barangId) {
                return peringatan('Obat/BMHP Belum Dipilih', 'Pilih obat atau BMHP yang akan dipakai.');
            }

            if (!pilihan.noBatch) {
                return peringatan('Nomor Batch Belum Dipilih', 'Pilih satu nomor batch pada tabel.');
            }

            // Jumlah diambil dari kolom "Jumlah Ambil" pada baris batch terpilih.
            var jumlah = String(pilihan.jumlah[pilihan.noBatch] === undefined
                ? ''
                : pilihan.jumlah[pilihan.noBatch]).trim();

            if (jumlah === '') {
                return peringatan('Jumlah Belum Diisi', 'Tentukan jumlah yang akan diambil dari batch ' + pilihan.noBatch + '.');
            }

            var sisa = stokTersedia(pilihan.barangId, pilihan.noBatch);
            var jumlahNum = Number(jumlah);

            if (isNaN(jumlahNum) || jumlahNum <= 0) {
                return peringatan('Jumlah Tidak Valid', 'Jumlah harus berupa angka lebih besar dari 0.', true);
            }

            if (sisa === null) {
                return peringatan('Stok Batch Tidak Tersedia', 'Nomor batch ' + pilihan.noBatch + ' sudah habis / tidak tersedia.', true);
            }

            if (jumlahNum > sisa) {
                return peringatan('Jumlah Melebihi Stok', 'Jumlah ' + formatStok(jumlahNum) + ' melebihi sisa stok batch ' + pilihan.noBatch + ' (' + formatStok(sisa) + ').', true);
            }

            // Satu barang + satu batch = satu baris. Kalau batch yang sama sudah
            // dipakai baris lain, gabungkan jumlahnya ke baris itu daripada
            // menambah baris baru (bisa membuat baris dobel dengan jumlah sama).
            var bentrok = cariBarisBatch(pilihan.noBatch, pilihan.barangId);

            if (bentrok && !bentrok.samaBarang) {
                return peringatan('Nomor Batch Sudah Dipakai', 'Nomor batch ' + pilihan.noBatch + ' sudah dipakai untuk barang lain pada baris #' + bentrok.nomorBaris + '. Nomor batch hanya untuk satu barang.');
            }

            if (bentrok) {
                var trSama = bentrok.tr;
                var $jumlahSama = $(trSama).find('[data-role="jumlah"]');
                var lama = Number($jumlahSama.val() || 0);

                if (lama + jumlahNum > sisa) {
                    return peringatan('Jumlah Melebihi Stok', 'Jumlah total untuk batch ' + pilihan.noBatch + ' (' + formatStok(lama) + ' + ' + formatStok(jumlahNum) + ') melebihi sisa stok (' + formatStok(sisa) + ').', true);
                }

                $jumlahSama.val(formatStok(lama + jumlahNum));
                nomorUrut();
                perbaruiPanel();
                tutupModal();
                return peringatan('Barang Sudah Ditambahkan', 'Barang + batch ' + pilihan.noBatch + ' sudah ditambahkan pada baris #' + bentrok.nomorBaris + ', sehingga jumlah ' + formatStok(jumlahNum) + ' digabung ke baris tersebut. Total sekarang ' + formatStok(lama + jumlahNum) + '.');
            }

            pilihan.jumlah[pilihan.noBatch] = jumlahNum;

            if (tbody.querySelectorAll('tr[data-item]').length >= BATAS_BARIS) {
                peringatan('Batas Baris Tercapai', 'Maksimal ' + BATAS_BARIS + ' baris obat/BMHP per pencatatan.');
                return;
            }

            counter++;
            var tr = document.createElement('tr');
            // Nama field memakai suffix counter, jadi menghapus baris di tengah
            // tidak menggeser pasangan obat_N dengan jumlah_N.
            var opsiJenis = '<option value="">-- Pilih --</option>' + Object.keys(jenisNama).map(function (id) {
                return '<option value="' + id + '"' + (String(id) === String(pilihan.jenisId) ? ' selected' : '') + '>' + jenisNama[id] + '</option>';
            }).join('');

            tr.innerHTML = [
                '<td class="px-3 py-2 text-center text-slate-400 align-middle"></td>',
                '<td class="px-3 py-2 align-top"><select name="jenis_barang_' + counter + '" data-role="jenis" class="jenis-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white outline-none">' + opsiJenis + '</select></td>',
                '<td class="px-3 py-2 align-top"><select name="obat_' + counter + '" data-role="barang" class="barang-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white outline-none"><option value="' + esc(pilihan.barangId) + '" selected>' + esc(pilihan.teks) + '</option></select></td>',
                '<td class="px-3 py-2 align-top"><select name="batch_' + counter + '" data-role="batch" class="batch-select w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white outline-none"></select>' +
                    '<div class="mt-1 text-[11px] leading-4" data-role="info"></div></td>',
                '<td class="px-3 py-2 align-top"><input type="number" step="0.01" min="0" max="' + esc(formatStok(sisa)) + '" name="jumlah_' + counter + '" data-role="jumlah" value="' + esc(formatStok(jumlahNum)) + '" placeholder="0" class="jumlah-obat w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white outline-none"></td>',
                '<td class="px-3 py-2 align-top text-center"><button type="button" data-role="hapus" class="btn-hapus-obat p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Baris"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button></td>'
            ].join('');

            tbody.appendChild(tr);
            pasangBaris(tr, counter);
            // Baris dari modal: barang & batch sudah pasti, langsung tampilkan.
            $(tr).find('[data-role="barang"]').val(pilihan.barangId);
            $(tr).find('[data-role="batch"]').html(opsiBatchHtml(pilihan.barangId, pilihan.noBatch)).val(String(pilihan.noBatch));
            nomorUrut();
            perbaruiPanel();
            syncPanel();
            tutupModal();
        });

        if (btnTambah) btnTambah.addEventListener('click', bukaModal);

        syncPanel();
    });
    </script>

@endsection()
