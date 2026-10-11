{{--
    Skala VAS / NRS 0-10 (Visual Analog Scale / Numeric Rating Scale).
    Dipakai form 27 Penilaian Nyeri & form 36 Metode Nyeri.

    Props:
      value    -> nilai tersimpan (string/int|null)
      name     -> nama input di emr_detail (default `skor_nyeri`)
      prefix   -> prefiks id agar tidak bentrok bila partial dipakai 2x seekali
      required -> tanda wajib (hanya misleading bila metode VAS dipilih)
--}}
@php
    $vasName = $name ?? 'skor_nyeri';
    $vasPrefix = $prefix ?? 'vas';
    $vasValue = $value === null || $value === '' ? null : (string) $value;
    $vasRequired = $required ?? false;
@endphp

<div class="p-4 border border-slate-200 rounded-lg bg-slate-50/60">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <span class="font-bold text-slate-800">
            Skala Nyeri (VAS / NRS 0-10)
            @if ($vasRequired)
                <span class="text-red-500">*</span>
            @endif
        </span>
        <span class="text-[11px] uppercase tracking-wider text-slate-500">0 tidak nyeri - 10 nyeri Sangat berat</span>
    </div>

    <img src="{{ asset('img/nyeri/skala_nyeri.jpg') }}" alt="Skala Nyeri 0-10"
         class="w-full h-auto rounded border border-slate-200 bg-white mb-4">

    <div class="flex flex-wrap gap-2">
        @for ($i = 0; $i <= 10; $i++)
            @php
                $vasId = $vasPrefix.'-'.$i;
                $cek = $vasValue !== null && (int) $vasValue === $i;
            @endphp
            <label for="{{ $vasId }}"
                   class="cursor-pointer inline-flex items-center justify-center w-10 h-10 rounded-lg border text-sm font-semibold transition-colors
                          {{ $cek ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-blue-50' }}">
                <input type="radio" id="{{ $vasId }}" name="{{ $vasName }}" value="{{ $i }}"
                       class="sr-only" @checked($cek)>
                {{ $i }}
            </label>
        @endfor
    </div>

    <div class="mt-3 flex flex-wrap gap-4 text-[11px] text-slate-500">
        <span><span class="font-semibold text-emerald-600">0</span> Tidak Nyeri</span>
        <span><span class="font-semibold text-amber-500">1-3</span> Nyeri Ringan</span>
        <span><span class="font-semibold text-orange-500">4-6</span> Nyeri Sedang</span>
        <span><span class="font-semibold text-red-600">7-10</span> Nyeri Berat</span>
    </div>
</div>