@php
    $isEdit = $isEdit ?? false;
    $hargaByKelas = $hargaByKelas ?? [];
    /** @var \App\Models\Tindakan|null $t */
    $t = $tindakan ?? null;

    $val = function ($field) use ($t) {
        return old($field, $t ? $t->{$field} : '');
    };

    $hargaVal = function ($key, $col) use ($hargaByKelas) {
        $h = $hargaByKelas[$key] ?? null;
        if ($h === null) return '';
        return old("tarif.$key.$col", $h->{$col} ?? '');
    };
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formTindakan">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        <!-- Kode Tindakan -->
        <div>
            <label for="kode_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode Tindakan <span class="text-red-500">*</span></label>
            <input type="text" id="kode_tindakan" name="kode_tindakan" value="{{ $val('kode_tindakan') }}" placeholder="Contoh: LAB-0001 / RAD-0001" 
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('kode_tindakan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Tindakan -->
        <div>
            <label for="nama_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Tindakan <span class="text-red-500">*</span></label>
            <input type="text" id="nama_tindakan" name="nama_tindakan" value="{{ $val('nama_tindakan') }}" placeholder="Contoh: Darah Rutin, Rontgen Thorax" 
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nama_tindakan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Bagian -->
        <div>
            <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bagian <span class="text-red-500">*</span></label>
            <select id="bagian_id" name="bagian_id" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Bagian --</option>
                @foreach ($bagianList as $b)
                    <option value="{{ $b->bagian_id }}" @selected((int) old('bagian_id', $t ? $t->bagian_id : '') === (int) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                @endforeach
            </select>
            @error('bagian_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kategori -->
        <div>
            <label for="kategori" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kategori <span class="text-red-500">*</span></label>
            <select id="kategori" name="kategori" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                @foreach (\App\Models\Tindakan::KATEGORI as $valK => $label)
                    <option value="{{ $valK }}" @selected($val('kategori') === $valK)>{{ $label }}</option>
                @endforeach
            </select>
            @error('kategori')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Satuan Hasil -->
        <div>
            <label for="satuan_hasil" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Satuan Hasil</label>
            <input type="text" id="satuan_hasil" name="satuan_hasil" value="{{ $val('satuan_hasil') }}" placeholder="Contoh: mg/dL, U/L, % (untuk lab)" 
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('satuan_hasil')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nilai Normal -->
        <div>
            <label for="nilai_normal" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nilai Normal</label>
            <input type="text" id="nilai_normal" name="nilai_normal" value="{{ $val('nilai_normal') }}" placeholder="Contoh: L 13-17 / P 12-16"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nilai_normal')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kode BPJS -->
        <div>
            <label for="kode_bpjs" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode BPJS / VClaim</label>
            <input type="text" id="kode_bpjs" name="kode_bpjs" value="{{ $val('kode_bpjs') }}" placeholder="Opsional (persiapan bridging)"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('kode_bpjs')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kode INACBG -->
        <div>
            <label for="kode_inacbg" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode INACBG</label>
            <input type="text" id="kode_inacbg" name="kode_inacbg" value="{{ $val('kode_inacbg') }}" placeholder="Opsional (persiapan bridging)"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('kode_inacbg')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kode LOINC -->
        <div>
            <label for="kode_loinc" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode LOINC</label>
            <input type="text" id="kode_loinc" name="kode_loinc" value="{{ $val('kode_loinc') }}" placeholder="Opsional (interop / SATU SEHAT)"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('kode_loinc')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Keterangan -->
        <div class="md:col-span-2">
            <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Catatan tambahan (opsional)"
                      class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">{{ $val('keterangan') }}</textarea>
            @error('keterangan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <hr class="my-6 border-slate-200">

    <!-- Tarif per Kelas -->
    <div>
        <div class="flex items-center gap-2 mb-3">
            <h3 class="text-sm font-semibold text-slate-800">Tarif per Kelas Perawatan</h3>
            <span class="text-[11px] text-slate-400">Baris default dipakai bila kelas spesifik belum diisi.</span>
        </div>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left" style="min-width: 500px;">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Tarif (Rp)</th>
                        <th class="px-3 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Tarif BPJS (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-blue-50/40">
                        <td class="px-3 py-2.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">DEFAULT</span>
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" name="tarif[default]" step="any" min="0" value="{{ old('tarif.default', ($hargaByKelas['default'] ?? null) ? $hargaByKelas['default']->tarif : '') }}" placeholder="0"
                                   class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" name="tarif_bpjs[default]" step="any" min="0" value="{{ old('tarif_bpjs.default', ($hargaByKelas['default'] ?? null) ? $hargaByKelas['default']->tarif_bpjs : '') }}" placeholder="0"
                                   class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                        </td>
                    </tr>
                    @foreach ($kelasList as $kls)
                        @php $key = (string) $kls->kelas_ruang_id; @endphp
                        <tr>
                            <td class="px-3 py-2.5 font-medium text-slate-700">{{ $kls->nama_kelas_ruang }}</td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="tarif[{{ $key }}]" step="any" min="0" value="{{ old("tarif.$key", ($hargaByKelas[$key] ?? null) ? $hargaByKelas[$key]->tarif : '') }}" placeholder="0"
                                       class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="tarif_bpjs[{{ $key }}]" step="any" min="0" value="{{ old("tarif_bpjs.$key", ($hargaByKelas[$key] ?? null) ? $hargaByKelas[$key]->tarif_bpjs : '') }}" placeholder="0"
                                       class="text-sm w-full text-right border border-slate-200 rounded-lg px-2 py-2 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
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
        <a href="{{ route('admin.master_tindakan.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tindakan' }}
        </button>
    </div>
</form>