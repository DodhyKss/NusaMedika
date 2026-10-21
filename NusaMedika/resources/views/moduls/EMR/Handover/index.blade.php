@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Handover / Serah Terima';
        $subtitleForm = 'Berita serah terima antar shift atau antar petugas beserta jejak pengakuan penerima.';
        $routeName = null;
        $routeUrl = url('emr/form/handover');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $jenisSerah = old('jenis_serah_terima', $emr_data['jenis_serah_terima'] ?? '');
        $headers = [
            'tanggal_serah_terima' => 'Tanggal',
            'shift' => 'Shift',
            'jenis_serah_terima' => 'Jenis',
            'status_kelengkapan_serah_terima' => 'Kelengkapan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Serah Terima"
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
                slug="handover"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU & JENIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu &amp; Jenis Serah Terima</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Serah Terima <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_serah_terima" name="tanggal_serah_terima"
                                   value="{{ $v('tanggal_serah_terima') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_serah_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Serah Terima <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_serah_terima" name="waktu_serah_terima" step="60"
                                   value="{{ $v('waktu_serah_terima') ? substr((string) $v('waktu_serah_terima'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_serah_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="shift" class="block font-bold text-slate-800 mb-1.5">
                                Shift <span class="text-red-500">*</span>
                            </label>
                            <select id="shift" name="shift"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('shift', $v('shift'), '-- Pilih Shift --') !!}
                            </select>
                            @error('shift')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                                Jenis Serah Terima <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_serah_terima" name="jenis_serah_terima"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('jenis_serah_terima', $jenisSerah, '-- Pilih Jenis --') !!}
                            </select>
                            @error('jenis_serah_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status_kelengkapan_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                                Status Kelengkapan <span class="text-red-500">*</span>
                            </label>
                            <select id="status_kelengkapan_serah_terima" name="status_kelengkapan_serah_terima"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('status_kelengkapan_serah_terima', $v('status_kelengkapan_serah_terima'), '-- Pilih Status --') !!}
                            </select>
                            @error('status_kelengkapan_serah_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Field kondisional: ruang tujuan hanya untuk Transfer Ruangan (B) --}}
                    <div id="wrap-ruang-tujuan" class="{{ $jenisSerah === 'B' ? '' : 'hidden' }}">
                        <label for="ruang_tujuan" class="block font-bold text-slate-800 mb-1.5">
                            Ruang Tujuan Serah Terima <span class="text-red-500">*</span>
                        </label>
                        <select id="ruang_tujuan" name="ruang_tujuan"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Ruang Tujuan --</option>
                            @foreach ($ruangTujuan as $ruang)
                                <option value="{{ $ruang->bagian_id }}" @selected((string) $v('ruang_tujuan') === (string) $ruang->bagian_id)>
                                    {{ $ruang->nama_bagian }}
                                </option>
                            @endforeach
                        </select>
                        @error('ruang_tujuan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PETUGAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Petugas Serah &amp; Terima</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="petugas_serah_id" class="block font-bold text-slate-800 mb-1.5">
                                Petugas Serah <span class="text-red-500">*</span>
                            </label>
                            <select id="petugas_serah_id" name="petugas_serah_id"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                <option value="">-- Pilih Petugas Serah --</option>
                                @foreach ($pegawais as $pegawai)
                                    <option value="{{ $pegawai->pegawai_id }}" @selected((string) $v('petugas_serah_id') === (string) $pegawai->pegawai_id)>
                                        {{ $pegawai->nama_pegawai }}
                                    </option>
                                @endforeach
                            </select>
                            @error('petugas_serah_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_petugas_serah" class="block font-bold text-slate-800 mb-1.5">Nama Petugas Serah</label>
                            <input type="text" id="nama_petugas_serah" name="nama_petugas_serah"
                                   value="{{ $v('nama_petugas_serah') }}" readonly
                                   class="w-full border border-slate-200 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                        </div>

                        <div>
                            <label for="petugas_terima_id" class="block font-bold text-slate-800 mb-1.5">
                                Petugas Terima <span class="text-red-500">*</span>
                            </label>
                            <select id="petugas_terima_id" name="petugas_terima_id"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                <option value="">-- Pilih Petugas Terima --</option>
                                @foreach ($pegawais as $pegawai)
                                    <option value="{{ $pegawai->pegawai_id }}" @selected((string) $v('petugas_terima_id') === (string) $pegawai->pegawai_id)>
                                        {{ $pegawai->nama_pegawai }}
                                    </option>
                                @endforeach
                            </select>
                            @error('petugas_terima_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nama_petugas_terima" class="block font-bold text-slate-800 mb-1.5">Nama Petugas Terima</label>
                            <input type="text" id="nama_petugas_terima" name="nama_petugas_terima"
                                   value="{{ $v('nama_petugas_terima') }}" readonly
                                   class="w-full border border-slate-200 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="waktu_konfirmasi_terima" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Konfirmasi Penerima <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_konfirmasi_terima" name="waktu_konfirmasi_terima" step="60"
                                   value="{{ $v('waktu_konfirmasi_terima') ? substr((string) $v('waktu_konfirmasi_terima'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_konfirmasi_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jumlah_pasien_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                                Jumlah Pasien Diserahterimakan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="jumlah_pasien_serah_terima" name="jumlah_pasien_serah_terima"
                                   min="1" max="200" step="1"
                                   value="{{ $v('jumlah_pasien_serah_terima') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('jumlah_pasien_serah_terima')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= ISI SERAH TERIMA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Isi Serah Terima</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="isi_serah_terima" class="block font-bold text-slate-800 mb-1.5">
                            Isi Serah Terima <span class="text-red-500">*</span>
                        </label>
                        <textarea id="isi_serah_terima" name="isi_serah_terima" rows="12"
                                  placeholder="Ringkasan kondisi pasien yang perlu diketahui petugas berikutnya."
                                  class="w-full border border-slate-300 rounded px-3 py-2 font-mono text-[13px] leading-relaxed focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('isi_serah_terima') }}</textarea>
                        <p class="mt-1 text-xs text-slate-400">
                            Isian awal diambil dari SBAR terakhir pada kunjungan ini. Teks ini boleh diubah /
                            dihapus sebelum disimpan.
                        </p>
                        @error('isi_serah_terima')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan_serah_terima" class="block font-bold text-slate-800 mb-1.5">Catatan Serah Terima</label>
                        <textarea id="catatan_serah_terima" name="catatan_serah_terima" rows="3"
                                  placeholder="Catatan tambahan (opsional)"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_serah_terima') }}</textarea>
                        @error('catatan_serah_terima')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tampil/sembunyi harus lewat style.display + lepas class "hidden"
            // (lihat AGENTS.md - utility .hidden Tailwind bisa dikalahkan utility
            // display lain, jadi keduanya harus dipasang).
            function tampilkan(el, tampil, display) {
                if (!el) return;
                if (tampil) {
                    el.classList.remove('hidden');
                    el.style.display = display;
                } else {
                    el.classList.add('hidden');
                    el.style.display = 'none';
                }
            }

            var jenis = document.getElementById('jenis_serah_terima');
            var wrapRuang = document.getElementById('wrap-ruang-tujuan');

            function syncJenis() {
                var val = jenis ? jenis.value : '';
                tampilkan(wrapRuang, val === 'B', 'block');
            }
            if (jenis) {
                jenis.addEventListener('change', syncJenis);
                syncJenis();
            }
        });
    </script>

@endsection
