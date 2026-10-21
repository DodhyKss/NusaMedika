<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: #1e293b; background: #fff; font-size: 12px; }
        .wrap { max-width: 780px; margin: 0 auto; padding: 32px; }
        .kop { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #0f766e; padding-bottom: 12px; margin-bottom: 20px; }
        .kop h1 { font-size: 18px; color: #0f766e; }
        .kop p { font-size: 11px; color: #64748b; }
        h2 { font-size: 14px; color: #0f766e; margin-bottom: 6px; }
        .meta { margin-bottom: 24px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta .lbl { width: 180px; color: #64748b; }
        .info-pasien { border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px 16px; margin-bottom: 24px; }
        .info-pasien table { width: 100%; border-collapse: collapse; }
        .info-pasien td { padding: 5px 0; vertical-align: top; }
        .info-pasien .lbl { width: 180px; color: #64748b; }
        .isi { border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px 16px; margin-bottom: 28px; }
        .isi .baris { margin-bottom: 14px; }
        .isi .baris:last-child { margin-bottom: 0; }
        .isi .lbl { font-size: 11px; color: #64748b; margin-bottom: 3px; }
        .isi .val { font-size: 12px; font-weight: 600; }
        .footer { display: flex; justify-content: space-between; gap: 24px; }
        .footer .ttd { width: 50%; text-align: center; }
        .footer .ttd p { color: #64748b; margin-bottom: 60px; }
        .footer .ttd .nama { font-weight: 700; border-top: 1px solid #334155; padding-top: 6px; }
        .print-btn { position: fixed; bottom: 24px; right: 24px; background: #0f766e; color: #fff; border: 0; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        @media print {
            .print-btn { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $detail = \App\Helpers\EmrHelper::emrDetailByVariabel((int) $emr_id)->toArray();
        if (empty($detail)) {
            $json = json_decode(\App\Helpers\EmrHelper::emrById((int) $emr_id)?->data, true) ?? [];
            $detail = $json;
        }

        $emr = \App\Helpers\EmrHelper::emrById((int) $emr_id);
        $registrasiDetail = \App\Models\RegistrasiDetail::with(['registrasi.pasien', 'registrasi.pasienNasabah.nasabah'])->find($emr->registrasi_detail_id ?? null);
        $pasien = $registrasiDetail?->registrasi?->pasien;
        $namaBagian = $registrasiDetail?->bagian?->nama_bagian;

        $jenisMap = [
            'REHABILITASI_MEDIK' => 'Konsultasi Rehabilitasi Medik',
            'KONSUL_LAYANAN' => 'Konsul Layanan Klinik',
            'RENCANA_KONTROL' => 'Rencana Kontrol Rawat Jalan',
        ];
        $jenis = $detail['jenis_konsultasi'] ?? '';
        $namaJenis = $jenisMap[$jenis] ?? $jenis;

        $namaBagianTujuan = $jenis === 'REHABILITASI_MEDIK'
            ? \App\Models\Bagian::find($detail['bagian_rehab_id'] ?? null)?->nama_bagian
            : \App\Models\Bagian::find($detail['bagian_tujuan_id'] ?? null)?->nama_bagian;
        $namaDokterTujuan = \App\Models\Pegawai::find($detail['dokter_tujuan_id'] ?? null)?->nama_pegawai;
        $namaDokterPencatat = \App\Models\Pegawai::find($emr->pegawai_id ?? null)?->nama_pegawai ?? auth()->user()?->nama_pegawai ?? '';
    @endphp

    <div class="wrap">
        <div class="kop">
            <div>
                <h1>MEDITECH RS</h1>
                <p>Rumah Sakit Medika Andalan</p>
            </div>
            <div style="text-align:right">
                <h2>{{ $namaJenis }}</h2>
                <p>{{ now()->translatedFormat('d F Y H:i') }}</p>
            </div>
        </div>

        <div class="info-pasien">
            <table>
                <tr>
                    <td class="lbl">No. Rekam Medis</td>
                    <td>: <b>{{ $pasien->no_mr ?? '-' }}</b></td>
                </tr>
                <tr>
                    <td class="lbl">Nama Pasien</td>
                    <td>: <b>{{ $pasien->nama_pasien ?? '-' }}</b></td>
                </tr>
                <tr>
                    <td class="lbl">Tanggal Lahir / Umur</td>
                    <td>: {{ $pasien?->tgl_lahir ? \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Jenis Kelamin</td>
                    <td>: {{ $pasien->jenis_kelamin ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="lbl">Poliklinik / Ruangan</td>
                    <td>: {{ $namaBagian ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="isi">
            <div class="baris">
                <div class="lbl">Bagian Tujuan</div>
                <div class="val">{{ $namaBagianTujuan ?? '-' }}</div>
            </div>
            @if($jenis === 'KONSUL_LAYANAN' || $jenis === 'RENCANA_KONTROL')
                <div class="baris">
                    <div class="lbl">Dokter Tujuan</div>
                    <div class="val">{{ $namaDokterTujuan ?? '-' }}</div>
                </div>
            @endif
            @if($jenis === 'RENCANA_KONTROL' && !empty($detail['tanggal_kontrol']))
                <div class="baris">
                    <div class="lbl">Tanggal Kontrol Selanjutnya</div>
                    <div class="val">{{ \Carbon\Carbon::parse($detail['tanggal_kontrol'])->translatedFormat('d F Y') }}</div>
                </div>
            @endif
            <div class="baris">
                <div class="lbl">Indikasi Konsultasi</div>
                <div class="val" style="white-space:pre-wrap">{{ $detail['indikasi_konsultasi'] ?? '-' }}</div>
            </div>
            @if(!empty($detail['catatan']))
                <div class="baris">
                    <div class="lbl">Catatan</div>
                    <div class="val" style="white-space:pre-wrap">{{ $detail['catatan'] }}</div>
                </div>
            @endif
        </div>

        <div class="footer">
            <div class="ttd">
                <p>Dicetak oleh,<br>{{ now()->translatedFormat('d F Y H:i') }}</p>
                <div class="nama">{{ $namaDokterPencatat }}</div>
            </div>
            <div class="ttd">
                <p>Petugas <?= $jenis === 'REHABILITASI_MEDIK' ? 'Rehabilitasi Medik' : 'Tujuan' ?>,</p>
                <div class="nama">&nbsp;</div>
            </div>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">Cetak</button>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>