@props([
    'id' => 'pasien_id',
    'name' => 'pasien_id',
    'label' => null,
    'placeholder' => '-- Ketik Nama atau No. RM Pasien --',
    'required' => false,
    'selected' => null,
])

@php
    $selectedLabel = null;
    $extraJson = null;
    $selectedPasienData = null;
    if ($selected) {
        $selectedPasien = \App\Models\Pasien::where('pasien_id', $selected)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();
        if ($selectedPasien) {
            $selectedLabel = "{$selectedPasien->no_mr} - {$selectedPasien->nama_pasien}";
            $selectedPasienData = [
                'ktp' => $selectedPasien->ktp,
                'jenis_kelamin' => $selectedPasien->jenis_kelamin === 'L' ? 'Laki-Laki' : ($selectedPasien->jenis_kelamin === 'P' ? 'Perempuan' : $selectedPasien->jenis_kelamin),
                'tempat_lahir' => $selectedPasien->tempat_lahir,
                'tgl_lahir' => $selectedPasien->tgl_lahir ? date('d-m-Y', strtotime($selectedPasien->tgl_lahir)) : '-',
                'alamat' => $selectedPasien->alamat,
                'no_hp' => $selectedPasien->no_hp,
                'agama' => $selectedPasien->agama,
                'gol_darah' => $selectedPasien->gol_darah,
                'status_perkawinan' => $selectedPasien->status_perkawinan,
                'pekerjaan' => $selectedPasien->pekerjaan,
                'nama_ibu_kandung' => $selectedPasien->nama_ibu_kandung,
            ];
            $extraJson = json_encode($selectedPasienData);
        }
    }
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
            {{ $label }}
            @if ($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="select2-pasien w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
        style="width: 100%"
        data-url="{{ route('api.pasien.search') }}"
        data-placeholder="{{ $placeholder }}"
        @if ($required) required @endif
    >
        <option value=""></option>
        @if ($selectedLabel)
            <option value="{{ $selected }}" selected data-extra="{{ $extraJson }}">{{ $selectedLabel }}</option>
        @endif
    </select>

    <!-- Patient Info Card -->
    <div id="pasien-card-{{ $id }}" class="mt-4 p-4 rounded-xl border border-blue-100 bg-blue-50/50 {{ $selectedPasienData ? '' : 'hidden' }}">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-white rounded-lg shadow-sm border border-slate-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-4">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. KTP</span>
                    <span class="block text-sm font-medium text-slate-700 p-ktp">{{ $selectedPasienData['ktp'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</span>
                    <span class="block text-sm font-medium text-slate-700 p-kelamin">{{ $selectedPasienData['jenis_kelamin'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tempat / Tgl Lahir</span>
                    <span class="block text-sm font-medium text-slate-700 p-ttl">{{ ($selectedPasienData['tempat_lahir'] ?? '-') . ' / ' . ($selectedPasienData['tgl_lahir'] ?? '-') }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. HP</span>
                    <span class="block text-sm font-medium text-slate-700 p-nohp">{{ $selectedPasienData['no_hp'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gol. Darah</span>
                    <span class="block text-sm font-medium text-slate-700 p-goldar">{{ $selectedPasienData['gol_darah'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Agama</span>
                    <span class="block text-sm font-medium text-slate-700 p-agama">{{ $selectedPasienData['agama'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Perkawinan</span>
                    <span class="block text-sm font-medium text-slate-700 p-kawin">{{ $selectedPasienData['status_perkawinan'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pekerjaan</span>
                    <span class="block text-sm font-medium text-slate-700 p-kerja">{{ $selectedPasienData['pekerjaan'] ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Ibu Kandung</span>
                    <span class="block text-sm font-medium text-slate-700 p-ibu">{{ $selectedPasienData['nama_ibu_kandung'] ?? '-' }}</span>
                </div>
                <div class="sm:col-span-2 md:col-span-3">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</span>
                    <span class="block text-sm font-medium text-slate-700 p-alamat">{{ $selectedPasienData['alamat'] ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
