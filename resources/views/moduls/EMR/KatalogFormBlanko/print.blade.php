<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $blanko->nama_blanko }} - Pengisian</title>
    <style>
        @page { size: {{ $blanko->ukuran_halaman }} {{ $blanko->orientasi_halaman }}; margin: 12mm; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11pt; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .grup { background: #e5e7eb; font-weight: bold; }
        .identitas { width: 100%; margin-bottom: 4mm; }
        .identitas td { border: none; padding: 1px 4px 1px 0; }
        .kop { white-space: pre-line; }
        .footer { margin-top: 8mm; font-size: 9pt; color: #444; }
        img.ttd { max-height: 18mm; }
    </style>
</head>
<body>
    @if ($blanko->tampilkan_logo)
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 24mm">
    @endif
    <div class="kop">{!! nl2br(e($blanko->kop_surat)) !!}</div>
    <h2 style="text-align:center; margin: 6mm 0 2mm">{{ $blanko->nama_blanko }}</h2>

    @if ($blanko->tampilkan_identitas_pasien)
        @php
            $pasienCetak = $registrasi_detail->registrasi->pasien ?? null;
            $usiaCetak = '-';
            if ($pasienCetak?->tgl_lahir) {
                $usiaCetak = \Carbon\Carbon::parse($pasienCetak->tgl_lahir)->age.' tahun';
            }
        @endphp
        <table class="identitas">
            <tr>
                <td style="width:16%">No. RM</td><td style="width:34%">: {{ $pasienCetak->no_mr ?? '-' }}</td>
                <td style="width:16%">Jenis Kelamin</td><td>: {{ ($pasienCetak->jenis_kelamin ?? '') === 'P' ? 'Perempuan' : 'Laki-laki' }}</td>
            </tr>
            <tr>
                <td>Nama Pasien</td><td>: {{ $pasienCetak->nama_pasien ?? '-' }}</td>
                <td>Usia</td><td>: {{ $usiaCetak }}</td>
            </tr>
        </table>
    @endif

    <table>
        <tbody>
            <tr>
                <th style="width:32%">Tanggal Pengisian</th>
                <td>{{ \Carbon\Carbon::parse($pengisian->input_time)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Petugas</th>
                <td>{{ $petugas ?? '-' }}</td>
            </tr>

            @php $grupTerakhir = null; @endphp
            @foreach ($fields as $f)
                @php
                    $kode = $f->kode_field;
                    $nilai = $pengisian->data[$kode] ?? null;
                @endphp
                @if ((string) $f->grup !== (string) $grupTerakhir)
                    @php $grupTerakhir = $f->grup; @endphp
                    <tr>
                        <td colspan="2" class="grup">{{ $f->grup ?: 'Detail' }}</td>
                    </tr>
                @endif
                <tr>
                    <th>{{ $f->label }}{{ $f->wajib ? ' *' : '' }}</th>
                    <td>
                        @if ($f->tipe_kontrol === 'signature')
                            @if (is_string($nilai) && str_starts_with($nilai, 'data:image/'))
                                <img src="{{ $nilai }}" alt="Tanda tangan" class="ttd">
                            @else
                                <em style="color:#777">-</em>
                            @endif
                        @elseif ($f->tipe_kontrol === 'checkbox_single')
                            {{ in_array((string) $nilai, ['1', 'on', 'true'], true) ? '✓' : '' }}
                        @elseif (is_array($nilai))
                            {{ implode(', ', array_map('strval', $nilai)) }}
                        @else
                            {{ $nilai ?? '' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — {{ $blanko->nama_blanko }} v{{ $blanko->versi }}
        (pengisian #{{ $pengisian->form_blanko_pengisian_id }}).
    </p>
</body>
</html>
