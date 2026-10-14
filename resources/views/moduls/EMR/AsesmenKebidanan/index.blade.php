@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Khusus Kebidanan & Penyakit Kandungan';
        $subtitleForm = 'Data obstetris (G/P/A), riwayat persalinan sebelumnya, komplikasi, riwayat operasi/penyakit, KB, dan menstruasi.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_kebidanan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'obstetrik_g' => 'G',
            'obstetrik_p' => 'P',
            'obstetrik_a' => 'A',
            'tanggal_asesmen' => 'Tanggal',
        ];

        $kolomObst = [
            'tempat' => 'Tempat Bersalin',
            'tahun' => 'Tahun',
            'kehamilan' => 'Kehamilan',
            'jenis_persalinan' => 'Jenis Persalinan',
            'penyulit' => 'Penyulit',
            'nifas' => 'Nifas',
            'sex' => 'Sex',
            'bb' => 'BB',
            'keadaan' => 'Keadaan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Kebidanan"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="$historyGrouped"
        :routeName="$routeName"
        :routeUrl="$routeUrl"
        :registrasiDetailId="$registrasiDetailId"
        :formAction="$formAction"
        :isEdit="$isEdit"
        :deleteAction="$deleteAction"
        :emrId="$emr_id"
        :isView="$isView"
        :printUrl="''"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="asesmen_kebidanan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ASESMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Asesmen <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_asesmen" value="{{ $v('jam_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Pemeriksa <span class="text-red-500">*</span></label>
                            <x-select_dokter name="dokter_pemeriksa_id" :selected="$v('dokter_pemeriksa_id')" required />
                            @error('dokter_pemeriksa_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= OBSTETRIK G/P/A ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Obstetrik (G/P/A)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-3 gap-4 max-w-md">
                        <div>
                            <label class="block text-xs font-medium mb-1">G <span class="text-red-500">*</span></label>
                            <input type="number" min="0" name="obstetrik_g" value="{{ $v('obstetrik_g') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('obstetrik_g') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">P <span class="text-red-500">*</span></label>
                            <input type="number" min="0" name="obstetrik_p" value="{{ $v('obstetrik_p') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('obstetrik_p') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">A <span class="text-red-500">*</span></label>
                            <input type="number" min="0" name="obstetrik_a" value="{{ $v('obstetrik_a') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('obstetrik_a') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT OBSTETRIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Obstetrik (Persalinan Sebelumnya)</span>
                </div>
                <div class="p-5 bg-white text-sm overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="border px-2 py-1 text-left">No</th>
                                @foreach ($kolomObst as $kolom => $label)
                                    <th class="border px-2 py-1 text-left whitespace-nowrap">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @for ($baris = 1; $baris <= 5; $baris++)
                                <tr>
                                    <td class="border px-2 py-1 text-center font-medium">{{ $baris }}</td>
                                    @foreach ($kolomObst as $kolom => $label)
                                        <td class="border px-1 py-1">
                                            <input type="text" name="riwayat_obst_{{ $kolom }}_{{ $baris }}"
                                                value="{{ $v('riwayat_obst_'.$kolom.'_'.$baris) }}"
                                                class="w-full border-0 border-b border-slate-200 px-1 py-0.5 text-xs focus:outline-none focus:ring-0"
                                                style="min-width:80px">
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= KOMPLIKASI & KELUHAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Komplikasi &amp; Keluhan Kebidanan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="komplikasi_hyperemesis" value="0"><input type="checkbox" name="komplikasi_hyperemesis" value="1" {{ $v('komplikasi_hyperemesis') === '1' ? 'checked' : '' }}> Hyperemesis</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="komplikasi_toksemia" value="0"><input type="checkbox" name="komplikasi_toksemia" value="1" {{ $v('komplikasi_toksemia') === '1' ? 'checked' : '' }}> Toksemia</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="komplikasi_eklampsi" value="0"><input type="checkbox" name="komplikasi_eklampsi" value="1" {{ $v('komplikasi_eklampsi') === '1' ? 'checked' : '' }}> Eklampsi</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="komplikasi_pph" value="0"><input type="checkbox" name="komplikasi_pph" value="1" {{ $v('komplikasi_pph') === '1' ? 'checked' : '' }}> PPH (Pendarahan Postpartum)</label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan Utama</label>
                        <textarea name="keluhan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan') }}</textarea>
                        @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Operasi Ibu</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div>
                        <label class="block text-xs font-medium mb-2">Pernah Operasi? <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="riwayat_operasi_pernah" value="Pernah" class="opsi-operasi" {{ $v('riwayat_operasi_pernah') === 'Pernah' ? 'checked' : '' }}> Pernah</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="riwayat_operasi_pernah" value="Tidak Pernah" class="opsi-operasi" {{ $v('riwayat_operasi_pernah') === 'Tidak Pernah' ? 'checked' : '' }}> Tidak Pernah</label>
                        </div>
                        @error('riwayat_operasi_pernah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-operasi" style="{{ $v('riwayat_operasi_pernah') === 'Pernah' ? '' : 'display:none' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                                <input type="text" name="riwayat_operasi_jenis" value="{{ $v('riwayat_operasi_jenis') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('riwayat_operasi_jenis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Detail</label>
                                <input type="text" name="riwayat_operasi_detail" value="{{ $v('riwayat_operasi_detail') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('riwayat_operasi_detail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT PENYAKIT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Penyakit Lain Ibu</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="penyakit_jantung" value="0"><input type="checkbox" name="penyakit_jantung" value="1" {{ $v('penyakit_jantung') === '1' ? 'checked' : '' }}> Penyakit Jantung</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="penyakit_diabetes" value="0"><input type="checkbox" name="penyakit_diabetes" value="1" {{ $v('penyakit_diabetes') === '1' ? 'checked' : '' }}> Diabetes</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="penyakit_paru" value="0"><input type="checkbox" name="penyakit_paru" value="1" {{ $v('penyakit_paru') === '1' ? 'checked' : '' }}> Penyakit Paru</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="penyakit_vemerik" value="0"><input type="checkbox" name="penyakit_vemerik" value="1" {{ $v('penyakit_vemerik') === '1' ? 'checked' : '' }}> Vemerik</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="penyakit_lainnya" value="0"><input type="checkbox" name="penyakit_lainnya" value="1" id="cb-penyakit-lainnya" class="opsi-penyakit-lainnya" {{ $v('penyakit_lainnya') === '1' ? 'checked' : '' }}> Lainnya</label>
                    </div>
                    <div id="blok-penyakit-lainnya" style="{{ $v('penyakit_lainnya') === '1' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Keterangan Penyakit Lain <span class="text-red-500">*</span></label>
                        <input type="text" name="penyakit_keterangan" value="{{ $v('penyakit_keterangan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('penyakit_keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KB & MENSTRUASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Reproduksi &amp; Menstruasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Program KB? <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="program_kb_pernah" value="Pernah" class="opsi-kb" {{ $v('program_kb_pernah') === 'Pernah' ? 'checked' : '' }}> Pernah</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="program_kb_pernah" value="Tidak Pernah" class="opsi-kb" {{ $v('program_kb_pernah') === 'Tidak Pernah' ? 'checked' : '' }}> Tidak Pernah</label>
                        </div>
                        @error('program_kb_pernah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-kb" style="{{ $v('program_kb_pernah') === 'Pernah' ? '' : 'display:none' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Jenis KB <span class="text-red-500">*</span></label>
                                <input type="text" name="program_kb_jenis" value="{{ $v('program_kb_jenis') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('program_kb_jenis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Sejak</label>
                                <input type="text" name="program_kb_sejak" value="{{ $v('program_kb_sejak') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('program_kb_sejak') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Menarche (umur) <span class="text-red-500">*</span></label>
                            <input type="text" name="menarche" value="{{ $v('menarche') }}" placeholder="cth: 13 th" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('menarche') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Haid Terakhir <span class="text-red-500">*</span></label>
                            <input type="date" name="haid_terakhir" value="{{ $v('haid_terakhir') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('haid_terakhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Haid (hari) <span class="text-red-500">*</span></label>
                            <input type="text" name="haid_lama" value="{{ $v('haid_lama') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('haid_lama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Haid Sebelumnya</label>
                            <input type="date" name="haid_sebelumnya" value="{{ $v('haid_sebelumnya') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('haid_sebelumnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Haid Sebelumnya</label>
                            <input type="text" name="haid_lama_2" value="{{ $v('haid_lama_2') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('haid_lama_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Siklus Haid <span class="text-red-500">*</span></label>
                            <input type="text" name="siklus_haid" value="{{ $v('siklus_haid') }}" placeholder="cth: 28 hari" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('siklus_haid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-2">Dismenore <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="dismenore" value="Ya" class="opsi-dismenore" {{ $v('dismenore') === 'Ya' ? 'checked' : '' }}> Ya</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="dismenore" value="Tidak" class="opsi-dismenore" {{ $v('dismenore') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                            </div>
                            @error('dismenore') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-2">Keteraturan Haid <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="keteraturan_haid" value="Teratur" {{ $v('keteraturan_haid') === 'Teratur' ? 'checked' : '' }}> Teratur</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="keteraturan_haid" value="Tidak Teratur" {{ $v('keteraturan_haid') === 'Tidak Teratur' ? 'checked' : '' }}> Tidak Teratur</label>
                            </div>
                            @error('keteraturan_haid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="blok-dismenore" style="{{ $v('dismenore') === 'Ya' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Dismenore Sejak <span class="text-red-500">*</span></label>
                        <input type="text" name="dismenore_sejak" value="{{ $v('dismenore_sejak') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dismenore_sejak') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                function toggle(selector, targetId, matchValue) {
                    document.querySelectorAll(selector).forEach(function (el) {
                        el.addEventListener('change', function () {
                            document.getElementById(targetId).style.display = (this.value === matchValue) ? '' : 'none';
                        });
                    });
                }
                toggle('.opsi-operasi', 'blok-operasi', 'Pernah');
                toggle('.opsi-kb', 'blok-kb', 'Pernah');
                toggle('.opsi-dismenore', 'blok-dismenore', 'Ya');
                document.querySelectorAll('.opsi-penyakit-lainnya').forEach(function (cb) {
                    cb.addEventListener('change', function () {
                        document.getElementById('blok-penyakit-lainnya').style.display = this.checked ? '' : 'none';
                    });
                });
            })();
        </script>
    </x-emr-split-layout>
@endsection
