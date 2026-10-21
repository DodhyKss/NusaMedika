{{-- Blok identitas pasien READ-ONLY untuk form Rujukan (58, 126, 127, 128)
     (KONSEP_FORMULIR_RUJUKAN.md §Implementasi) — di-render dari master
     `pasien` + `registrasi_detail` + `bagian`, TIDAK disimpan ke emr_detail.
     Isi: No. RM / Nama / Jenis Kelamin / Usia / Alamat / Ruangan. --}}
@php
    $rPasien = $registrasi_detail->registrasi->pasien ?? null;
    $rUmur = '—';
    if ($rPasien && $rPasien->tgl_lahir) {
        try {
            $rUmur = \Carbon\Carbon::parse($rPasien->tgl_lahir)->age.' Tahun';
        } catch (\Exception $e) {
            $rUmur = '—';
        }
    }
    $rJk = match ($rPasien->jenis_kelamin ?? '') {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '—',
    };
    $rRm = $rPasien->no_mr ?? '—';
    $rNama = $rPasien->nama_pasien ?? '—';
    $rAlamat = $rPasien->alamat ?? '—';
    $rRuangan = $registrasi_detail->bagian->nama_bagian ?? '—';
@endphp
<div class="border border-slate-200 rounded-sm shadow-sm">
    <div class="px-4 py-3 bg-slate-100 border-b border-slate-200">
        <span class="text-slate-700 font-medium text-[15px]">Identitas Pasien</span>
    </div>
    <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">No. RM</label>
            <input type="text" readonly value="{{ $rRm }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium mb-1 text-slate-500">Nama Pasien</label>
            <input type="text" readonly value="{{ $rNama }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Jenis Kelamin</label>
            <input type="text" readonly value="{{ $rJk }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Usia</label>
            <input type="text" readonly value="{{ $rUmur }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">Ruangan</label>
            <input type="text" readonly value="{{ $rRuangan }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
        <div class="md:col-span-3">
            <label class="block text-xs font-medium mb-1 text-slate-500">Alamat</label>
            <input type="text" readonly value="{{ $rAlamat }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
        </div>
    </div>
</div>
