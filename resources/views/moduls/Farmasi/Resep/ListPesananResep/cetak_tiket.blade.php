<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak E-Tiket - {{ $resep->no_resep }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #000; width: 80mm; margin: 0 auto; font-size: 11px; line-height: 1.3; }
        @media print {
            @page { size: 80mm auto; margin: 5mm; }
            body { margin: 0; }
        }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
        .header h1 { font-size: 14px; letter-spacing: 1px; }
        .header p { font-size: 10px; }
        .title { text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 8px; }
        .resep-no { text-align: center; border: 2px dashed #000; border-radius: 4px; padding: 5px; font-size: 16px; font-weight: bold; letter-spacing: 2px; margin-bottom: 8px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info td { padding: 2px 0; font-size: 11px; vertical-align: top; }
        .info td.label { white-space: nowrap; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 3px 2px; text-align: left; font-size: 10px; }
        table.items td { padding: 3px 2px; vertical-align: top; font-size: 11px; }
        .footer { margin-top: 10px; border-top: 1px solid #000; padding-top: 6px; font-size: 9px; text-align: center; }
    </style>
</head>
<body onload="setTimeout(() => { window.print(); window.close(); }, 500);">
    @php
        $detailAktif = $resep->details->reject(fn ($d) => (float) ($d->jumlah_dispense ?? 0) <= 0);
        $depos = \App\Models\Bagian::aktif()
            ->whereIn('bagian_id', $detailAktif->pluck('bagian_dispense_id')->unique()->filter())
            ->pluck('nama_bagian', 'bagian_id');
        $rawat = $resep->registrasiDetail?->registrasi?->jenis_rawat;
    @endphp
    <div class="header">
        <h1>NusaMedika Farmasi</h1>
        <p>Instalasi Farmasi Rumah Sakit</p>
    </div>

    <div class="title">E-TIKET OBAT</div>

    <div class="resep-no">{{ $resep->no_resep }}</div>

    <table class="info">
        <tr>
            <td class="label">Pasien</td>
            <td>: {{ $resep->pasien->nama_pasien ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">No. MR</td>
            <td>: {{ $resep->pasien->no_mr ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Rawat</td>
            <td>: {{ $rawat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Depo</td>
            <td>: {{ $depos->implode(', ') ?: '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 65%;">Obat</th>
                <th style="text-align: center;">Jumlah</th>
                <th style="text-align: center;">No. Batch</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailAktif as $d)
                <tr>
                    <td>
                        {{ $d->barang->nama_barang ?? '-' }}
                        @if ($d->s_1 || $d->s_2 || $d->aturan_pakai)
                            <br><span style="font-size: 9px;">{{ $d->s_1 }} {{ $d->s_2 }} @if ($d->aturan_pakai) ({{ $d->aturan_pakai }}) @endif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ rtrim(rtrim(number_format((float) $d->jumlah_dispense, 2, ',', '.'), '0'), ',') }}</td>
                    <td style="text-align: center;">{{ $d->no_batch ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Belum ada obat yang didispense.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak {{ now()->format('d-m-Y H:i') }} &bull; Serahkan tiket ini saat mengambil obat
    </div>
</body>
</html>