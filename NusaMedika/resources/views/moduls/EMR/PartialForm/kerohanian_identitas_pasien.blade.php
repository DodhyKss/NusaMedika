{{-- Blok identitas pasien READ-ONLY untuk form Kerohanian (114 & 115).
     Di-render dari master `pasien` + `registrasi_detail` + `bagian`
     (KONSEP_PSIKOSOSIAL_KEROHANIAN.md §5/§6) — TIDAK disimpan ke emr_detail. --}}
@php
    $kPasien = $registrasi_detail->registrasi->pasien ?? null;
    $kUmur = '—';
    if ($kPasien && $kPasien->tgl_lahir) {
        try {
            $kUmur = \Carbon\Carbon::parse($kPasien->tgl_lahir)->age.' Tahun';
        } catch (\Exception $e) {
            $kUmur = '—';
        }
    }
    $kJk = match ($kPasien->jenis_kelamin ?? '') {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '—',
    };
    $kRm = $kPasien->no_mr ?? '—';
    $kNama = $kPasien->nama_pasien ?? '—';
    $kAgama = $kPasien->agama ?? '—';
    $kRuangan = $registrasi_detail->bagian->nama_bagian ?? '—';
@endphp
<div class="border border-slate-200 rounded-sm shadow-sm">
    <div class="px-4 py-3 bg-slate-100 border-b border-slate-200">
        <span class="text-slate-700 font-medium text-[15px]">Identitas Pasien</span>
    </div>
    <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">No. RM</label>
            <input type="text" readonly value="{{ $kRm }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium mb-1 text-slate-500">Nama Pasien</label>
            <input type="text" readonly value="{{ $kNama }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Umur</label>
            <input type="text" readonly value="{{ $kUmur }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Jenis Kelamin</label>
            <input type="text" readonly value="{{ $kJk }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Agama</label>
            <input type="text" readonly value="{{ $kAgama }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div class="md:col-span-3">
            <label class="block text-xs font-medium mb-1 text-slate-500">Ruangan</label>
            <input type="text" readonly value="{{ $kRuangan }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600 md:max-w-md">
        </div>
    </div>
</div>
