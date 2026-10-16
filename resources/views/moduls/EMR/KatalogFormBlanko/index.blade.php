@extends('layouts.iframe')

@section('content')
    @php
        $opsiUmur = ['' => 'Semua Usia', 'Neonatus' => 'Neonatus', 'Anak' => 'Anak', 'Dewasa' => 'Dewasa', 'Geriatri' => 'Geriatri'];
        $opsiJk = ['' => 'Semua Gender', 'L' => 'Laki-laki (L)', 'P' => 'Perempuan (P)'];
        $opsiRawat = [
            '' => 'Semua Jenis Rawat',
            env('JENIS_RAWAT_RJ', 'RJ') => 'Rawat Jalan',
            env('JENIS_RAWAT_RI', 'RI') => 'Rawat Inap',
            env('JENIS_RAWAT_IGD', 'IGD') => 'IGD',
        ];
        $opsiProfesi = ['' => 'Semua Profesi', '1' => 'Dokter', '2' => 'Perawat', '7' => 'Perekam Medis'];
    @endphp

    <x-informasi-pasien />

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Katalog Form Blanko</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pilih formulir yang sesuai untuk mulai mengisi.</p>
            </div>
            <form method="GET" action="{{ route('emr.katalog_form_blanko.index', ['registrasi_detail_id' => $registrasi_detail_id]) }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Kategori Usia</label>
                    <select name="kategori_umur" class="border border-slate-300 rounded-md text-sm px-2.5 py-1.5">
                        @foreach ($opsiUmur as $val => $label)
                            <option value="{{ $val }}" @selected($filter['kategori_umur'] === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="border border-slate-300 rounded-md text-sm px-2.5 py-1.5">
                        @foreach ($opsiJk as $val => $label)
                            <option value="{{ $val }}" @selected($filter['jenis_kelamin'] === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Jenis Pelayanan</label>
                    <select name="jenis_rawat" class="border border-slate-300 rounded-md text-sm px-2.5 py-1.5">
                        @foreach ($opsiRawat as $val => $label)
                            <option value="{{ $val }}" @selected($filter['jenis_rawat'] === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Profesi</label>
                    <select name="profesi_id" class="border border-slate-300 rounded-md text-sm px-2.5 py-1.5">
                        @foreach ($opsiProfesi as $val => $label)
                            <option value="{{ $val }}" @selected((string) $filter['profesi_id'] === (string) $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors shadow-sm">Terapkan Filter</button>
                <a href="{{ route('emr.katalog_form_blanko.index', ['registrasi_detail_id' => $registrasi_detail_id]) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-600 text-sm font-semibold rounded-md hover:bg-slate-50 transition-colors">Reset</a>
            </form>
        </div>

        <div class="p-5">
            @if ($daftar->isEmpty())
                <div class="text-center py-10 text-slate-500 text-sm">
                    Tidak ada form blanko yang cocok dengan filter.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($daftar as $b)
                        @php
                            $batas = (array) ($b->filter_adaptif ?? []);
                            $badge = [];
                            if (! empty($batas['kategori_umur'])) {
                                $badge[] = 'Usia: '.implode(', ', (array) $batas['kategori_umur']);
                            }
                            if (! empty($batas['jenis_kelamin'])) {
                                $badge[] = 'Gender: '.implode(', ', (array) $batas['jenis_kelamin']);
                            }
                            if (! empty($batas['jenis_rawat'])) {
                                $badge[] = 'Layanan: '.implode(', ', (array) $batas['jenis_rawat']);
                            }
                            if (! empty($batas['profesi_id'])) {
                                $profesiNama = ['1' => 'Dokter', '2' => 'Perawat', '7' => 'Perekam Medis'];
                                $badge[] = 'Profesi: '.implode(', ', array_map(fn ($id) => $profesiNama[(string) $id] ?? ('#'.$id), (array) $batas['profesi_id']));
                            }
                        @endphp
                        <div class="border border-slate-200 rounded-xl p-4 bg-white hover:border-blue-300 hover:shadow-sm transition-all flex flex-col gap-3">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold text-slate-800 leading-snug">{{ $b->nama_blanko }}</h3>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200 shrink-0">v{{ $b->versi }}</span>
                                </div>
                                @if ($b->deskripsi)
                                    <p class="text-xs text-slate-500 mt-1">{{ $b->deskripsi }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-auto">
                                @if ($badge === [])
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Semua pasien</span>
                                @else
                                    @foreach ($badge as $teks)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">{{ $teks }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <a href="{{ route('emr.katalog_form_blanko.isi', ['form_blanko' => $b->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id]) }}"
                                   class="flex-1 text-center px-3 py-2 bg-blue-600 text-white text-xs font-bold rounded-md hover:bg-blue-700 transition-colors shadow-sm">Isi Formulir</a>
                                <a href="{{ route('emr.katalog_form_blanko.cetak', ['form_blanko' => $b->form_blanko_id]) }}" target="_blank" rel="noopener"
                                   class="px-3 py-2 bg-white border border-slate-300 text-slate-600 text-xs font-bold rounded-md hover:bg-slate-50 transition-colors" title="Cetak blanko kosong">Cetak Blanko</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div id="cek129Katalog" data-penanda="katalog-form-blanko"></div>
@endsection
