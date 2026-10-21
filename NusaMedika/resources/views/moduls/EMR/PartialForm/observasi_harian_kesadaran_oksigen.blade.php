@php
    // Partial: Kesadaran dan Pemberian Oksigen (form `tanda_vital`).
    //
    // `kesadaran` & `oksigen` TIDAK berada di panel Tanda Vital karena form ini
    // memakai partial terpisah — keduanya tetap jadi parameter EVM, jadi rekap
    // EVM di partial A memakai tombol "Isi" untuk melompat ke sini.
    //
    // Cara & flow rate oksigen hanya relevan saat Oksigen = Oksigen; yang
    // mengosongkannya di server adalah filteredData() TandaVitalController.
@endphp

<x-emr-accordion id="acc-observasi-kesadaran-oksigen" title="C. Kesadaran dan Pemberian Oksigen" bgClass="bg-slate-100" :isOpen="true">
    <div class="text-sm space-y-5">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="kesadaran" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    Tingkat Kesadaran <span class="text-red-500">*</span>
                </label>
                <select id="kesadaran" name="kesadaran"
                        class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    {!! \App\Helpers\SelectOption::render('kesadaran', old('kesadaran', $emr_data['kesadaran'] ?? null), '-- Pilih Kesadaran --') !!}
                </select>
                <p class="mt-1.5 text-xs text-slate-400">Dipakai langsung oleh skor EVM (Compos Mentis = 0, selainnya 3).</p>
                @error('kesadaran')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="oksigen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    Air atau Oksigen <span class="text-red-500">*</span>
                </label>
                <select id="oksigen" name="oksigen"
                        class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    {!! \App\Helpers\SelectOption::render('oksigen', old('oksigen', $emr_data['oksigen'] ?? null), '-- Pilih --') !!}
                </select>
                <p class="mt-1.5 text-xs text-slate-400">Dipakai langsung oleh skor EVM (Air = 0, Oksigen = 2).</p>
                @error('oksigen')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Rincian pemberian oksigen. Hanya tampil saat oksigen = Oksigen;
             state awal sudah benar sejak render (class `hidden` + inline style
             sekaligus, lihat catatan di partial Nyeri). --}}
        <div id="wrapDetailOksigen" class="{{ $oksigenTerpilih === 'Oksigen' ? '' : 'hidden' }}" style="{{ $oksigenTerpilih === 'Oksigen' ? '' : 'display: none;' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cara_oksigen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        Cara Pemberian Oksigen
                    </label>
                    <select id="cara_oksigen" name="cara_oksigen"
                            class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <option value="">-- Pilih Cara --</option>
                        @foreach (['Nasal Kanul', 'Simple Mask', 'Non Rebreather', 'Rebreather', 'Ventilator'] as $cara)
                            <option value="{{ $cara }}" @selected(old('cara_oksigen', $emr_data['cara_oksigen'] ?? '') === $cara)>{{ $cara }}</option>
                        @endforeach
                    </select>
                    @error('cara_oksigen')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="flow_rate" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        Flow Rate (L/menit)
                    </label>
                    <input type="number" step="0.5" min="0" max="60" id="flow_rate" name="flow_rate"
                           value="{{ old('flow_rate', $emr_data['flow_rate'] ?? '') }}" placeholder="3"
                           class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                    @error('flow_rate')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <span class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    Endotracheal Tube (ETT)
                </span>
                <div class="flex gap-6">
                    @foreach (['Ya', 'Tidak'] as $pilihan)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="ett" value="{{ $pilihan }}" class="text-blue-600 focus:ring-blue-500 w-4 h-4"
                                   @checked(old('ett', $emr_data['ett'] ?? '') === $pilihan)>
                            <span class="text-slate-700">{{ $pilihan }}</span>
                        </label>
                    @endforeach
                </div>
                @error('ett')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <p class="text-xs text-slate-400">
            Kesadaran dan jenis oksigen tercatat pada setiap observasi, bukan hanya saat berubah,
            sehingga perubahan startling dapat dibandingkan antar waktu.
        </p>
    </div>
</x-emr-accordion>