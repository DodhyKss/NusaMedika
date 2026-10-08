{{-- Opsi dropdown Obat/BMHP untuk form EMR Tindakan Medis.

     BARANG memakai Select2 AJAX (`api.barang.search`), jadi partial ini hanya
     perlu merender OPSI YANG SEDANG DIPILIH (agar nilai lama tampil saat form
     dibuka dalam mode edit). Sisanya diambil dari server saat user mengetik
     pencarian — tidak perlu memuat ribuan `<option>` sekaligus.

     NB: filter jenis barang diterapkan lewat query `jenis_barang_id`, bukan
     dengan memfilter daftar di browser.

     Variabel: $jenisId, $terpilih, $barangMap --}}
@php
    $jenisLabel = $jenisId ? ($jenisNama[$jenisId] ?? 'Obat/BMHP') : 'Obat/BMHP';
@endphp
<option value="">-- Pilih {{ $jenisLabel }} --</option>
@if ($terpilih !== '' && isset($barangMap[$terpilih]))
    {{-- Opsi nilai yang sedang tersimpan, supaya Select2 menampilkan isian lama. --}}
    <option value="{{ $terpilih }}" selected>
        {{ $barangMap[$terpilih]['kode'] ? $barangMap[$terpilih]['kode'].' - ' : '' }}{{ $barangMap[$terpilih]['nama'] }}@if ($barangMap[$terpilih]['satuan']) ({{ $barangMap[$terpilih]['satuan'] }})@endif
    </option>
@endif