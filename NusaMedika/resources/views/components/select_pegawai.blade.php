<div>
    <label for="{{ $id }}"
        class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
        {{ $label }} {!! $required ? '<span class="text-red-500">*</span>' : '' !!}
    </label>

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {!! $required ? 'required' : '' !!}
            class="select2 w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">

            <option value="">{{ $placeholder }}</option>

            @foreach ($pegawais as $pegawai)
                <option value="{{ $pegawai->pegawai_id }}" @selected((string) $selected === (string) $pegawai->pegawai_id)>
                    {{ $pegawai->nama_pegawai ?? '' }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- Tanpa blok ini, kegagalan validasi (mis. penerima_id tidak ada)
         ditolak diam-diam tanpa pesan di layar. --}}
    @error($name)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>