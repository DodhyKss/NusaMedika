@php
    // Jika tidak ada data emr_data yang di-passing, kita ambil berdasarkan emr_id
    if (!isset($emr_data)) {
        $emr_data = \Illuminate\Support\Facades\DB::table('emr')
            ->leftJoin('pegawai', 'emr.pegawai_id', '=', 'pegawai.pegawai_id')
            ->where('emr.emr_id', $emr_id)
            ->select('emr.*', 'pegawai.nama_pegawai')
            ->first();
    }
    
    // Ambil detail SOAP
    $details = \Illuminate\Support\Facades\DB::table('emr_detail')
        ->where('emr_id', $emr_id)
        ->pluck('value', 'variabel')
        ->toArray();
        
    $s = $details['subjective'] ?? '-';
    $o = $details['objective'] ?? '-';
    $a = $details['assessment'] ?? '-';
    $p = $details['planning'] ?? '-';
    $i = $details['instruksi'] ?? '-';
@endphp

@if($emr_data)
<div class="cppt-print-container" style="font-family: Arial, sans-serif; line-height: 1.5; color: #000; margin-bottom: 20px; page-break-inside: avoid; border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
    <div style="border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: bold;">Catatan Perkembangan Pasien Terintegrasi (CPPT)</h4>
        </div>
        <div style="font-size: 13px; text-align: right;">
            <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($emr_data->tgl_jam)->format('d/m/Y H:i') }}<br>
            <strong>Pencatat:</strong> {{ $emr_data->nama_pegawai ?? 'Dokter/Perawat' }}
        </div>
    </div>
    
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td style="width: 25px; vertical-align: top; font-weight: bold; padding: 4px 0;">S</td>
            <td style="vertical-align: top; padding: 4px 0;">: {!! nl2br(e($s)) !!}</td>
        </tr>
        <tr>
            <td style="width: 25px; vertical-align: top; font-weight: bold; padding: 4px 0;">O</td>
            <td style="vertical-align: top; padding: 4px 0;">: {!! nl2br(e($o)) !!}</td>
        </tr>
        <tr>
            <td style="width: 25px; vertical-align: top; font-weight: bold; padding: 4px 0;">A</td>
            <td style="vertical-align: top; padding: 4px 0;">: {!! nl2br(e($a)) !!}</td>
        </tr>
        <tr>
            <td style="width: 25px; vertical-align: top; font-weight: bold; padding: 4px 0;">P</td>
            <td style="vertical-align: top; padding: 4px 0;">: {!! nl2br(e($p)) !!}</td>
        </tr>
        <tr>
            <td style="width: 25px; vertical-align: top; font-weight: bold; padding: 4px 0;">I</td>
            <td style="vertical-align: top; padding: 4px 0;">: {!! nl2br(e($i)) !!}</td>
        </tr>
    </table>
</div>
@endif
