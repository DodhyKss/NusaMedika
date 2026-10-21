<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Detail Resep - {{ $resep->no_resep }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #000; max-width: 700px; margin: 0 auto; padding: 20px; font-size: 12px; line-height: 1.4; }
        @media print {
            @page { size: A4; margin: 12mm; }
            body { padding: 0; }
        }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 15px; letter-spacing: 1px; }
        .header .resep-no { font-size: 14px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; text-align: center; border-bottom: 1px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.info td { padding: 3px 4px; vertical-align: top; font-size: 12px; border: 1px solid #000; }
        table.info td.label { font-weight: bold; width: 130px; }
        .section-title { font-weight: bold; font-size: 12px; margin: 12px 0 6px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { border: 1px solid #000; padding: 5px 4px; text-align: left; font-size: 11px; background: #f1f1f1; }
        table.items td { border: 1px solid #000; padding: 5px 4px; vertical-align: top; font-size: 11px; }
        table.items .num { text-align: center; }
        .footer { margin-top: 24px; display: flex; justify-content: space-between; align-items: flex-end; }
        .ttd { width: 160px; text-align: center; font-size: 11px; }
        .ttd .space { height: 48px; }
    </style>
</head>
<body onload="setTimeout(() => { window.print(); window.close(); }, 500);">
    @php
        $rawat = $resep->registrasiDetail?->registrasi?->jenis_rawat;
        $badge = match ((int) $resep->status_resep) {
            0 => 'Menunggu',
            1 => 'Selesai',
            2 => 'Batal',
            3 => 'Dispense Sebagian',
            default => '-',
        };
    @endphp
    <div class="header">
        <div>
            <h1>NusaMedika</h1>
            <span>Instalasi Farmasi Rumah Sakit</span>
        </div>
        <div class="resep-no">No. Resep: {{ $resep->no_resep }}</div>
    </div>

    <div class="title">DETAIL RESEP &amp; DISPENSE OBAT</div>

    <table class="info">
        <tr>
            <td class="label">Pasien</td>
            <td>{{ $resep->pasien->nama_pasien ?? '-' }}</td>
            <td class="label" style="width: 100px;">No. MR</td>
            <td>{{ $resep->pasien->no_mr ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Dokter</td>
            <td>{{ $resep->dokter ? 'Dr. '.$resep->dokter->nama_pegawai : '-' }}</td>
            <td class="label">Jenis Rawat</td>
            <td>{{ $rawat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Resep</td>
            <td>{{ \Carbon\Carbon::parse($resep->tanggal_resep)->format('d-m-Y H:i') }}</td>
            <td class="label">Status</td>
            <td>{{ $badge }}</td>
        </tr>
    </table>

    @if ($resep->keterangan)
        <div style="margin-bottom: 8px;"><strong>Keterangan:</strong> {{ $resep->keterangan }}</div>
    @endif

    <div class="section-title">Item Resep</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 34%;">Obat</th>
                <th class="num" style="width: 7%;">Jumlah</th>
                <th class="num" style="width: 9%;">Dispense</th>
                <th class="num" style="width: 8%;">Sisa</th>
                <th style="width: 12%;">No. Batch</th>
                <th style="width: 18%;">Waktu Dispense</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resep->details as $d)
                @php
                    $jumlahDispense = (float) ($d->jumlah_dispense ?? 0);
                    $sisa = (float) $d->jumlah - $jumlahDispense;
                    $itemStatus = $jumlahDispense <= 0 ? 'Belum'
                        : ($sisa > 0 ? 'Sebagian'
                        : ((int) $d->flag_dispense === 1 ? 'Lengkap' : 'Selesai'));
                @endphp
                <tr>
                    <td>
                        {{ $d->barang->nama_barang ?? '-' }}
                        @if ($d->barang && $d->barang->kode_barang)
                            <br><span style="font-size: 9px;">{{ $d->barang->kode_barang }}</span>
                        @endif
                        @if ($d->s_1 || $d->s_2 || $d->aturan_pakai || $d->rute_pemberian)
                            <br><span style="font-size: 9px;">{{ $d->s_1 }} {{ $d->s_2 }} @if ($d->aturan_pakai) ({{ $d->aturan_pakai }}) @endif @if ($d->rute_pemberian) &bull; {{ $d->rute_pemberian }} @endif</span>
                        @endif
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $d->jumlah, 2, ',', '.'), '0'), ',') }}</td>
                    <td class="num">{{ $jumlahDispense > 0 ? rtrim(rtrim(number_format($jumlahDispense, 2, ',', '.'), '0'), ',') : '-' }}</td>
                    <td class="num">{{ $sisa > 0 ? rtrim(rtrim(number_format($sisa, 2, ',', '.'), '0'), ',') : '-' }}</td>
                    <td>{{ $d->no_batch ?? '-' }}</td>
                    <td>{{ $d->waktu_dispense ? \Carbon\Carbon::parse($d->waktu_dispense)->format('d-m-Y H:i') : '-' }}</td>
                    <td>{{ $itemStatus }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd">
            Mengetahui,<br>
            (Dokter)
            <div class="space"></div>
            <span style="border-top: 1px solid #000; display: block; padding-top: 2px;">{{ $resep->dokter ? 'Dr. '.$resep->dokter->nama_pegawai : '....................' }}</span>
        </div>
        <div class="ttd">
            Disiapkan Oleh,<br>
            (Petugas Farmasi)
            <div class="space"></div>
            <span style="border-top: 1px solid #000; display: block; padding-top: 2px;">Dicetak {{ now()->format('d-m-Y H:i') }}</span>
        </div>
    </div>
</body>
</html>