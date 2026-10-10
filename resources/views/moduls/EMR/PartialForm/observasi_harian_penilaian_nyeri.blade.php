@php
    // Partial: Penilaian Nyeri (form `tanda_vital`).
    //
    // Skor nyeri hanya relevan bila pasien mengeluh nyeri; yang mengosongkan
    // kolomnya saat "Tidak" adalah filteredData() di server, bukan browser.
@endphp

<x-emr-accordion id="acc-observasi-penilaian-nyeri" title="B. Penilaian Nyeri" bgClass="bg-yellow-50" :isOpen="true">
    <div class="text-sm space-y-4">

        <div>
            <span class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                Apakah Pasien Mengalami Nyeri? <span class="text-red-500">*</span>
            </span>
            <div class="flex gap-6">
                @foreach (['Ya', 'Tidak'] as $pilihan)
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="nyeri" value="{{ $pilihan }}" class="nyeri-pilih text-amber-600 focus:ring-amber-500 w-4 h-4"
                               @checked(old('nyeri', $emr_data['nyeri'] ?? 'Tidak') === $pilihan)>
                        <span class="text-slate-700">{{ $pilihan }}</span>
                    </label>
                @endforeach
            </div>
            @error('nyeri')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Skor nyeri: hanya tampil saat Nyeri = Ya.
             State awal sudah benar sejak render (belum bergantung JS) memakai
             inline style + class `hidden` sekaligus — setting style saja tidak
             cukup karena class `hidden` tetap memberi display:none. --}}
        <div id="wrapSkorNyeri" class="{{ $nyeriTerpilih === 'Ya' ? '' : 'hidden' }}" style="{{ $nyeriTerpilih === 'Ya' ? '' : 'display: none;' }}">
            <label for="skor_nyeri" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                Skor Nyeri (0&ndash;10)
            </label>
            <select id="skor_nyeri" name="skor_nyeri"
                    class="select2 w-full sm:w-40 border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Skor --</option>
                @for ($skor = 0; $skor <= 10; $skor++)
                    <option value="{{ $skor }}" @selected((string) old('skor_nyeri', $emr_data['skor_nyeri'] ?? '') === (string) $skor)>{{ $skor }}</option>
                @endfor
            </select>
            <p class="mt-1.5 text-xs text-slate-400">0 = tidak nyeri, 10 = nyeri terparah yang dirasakan pasien.</p>
            @error('skor_nyeri')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs text-slate-400">
            Bila dipilih <strong>Ya</strong>, isi skor numeriknya. Kolom ini ikut tersimpan di riwayat observasi
            sehingga pergeseran nyeri antar waktu dapat dibandingkan.
        </p>
    </div>
</x-emr-accordion>