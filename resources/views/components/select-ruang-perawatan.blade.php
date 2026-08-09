<div>
    <label for="{{ $id }}"
        class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
        Pilih Ruang Perawatan
    </label>

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            class="select2-ruangan w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white">

            <option value="">Semua Ruang Perawatan</option>

            @foreach ($ruangan as $r)
                <option value="{{ $r->bagian_id }}"
                    {{ $selected == $r->bagian_id ? 'selected' : '' }}>
                    {{ $r->nama_bagian ?? '' }}
                </option>
            @endforeach

        </select>
    </div>
</div>