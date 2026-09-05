<x-emr-accordion id="acc-riwayat-penyakit" title="B. Riwayat Penyakit" bgClass="bg-yellow-50" :isOpen="false">
    <div class="space-y-5 text-sm">
        <div>
            <label class="block font-bold text-slate-800 mb-1">Diagnosa Medis</label>
            <textarea type="text" name="diagnosa_medis" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan diagnosa medis...">{{ $emr_data ?? '' }}</textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Keluhan Utama</label>
            <textarea name="keluhan" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik keluhan utama...">{{ $emr_data ?? '' }}</textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Riwayat Penyakit Sebelumnya</label>
            <textarea name="riwayat_penyakit_sebelumnya" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik riwayat penyakit sebelumnya...">{{ $emr_data ?? '' }}</textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Riwayat Penyakit Sekarang</label>
            <textarea name="riwayat_penyakit_sekarang" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik riwayat penyakit sekarang...">{{ $emr_data ?? '' }}</textarea>
        </div>
        
        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Apakah Termasuk Jenis Penyakit Infeksius ? <span class="text-red-500 text-xs font-normal">*</span></h4>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="infeksius_flag" value="Tidak" onclick="document.getElementById('extendedInfeksius').classList.add('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="infeksius_flag" value="Ya" onclick="document.getElementById('extendedInfeksius').classList.remove('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
            </div>
            
            <div id="extendedInfeksius" class="mt-4 p-4 bg-white border border-slate-200 rounded-md space-y-4 {{ (isset($emr_data) && $emr_data == 'Ya') ? '' : 'hidden' }}">
                <div>
                    <h5 class="font-bold text-slate-800 mb-2">Menular Melalui <span class="text-red-500 text-xs font-normal">*</span></h5>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach(['Udara', 'Droplet', 'Kontak Langsung / Kulit', 'Makanan', 'Air', 'Susu / ASI', 'Cairan Tubuh', 'Hewan'] as $menular)
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="menular_melalui" value="{{ $menular }}" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == $menular) ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">{{ $menular }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h5 class="font-bold text-slate-800 mb-2">Memerlukan Isolasi <span class="text-red-500 text-xs font-normal">*</span></h5>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="infeksius_memerlukan_isolasi" value="Ya" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Ya</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="infeksius_memerlukan_isolasi" value="Tidak" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Tidak</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Hasil Penunjang</label>
                    <textarea name="infeksius_hasil_penunjang" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Masukkan Hasil Pemeriksaan Penunjang...">{{ $emr_data ?? '' }}</textarea>
                </div>
            </div>
        </div>
        
        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Penurunan Imunologi / Daya Tahan Tubuh ? <span class="text-red-500 text-xs font-normal">*</span></h4>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="imunologi_flag" value="Tidak" onclick="document.getElementById('extendedImunologi').classList.add('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="imunologi_flag" value="Ya" onclick="document.getElementById('extendedImunologi').classList.remove('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
            </div>
            
            <div id="extendedImunologi" class="mt-4 p-4 bg-white border border-slate-200 rounded-md space-y-4 {{ (isset($emr_data) && $emr_data == 'Ya') ? '' : 'hidden' }}">
                <div>
                    <h5 class="font-bold text-slate-800 mb-2">Memerlukan Isolasi <span class="text-red-500 text-xs font-normal">*</span></h5>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="imunologi_memerlukan_isolasi" value="Ya" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Ya</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="imunologi_memerlukan_isolasi" value="Tidak" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Tidak</span>
                        </label>
                    </div>
                </div>
                <div>
                    <h5 class="font-bold text-slate-800 mb-2">Perlu Pembatasan Pengunjung <span class="text-red-500 text-xs font-normal">*</span></h5>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="imunologi_pembatasan_pengunjung" value="Ya" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Ya</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="imunologi_pembatasan_pengunjung" value="Tidak" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                            <span class="ml-2 text-slate-600">Tidak</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Hasil Penunjang</label>
                    <textarea name="imunologi_hasil_penunjang" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Masukkan Hasil Pemeriksaan Penunjang...">{{ $emr_data ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Riwayat Vaksin Covid 19</h4>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="vaksin_covid" value="1" onclick="document.getElementById('extendedVaksin').classList.remove('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == '1') ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="vaksin_covid" value="2" onclick="document.getElementById('extendedVaksin').classList.add('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == '2') ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Ya, Lupa tanggal vaksin</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="vaksin_covid" value="3" onclick="document.getElementById('extendedVaksin').classList.add('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == '3') || !isset($emr_data) ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
            </div>
            
            <div id="extendedVaksin" class="mt-4 p-4 bg-white border border-slate-200 rounded-md space-y-4 {{ (isset($emr_data) && $emr_data == '1') ? '' : 'hidden' }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tanggal Vaksin Pertama</label>
                        <input type="date" name="tanggal_covid_1" value="{{ $emr_data ?? '' }}" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tanggal Vaksin Kedua</label>
                        <input type="date" name="tanggal_covid_2" value="{{ $emr_data ?? '' }}" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Apakah memiliki riwayat Operasi / Kemoterapi / Radioterapi ? <span class="text-red-500 text-xs font-normal">*</span></h4>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="riw_ope_kemo" value="Tidak" onclick="document.getElementById('extendedRiwayatOperasi').classList.add('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Tidak') || !isset($emr_data) ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="riw_ope_kemo" value="Ya" onclick="document.getElementById('extendedRiwayatOperasi').classList.remove('hidden')" class="text-blue-500 focus:ring-blue-500" {{ (isset($emr_data) && $emr_data == 'Ya') ? 'checked' : '' }}>
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
            </div>
            
            <div id="extendedRiwayatOperasi" class="mt-4 p-4 bg-white border border-slate-200 rounded-md space-y-4 {{ (isset($emr_data) && $emr_data == 'Ya') ? '' : 'hidden' }}">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Riwayat Operasi</label>
                    <textarea name="riwayat_operasi" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Masukkan keterangan dan tanggal...">{{ $emr_data ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Riwayat Kemoterapi</label>
                    <textarea name="riwayat_kemoterapi" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Masukkan keterangan dan tanggal...">{{ $emr_data ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Riwayat Radioterapi</label>
                    <textarea name="riwayat_radioterapi" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Masukkan keterangan dan tanggal...">{{ $emr_data ?? '' }}</textarea>
                </div>
            </div>
        </div>

    </div>
</x-emr-accordion>
