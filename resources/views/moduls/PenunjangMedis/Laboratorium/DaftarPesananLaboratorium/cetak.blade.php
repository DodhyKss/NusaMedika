<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LHU Laboratorium - {{ $order->no_order }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #000; max-width: 700px; margin: 0 auto; padding: 20px; font-size: 12px; line-height: 1.4; }
        @media print {
            @page { size: A4; margin: 12mm; }
            body { padding: 0; }
        }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 15px; letter-spacing: 1px; }
        .header .order-no { font-size: 14px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; text-align: center; border-bottom: 1px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.info td { padding: 3px 4px; vertical-align: top; font-size: 12px; border: 1px solid #000; }
        table.info td.label { font-weight: bold; width: 130px; }
        .section-title { font-weight: bold; font-size: 12px; margin: 12px 0 6px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { border: 1px solid #000; padding: 5px 4px; text-align: left; font-size: 11px; background: #f1f1f1; }
        table.items td { border: 1px solid #000; padding: 5px 4px; vertical-align: top; font-size: 11px; }
        .footer { margin-top: 24px; display: flex; justify-content: space-between; align-items: flex-end; }
        .ttd { width: 160px; text-align: center; font-size: 11px; }
        .ttd .space { height: 48px; }
    </style>
</head>
<body onload="setTimeout(() => { window.print(); window.close(); }, 500);">
    @php
        $rawat = $order->registrasiDetail?->registrasi?->jenis_rawat;
        $badge = match ((int) $order->status_order) {
            0 => 'Menunggu',
            1 => 'Diproses',
            2 => 'Selesai',
            3 => 'Batal',
            default => '-',
        };
    @endphp
    <div class="header">
        <div>
            <h1>NusaMedika</h1>
            <span>Instalasi Laboratorium</span>
        </div>
        <div class="order-no">No. Order: {{ $order->no_order }}</div>
    </div>

    <div class="title">LAPORAN HASIL UJI LABORATORIUM</div>

    <table class="info">
        <tr>
            <td class="label">Pasien</td>
            <td>{{ $order->pasien?->nama_pasien ?? '-' }}</td>
            <td class="label" style="width: 100px;">No. MR</td>
            <td>{{ $order->pasien?->no_mr ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Dokter Peminta</td>
            <td>{{ $order->dokter ? 'Dr. '.$order->dokter->nama_pegawai : '-' }}</td>
            <td class="label">Jenis Rawat</td>
            <td>{{ $rawat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Bagian Asal</td>
            <td>{{ $order->bagianAsal?->nama_bagian ?? '-' }}</td>
            <td class="label">Tanggal Order</td>
            <td>{{ $order->tanggal_order ? \Carbon\Carbon::parse($order->tanggal_order)->format('d-m-Y H:i') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Prioritas</td>
            <td>{{ $order->prioritas ?? '-' }}</td>
            <td class="label">Status</td>
            <td>{{ $badge }}</td>
        </tr>
    </table>

    @if ($order->keterangan)
        <div style="margin-bottom: 8px;"><strong>Keterangan:</strong> {{ $order->keterangan }}</div>
    @endif

    <div class="section-title">Hasil Pemeriksaan</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 30%;">Pemeriksaan</th>
                <th style="width: 12%;">Satuan</th>
                <th style="width: 16%;">Nilai Normal</th>
                <th style="width: 26%;">Hasil</th>
                <th style="width: 16%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->details as $d)
                <tr>
                    <td>{{ $d->nama_tindakan }}</td>
                    <td>{{ $d->satuan_hasil ?? '-' }}</td>
                    <td>{{ $d->nilai_normal ?? '-' }}</td>
                    <td>{{ $d->hasil ?? '-' }}</td>
                    <td>{{ !empty($d->hasil) ? ((int) $d->flag_abnormal === 1 ? 'ABNORMAL' : 'Normal') : 'Belum' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ((int) $order->status_order === 2)
        <div style="margin-top: 8px; font-size: 11px;">Tanggal Hasil: {{ $order->tanggal_hasil ? \Carbon\Carbon::parse($order->tanggal_hasil)->format('d-m-Y H:i') : '-' }}</div>
    @endif

    <div class="footer">
        <div class="ttd">
            Dokter Peminta,<br>
            ({{ $order->dokter?->profesi?->nama_profesi ?? 'Dokter' }})
            <div class="space"></div>
            <span style="border-top: 1px solid #000; display: block; padding-top: 2px;">{{ $order->dokter ? 'Dr. '.$order->dokter->nama_pegawai : '....................' }}</span>
        </div>
        <div class="ttd">
            Petugas Laboratorium,<br>
            (Analis Lab)
            <div class="space"></div>
            <span style="border-top: 1px solid #000; display: block; padding-top: 2px;">{{ $order->petugasPelaksana?->nama_pegawai ?? '....................' }}</span>
        </div>
        <div class="ttd">
            Diketahui,<br>
            (Kepala Lab)
            <div class="space"></div>
            <span style="border-top: 1px solid #000; display: block; padding-top: 2px;">....................</span>
        </div>
    </div>
</body>
</html>