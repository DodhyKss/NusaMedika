@php
    $isEdit = $isEdit ?? false;
    /** @var \App\Models\Tindakan|null $t */
    $t = $tindakan ?? null;
    $tarifByKelas = $tarifByKelas ?? [];

    $tarifVal = function ($key) use ($tarifByKelas) {
        return old("tarif.$key", $tarifByKelas[$key] ?? '');
    };
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formMasterTarif">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if (! $isEdit)
        <div class="mb-5">
            <label for="tindakan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tindakan <span class="text-red-500">*</span></label>
            <select id="tindakan_id" name="tindakan_id" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Tindakan --</option>
                @foreach ($belumAdaTarif as $b)
                    <option value="{{ $b->tindakan_id }}" @selected((int) old('tindakan_id') === (int) $b->tindakan_id)>
                        {{ $b->nama_tindakan }} ({{ $b->kode_tindakan }})@if ($b->kategori) — {{ $b->kategori->nama_kategori_tindakan }}@endif
                    </option>
                @endforeach
            </select>
            <p class="mt-1.5 text-xs text-slate-400">Hanya tindakan yang belum memiliki tarif yang ditampilkan.</p>
            @error('tindakan_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">{{ $t->kode_tindakan }}</span>
            <span class="text-sm font-semibold text-slate-800">{{ $t->nama_tindakan }}</span>
            @if ($t->kategori)
                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700">{{ $t->kategori->nama_kategori_tindakan }}</span>
            @endif
        </div>
    @endif

    <div>
        <div class="flex items-center gap-2 mb-3">
            <h3 class="text-sm font-semibold text-slate-800">Tarif per Kelas Perawatan</h3>
            <span class="text-[11px] text-slate-400">Baris default dipakai bila kelas spesifik belum diisi.</span>
        </div>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left" style="min-width: 420px;">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Tarif (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-blue-50/40">
                        <td class="px-3 py-2.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">DEFAULT</span>
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" name="tarif[default]" step="any" min="0" value="{{ $tarifVal('default') }}" placeholder="0"
                                   class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('tarif.default')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                    @foreach ($kelasList as $kls)
                        @php $key = (string) $kls->kelas_ruang_id; @endphp
                        <tr>
                            <td class="px-3 py-2.5 font-medium text-slate-700">{{ $kls->nama_kelas_ruang }}</td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="tarif[{{ $key }}]" step="any" min="0" value="{{ $tarifVal($key) }}" placeholder="Kosong = pakai default"
                                       class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                                @error("tarif.$key")
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <hr class="my-6 border-slate-200">

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.master_tarif.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tarif' }}
        </button>
    </div>
</form>
