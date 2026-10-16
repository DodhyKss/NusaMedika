<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $blanko->nama_blanko }}</title>
    <style>
        @page { size: {{ $blanko->ukuran_halaman }} {{ $blanko->orientasi_halaman }}; margin: 12mm; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11pt; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .grup { background: #e5e7eb; font-weight: bold; }
        .kosong { min-height: 22mm; }
        .kosong-ttd { min-height: 30mm; }
        .kop { white-space: pre-line; }
        .footer { margin-top: 10mm; font-size: 9pt; color: #444; }
    </style>
</head>
<body>
    @if ($blanko->tampilkan_logo)
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 24mm">
    @endif
    <div class="kop">{!! nl2br(e($blanko->kop_surat)) !!}</div>
    <h2 style="text-align:center; margin: 6mm 0 2mm">{{ $blanko->nama_blanko }}</h2>
    @if ($blanko->deskripsi)
        <p style="text-align:center; margin-top:0">{{ $blanko->deskripsi }}</p>
    @endif

    <table style="margin-top: 4mm">
        <thead>
            <tr>
                <th style="width: 32%">Uraian</th>
                <th>Isian</th>
            </tr>
        </thead>
        <tbody>
            @php $grupTerakhir = null; @endphp
            @foreach ($fields as $f)
                @if ((string) $f->grup !== (string) $grupTerakhir)
                    @php $grupTerakhir = $f->grup; @endphp
                    <tr>
                        <td colspan="2" class="grup">{{ $f->grup ?: 'Detail' }}</td>
                    </tr>
                @endif
                <tr>
                    <th>{{ $f->label }}{{ $f->wajib ? ' *' : '' }}</th>
                    @if ($f->tipe_kontrol === 'signature')
                        <td class="kosong-ttd"></td>
                    @else
                        <td class="kosong"></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — {{ $blanko->nama_blanko }} v{{ $blanko->versi }} (formulir kosong).
    </p>
</body>
</html>
