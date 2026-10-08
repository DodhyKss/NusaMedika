{{-- Opsi dropdown Nomor Batch (SELECT BIASA, tanpa Select2).

     Satu batch per baris, jadi daftar pendek tidak perlu pencarian Select2.

     PENTING: jangan pakai blok `@php` di dalam blok `@php` / `@foreach`.
     Regex `@php` milik Blade bersifat greedy sehingga blok luar menelan blok
     di dalamnya, hasil kompilasi terpotong dan melempar ParseError
     ("unexpected token endforeach"). Karena itu di sini memakai tag PHP biasa.

     Variabel: $barangId, $terpilih, $stokMap, $tampilkanSisa
--}}
<option value="">-- Pilih Batch --</option>
<?php
    $batches = $barangId !== '' ? ($stokMap[$barangId] ?? []) : [];
    $tampilkanSisa = $tampilkanSisa ?? true;
?>
@foreach ($batches as $noBatch => $batch)
    <?php
        $keterangan = [];

        // Sisa stok tidak ditampilkan pada aksi Lihat.
        if ($tampilkanSisa) {
            $keterangan[] = 'sisa '.rtrim(rtrim(number_format($batch['jumlah'], 2, ',', '.'), '0'), ',');
        }

        if (! empty($batch['tgl_expired'])) {
            $keterangan[] = 'exp '.\Carbon\Carbon::parse($batch['tgl_expired'])->format('d/m/Y');
        }

        if ($batch['kedaluwarsa'] ?? false) {
            $keterangan[] = '(Kedaluwarsa)';
        }

        $label = $noBatch.($keterangan ? ' — '.implode(' · ', $keterangan) : '');
    ?>

    {{-- Batch kedaluwarsa tetap ditampilkan, tapi tidak bisa dipilih. --}}
    <option value="{{ $noBatch }}"
            @selected((string) $terpilih === (string) $noBatch)
            @disabled(($batch['kedaluwarsa'] ?? false) && (string) $terpilih !== (string) $noBatch)>{{ $label }}</option>
@endforeach