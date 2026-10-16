@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surveilans Pasien Terpasang CVC/PICC';
        $subtitleForm = 'Bundle pencegahan CLABSI harian — lokasi, nomor kateter, lumen, item pencegahan & kriteria BSI.';
        $routeName = null;
        $routeUrl = url('emr/form/surveilans_cvc_picc');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tgl Evaluasi',
            'lokasi_subclavia' => 'Lokasi',
            'bsi_pasang' => 'Pasang',
            'bsi_lepas' => 'Lepas',
        ];

        $jawaban = ['Ya', 'Tidak', 'N/A'];
        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };

        $lokasiItem = [
            'lokasi_subclavia' => 'Subclavia',
            'lokasi_jugularis' => 'Jugularis',
            'lokasi_femoralis' => 'Femoralis',
        ];
        $cathItem = [
            'cath_12' => 'Fr 12', 'cath_7' => 'Fr 7', 'cath_5' => 'Fr 5',
            'cath_4' => 'Fr 4', 'cath_3' => 'Fr 3',
        ];
        $lumenItem = [
            'cvc_4lumen' => '4 Lumen', 'cvc_3lumen' => '3 Lumen',
            'cvc_2lumen' => '2 Lumen', 'cvc_1lumen' => '1 Lumen',
        ];
        $bsiPencegahan = [
            'bsi_1' => 'Cuci tangan sesuai 5 moment',
            'bsi_2' => 'Pemasangan dengan teknik aseptik',
            'bsi_3' => 'Masker, penutup kepala, gown steril, duk steril saat insersi',
            'bsi_4' => 'Bersihkan area insersi dengan antiseptik',
            'bsi_5' => 'Teknik aseptik saat injeksi / sambung tubing',
            'bsi_6' => 'Kondisi dressing baik (bersih, fiksasi baik)',
            'bsi_7' => 'Ganti dressing setiap hari / kotor',
        ];
        $bsiGejala = [
            'bsi_8' => 'a. Kuman pada kultur darah',
            'bsi_9' => 'b. Demam > 38 °C',
            'bsi_10' => 'Hipotermi',
            'bsi_11' => 'Hipotensi',
            'bsi_12' => 'Apneu',
            'bsi_13' => 'Bradikardi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surveilans CVC/PICC"
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
                slug="surveilans_cvc_picc"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS EVALUASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Evaluasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Evaluasi <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_evaluasi" value="{{ $v('waktu_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="hidden md:block"></div>
                </div>
            </div>

            {{-- ================= LOKASI, NOMOR KATETER & LUMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Lokasi, Nomor Kateter &amp; Jenis Lumen</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Lokasi Pemasangan <span class="text-red-500">*</span> <span class="font-normal">(tepat satu Ya)</span></p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 max-w-4xl">
                            @foreach ($lokasiItem as $key => $label)
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                    {!! $sel($key, $jawaban) !!}
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Lainnya (sebutkan)</label>
                                <input type="text" id="lokasi_lainnya" name="lokasi_lainnya" value="{{ $v('lokasi_lainnya') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('lokasi_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Nomor Kateter <span class="text-red-500">*</span> <span class="font-normal">(tepat satu Ya)</span></p>
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 max-w-4xl">
                            @foreach ($cathItem as $key => $label)
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                    {!! $sel($key, $jawaban) !!}
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Lainnya (sebutkan)</label>
                                <input type="text" id="cath_lainnya" name="cath_lainnya" value="{{ $v('cath_lainnya') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('cath_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Jenis CVC/PICC (Lumen) <span class="text-red-500">*</span> <span class="font-normal">(tepat satu Ya)</span></p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-3xl">
                            @foreach ($lumenItem as $key => $label)
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                    {!! $sel($key, $jawaban) !!}
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= STATUS PASANG / LEPAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Status Pemasangan &amp; Pelepasan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4 max-w-4xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pasang hari ini <span class="text-red-500">*</span></label>
                        {!! $sel('bsi_pasang', $jawaban) !!}
                        @error('bsi_pasang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Lepas hari ini <span class="text-red-500">*</span></label>
                        {!! $sel('bsi_lepas', $jawaban) !!}
                        @error('bsi_lepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pasang</label>
                        <input type="date" name="tanggal_pasang" value="{{ $v('tanggal_pasang') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pasang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Lepas <span class="text-[11px] text-slate-400">(bila "Lepas" = Ya)</span></label>
                        <input type="date" id="tanggal_lepas" name="tanggal_lepas" value="{{ $v('tanggal_lepas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_lepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= ITEM PENCEGAHAN BSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200">
                    <span class="text-sky-700 font-medium text-[15px]">Item Pencegahan BSI (Blood Stream Infection)</span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($bsiPencegahan as $key => $label)
                        <div class="py-2 grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                            <span class="text-sm">{{ $loop->iteration }}. {{ $label }}</span>
                            <div>
                                {!! $sel($key, $jawaban) !!}
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= KRITERIA BSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-red-700 font-medium text-[15px]">Gejala &amp; Tanda BSI (Kriteria CLABSI)</span>
                    <span id="badge-clabsi" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Kriteria BSI terpenuhi — hubungi IPCN
                    </span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($bsiGejala as $key => $label)
                        <div class="py-2 grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                            <span class="text-sm">{{ $label }}</span>
                            <div>
                                {!! $sel($key, $jawaban) !!}
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                    <div class="pt-3">
                        <label class="block text-xs font-medium mb-1">Keterangan (nama kuman hasil kultur &amp; sensitivitas)</label>
                        <textarea name="keterangan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek116Clabsi() {
                    var ya = 0;
                    for (var i = 8; i <= 13; i++) {
                        var el = document.querySelector('select[name="bsi_' + i + '"]');
                        if (el && el.value === 'Ya') { ya++; }
                    }
                    var badge = document.getElementById('badge-clabsi');
                    if (badge) { badge.style.display = ya > 0 ? '' : 'none'; }
                }

                document.querySelectorAll('select[name^="bsi_"]').forEach(function (el) {
                    el.addEventListener('change', cek116Clabsi);
                });
                cek116Clabsi();
            })();
        </script>
    </x-emr-split-layout>
@endsection
