<x-emr-accordion id="acc-informasi-pasien" title="A. Informasi Pasien" bgClass="bg-slate-100" :isOpen="true">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
        <!-- Kiri: Informasi Dasar & Keluarga -->
        <div class="space-y-6">
            <div>
                <h3 class="font-bold text-slate-800 mb-3">Informasi Dasar Pasien</h3>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Agama <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="agama" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" value="{{ $emr_data[env('OBJEK_ID_AGAMA')]['agama'] }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Nilai-nilai budaya / kepercayaan <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="nilai_budaya" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Tingkat Pendidikan <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="pendidikan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" value="{{ $emr_data[env('OBJEK_ID_TINKAT_PENDIDIKAN')]['tingkat_pendidikan'] }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Pekerjaan <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="pekerjaan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Suku Bangsa <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="suku_bangsa" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Kebangsaan <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="kebangsaan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">No. Hp / Tlp. Pasien <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="no_hp" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-slate-800 mb-3">Informasi Keluarga</h3>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Nama Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="nama_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Usia Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="usia_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Pendidikan Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="pendidikan_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Pekerjaan Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="pekerjaan_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Suku Bangsa Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="suku_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Kebangsaan Suami / Istri <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="kebangsaan_pasangan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Tinggal Bersama <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="tinggal_bersama" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Penanggung Jawab Pasien <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="penanggung_jawab" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center mb-2">
                    <label class="col-span-1 text-slate-600">Hubungan <span class="text-red-500">*</span></label>
                    <div class="col-span-2 flex items-center gap-2">
                        <span>:</span> <input type="text" name="hubungan" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Nilai Nilai Kebudayaan -->
        <div>
            <h3 class="font-bold text-slate-800 mb-3">Nilai Nilai Kebudayaan / Kepercayaan</h3>
            
            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:w-1/2">
                    <span class="text-slate-600">Aktifitas Sebelum Makan <span class="text-red-500">*</span></span>
                </div>
                <div class="w-full sm:w-1/2 flex flex-col gap-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="aktifitas_makan" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                        <span class="ml-2 text-slate-600">Tidak</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="aktifitas_makan" value="ya" class="text-blue-500 focus:ring-blue-500">
                            <span class="ml-2 mr-2 text-slate-600">Ya</span>
                        </label>
                        <input type="text" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" name="aktifitas_makan_ya">
                    </div>
                </div>
            </div>

            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:w-1/2">
                    <span class="text-slate-600">Pantangan Pulang Hari Tertentu <span class="text-red-500">*</span></span>
                </div>
                <div class="w-full sm:w-1/2 flex flex-col gap-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pantangan_pulang" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                        <span class="ml-2 text-slate-600">Tidak</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="pantangan_pulang" value="ya" class="text-blue-500 focus:ring-blue-500">
                            <span class="ml-2 mr-2 text-slate-600">Ya</span>
                        </label>
                        <input type="text" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" name="pantangan_pulang_ya">
                    </div>
                </div>
            </div>

            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:w-1/2">
                    <span class="text-slate-600">Pantangan Menerima Transfusi Darah <span class="text-red-500">*</span></span>
                </div>
                <div class="w-full sm:w-1/2 flex flex-col gap-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pantangan_transfusi" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                        <span class="ml-2 text-slate-600">Tidak</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="pantangan_transfusi" value="ya" class="text-blue-500 focus:ring-blue-500">
                            <span class="ml-2 mr-2 text-slate-600">Ya</span>
                        </label>
                        <input type="text" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" name="pantangan_transfusi_ya">
                    </div>
                </div>
            </div>

            <div class="mb-4 flex flex-col sm:flex-row gap-2">
                <div class="w-full sm:w-1/2">
                    <span class="text-slate-600">Pantangan Makan <span class="text-red-500">*</span></span>
                </div>
                <div class="w-full sm:w-1/2 flex flex-col gap-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pantangan_makan" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                        <span class="ml-2 text-slate-600">Tidak</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="pantangan_makan" value="ya" class="text-blue-500 focus:ring-blue-500">
                            <span class="ml-2 mr-2 text-slate-600">Ya</span>
                        </label>
                        <input type="text" class="form-input border border-slate-300 rounded w-full h-8 px-2 focus:border-blue-500 focus:ring-blue-500" name="pantangan_makan_ya">
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-emr-accordion>
