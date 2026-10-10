@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Tanda Vital';
        $subtitleForm = 'Observasi harian: tanda vital, penilaian nyeri, kesadaran, dan pemberian oksigen.';
        $routeName = null;
        $routeUrl = url('emr/form/tanda_vital');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Nilai yang sudah tersimpan (atau kosong saat membuat baru). Dipakai
        // partial Nyeri & Oksigen untuk menentukan blok mana yang tampil,
        // sehingga tampilan sudah benar SEBELUM JS berjalan.
        $nyeriTerpilih = (string) old('nyeri', $emr_data['nyeri'] ?? 'Tidak');
        $oksigenTerpilih = (string) old('oksigen', $emr_data['oksigen'] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Tanda Vital"
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
                slug="tanda_vital"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'tanggal_observasi' => 'Tanggal',
                    'waktu_observasi' => 'Jam',
                    'td_sistolik' => 'Sistolik',
                    'td_diastolik' => 'Diastolik',
                    'nadi' => 'Nadi',
                    'pernapasan' => 'Napas',
                    'suhu' => 'Suhu',
                    'saturasi' => 'SpO2',
                    'total_ews' => 'Skor EVM',
                ]"
                :badges="[
                    'kategori_ews' => [
                        'Rendah' => ['label' => 'Risiko Rendah', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'Sedang' => ['label' => 'Risiko Sedang', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                        'Tinggi' => ['label' => 'Risiko Tinggi', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                    ],
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- Tiga partial: seluruh isi form ada di sini supaya mudah
                 dibandingkan dengan form EMR lain. --}}
            @include('moduls.EMR.PartialForm.observasi_harian_tanda_vital')
            @include('moduls.EMR.PartialForm.observasi_harian_penilaian_nyeri')
            @include('moduls.EMR.PartialForm.observasi_harian_kesadaran_oksigen')

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lompat ke input parameter EVM: scroll ke field lalu sorot singkat,
        // supaya petugas tahu persis di mana harus mengetik.oksigen &
        // kesadaran berada di partial "Kesadaran dan Pemberian Oksigen", lima
        // parameter lainnya di partial "Tanda Vital".
        function lompatKeInput(parameter) {
            var el = document.getElementById(parameter);
            if (!el) return;

            // Untuk select2, sorot wrapper-nya; select aslinya disembunyikan
            // oleh Select2 sehingga ring di elemen itu tidak terlihat.
            var target = el.closest('.relative') || el.parentElement || el;

            if (target.scrollIntoView) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            target.classList.add('ring-2', 'ring-amber-400', 'ring-offset-2', 'rounded');
            setTimeout(function () {
                target.classList.remove('ring-2', 'ring-amber-400', 'ring-offset-2');
            }, 2400);

            // Fokuskan juga elemen aslinya supaya bisa langsung diketik.
            try {
                el.focus({ preventScroll: true });
            } catch (e) {
                /* beberapa browser menolak focus pada select yang disabled */
            }
        }

        document.querySelectorAll('[data-ews-lompat]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                lompatKeInput(btn.getAttribute('data-ews-lompat'));
            });
        });

        // Tampil/sembunyi memakai INLINE STYLE, bukan class `hidden` saja.
        //
        // Tailwind v4 menaruh `.hidden` (display:none) SEBELUM `.inline-flex`
        // (display:inline-flex) di hasil CSS. Keduanya specificity sama, jadi
        // aturan yang belakangan menang. Kelas `hidden` ikut dilepas/dipasang
        // karena mengosongkan inline style saja tidak cukup: markup yang masih
        // memuat class `hidden` tetap `display:none`.
        function tampilkan(el, tampil, display) {
            if (!el) return;

            el.classList.toggle('hidden', !tampil);
            el.style.display = tampil ? (display || '') : 'none';
        }

        var isView = {{ $isView ? 'true' : 'false' }};

        // ---------------------------------------------------------------
        // Skor nyeri: tampil hanya saat Nyeri = Ya.
        // ---------------------------------------------------------------
        var wrapSkor = document.getElementById('wrapSkorNyeri');

        function syncNyeri() {
            var dipilih = document.querySelector('input.nyeri-pilih:checked');
            var ada = !!(dipilih && dipilih.value === 'Ya');

            tampilkan(wrapSkor, ada);
        }

        document.querySelectorAll('input.nyeri-pilih').forEach(function (r) {
            r.addEventListener('change', syncNyeri);
        });
        syncNyeri();

        // ---------------------------------------------------------------
        // Rincian oksigen (cara + flow rate + ETT): hanya saat memakai
        // oksigen. Nilai dikirim apa adanya; yang mengosongkannya saat
        // Oksigen = Air adalah filteredData() di server, bukan browser.
        // ---------------------------------------------------------------
        var wrapOksigen = document.getElementById('wrapDetailOksigen');

        function syncOksigen() {
            var el = document.getElementById('oksigen');
            var ada = !!(el && el.value === 'Oksigen');

            tampilkan(wrapOksigen, ada);
        }

        var inputOksigen = document.getElementById('oksigen');
        if (inputOksigen) {
            inputOksigen.addEventListener('change', syncOksigen);
        }
        syncOksigen();

        // ---------------------------------------------------------------
        // BMI: pratinjau di browser. Angka FINAL tetap dihitung ulang di
        // server (filteredData) sehingga tidak bisa dimanipulasi.
        // ---------------------------------------------------------------
        var berat = document.getElementById('berat_badan');
        var tinggi = document.getElementById('tinggi_badan');
        var outBmi = document.getElementById('preview_bmi');
        var outKategori = document.getElementById('preview_bmi_kategori');

        function hitungBmi() {
            if (!outBmi || !berat || !tinggi) return;

            var b = parseFloat(berat.value);
            var t = parseFloat(tinggi.value);

            if (!b || b <= 0 || !t || t <= 0) {
                // Kosongkan hanya bila belum ada nilai tersimpan di server.
                if (isView) return;
                outBmi.textContent = '-';
                if (outKategori) outKategori.textContent = '';
                return;
            }

            var bmi = b / Math.pow(t / 100, 2);
            outBmi.textContent = bmi.toFixed(1);

            if (outKategori) {
                if (bmi < 18.5) outKategori.textContent = '(Kurus)';
                else if (bmi < 25) outKategori.textContent = '(Normal)';
                else if (bmi < 30) outKategori.textContent = '(Overweight)';
                else outKategori.textContent = '(Obesitas)';
            }
        }

        if (berat) berat.addEventListener('input', hitungBmi);
        if (tinggi) tinggi.addEventListener('input', hitungBmi);
        hitungBmi();

        // ---------------------------------------------------------------
        // EVM / Early Warning Score — pratinjau live.
        //
        // Skor di bawah MENCERMINKAN App\Helpers\EwsHelper (RCPCH, 7
        // parameter). Nilai final tetap dihitung ulang di server saat disimpan
        // (filteredData), jadi tabel ini murni tampilan bantu.
        // ---------------------------------------------------------------
        var PARAMETER_EWS = @json(\App\Helpers\EwsHelper::PARAMETER);

        // Warna badge diambil dari helper yang sama supaya JS dan server
        // tidak pernah berbeda (sumber kebenaran tetap App\Helpers\EwsHelper).
        var WARNA_SKOR = @json(array_values(\App\Helpers\EwsHelper::SKOR_WARNA));
        var BADGE_KATEGORI = @json(\App\Helpers\EwsHelper::KATEGORI_WARNA);
        var LABEL_TIDAK_DIUKUR = @json(\App\Helpers\EwsHelper::LABEL_TIDAK_DIUKUR);

        // Skor satu parameter. Mengembalikan null bila nilai kosong atau di luar
        // rentang wajar — sama seperti EwsHelper::skor() di server.
        function skorEws(parameter, mentah) {
            var v = (mentah === null || mentah === undefined) ? '' : String(mentah).trim();
            if (v === '') return null;

            var n = parseFloat(v);

            switch (parameter) {
                case 'pernapasan':
                    if (isNaN(n) || n < 1 || n > 60) return null;
                    return n <= 8 ? 3 : (n <= 11 ? 1 : (n <= 20 ? 0 : (n <= 24 ? 2 : 3)));
                case 'saturasi':
                    if (isNaN(n) || n < 50 || n > 100) return null;
                    return n <= 89 ? 3 : (n <= 91 ? 2 : (n <= 95 ? 1 : 0));
                case 'oksigen':
                    return v === 'Air' ? 0 : (v === 'Oksigen' ? 2 : null);
                case 'td_sistolik':
                    if (isNaN(n) || n < 50 || n > 300) return null;
                    return n <= 90 ? 3 : (n <= 100 ? 2 : (n <= 110 ? 1 : (n <= 219 ? 0 : 3)));
                case 'nadi':
                    if (isNaN(n) || n < 20 || n > 250) return null;
                    return n <= 40 ? 3 : (n <= 50 ? 1 : (n <= 90 ? 0 : (n <= 110 ? 1 : (n <= 130 ? 2 : 3))));
                case 'kesadaran':
                    return v === 'Compos Mentis' ? 0 : 3;
                case 'suhu':
                    if (isNaN(n) || n < 25 || n > 45) return null;
                    return n <= 35 ? 3 : (n <= 36 ? 1 : (n <= 38 ? 0 : (n <= 39 ? 1 : 2)));
                default:
                    return null;
            }
        }

        function nilaiParamEws(parameter) {
            var el = document.getElementById(parameter);
            return el ? el.value : '';
        }

        // Apakah parameter ini ditandai "tidak diukur" oleh petugas?
        function paramNa(parameter) {
            var cb = document.querySelector('input.ews-na[value="' + parameter + '"]');
            return !!(cb && cb.checked);
        }

        function hitungEws() {
            var total = 0;
            var terukur = 0;
            var selesai = 0;
            var jumlahNa = 0;
            var kurang = [];

            PARAMETER_EWS.forEach(function (parameter) {
                var baris = document.querySelector('tr[data-ews-param="' + parameter + '"]');
                if (!baris) return;

                var na = paramNa(parameter);
                var mentah = nilaiParamEws(parameter);
                var ada = !na && String(mentah === null ? '' : mentah).trim() !== '';
                var skor = ada ? skorEws(parameter, mentah) : null;

                var selNilai = baris.querySelector('[data-ews-nilai]');
                var selSkor = baris.querySelector('[data-ews-skor]');
                var selLabel = baris.querySelector('td');

                // Parameter yang ditandai tidak diukur: dianggap SUDAH SELESAI
                // dengan kontribusi 0, sama seperti EwsHelper::hitung() di server.
                if (na) {
                    jumlahNa++;
                    selesai++;

                    if (selNilai) selNilai.textContent = LABEL_TIDAK_DIUKUR;
                    if (selSkor) {
                        selSkor.textContent = LABEL_TIDAK_DIUKUR;
                        selSkor.className = 'px-3 py-2 text-center text-slate-400 bg-slate-100 rounded';
                    }
                    if (baris.classList) baris.classList.add('bg-slate-50');
                    return;
                }

                if (baris.classList) baris.classList.remove('bg-slate-50');

                if (selNilai) selNilai.textContent = ada ? String(mentah) : '-';

                if (skor === null) {
                    // Kosong ATAU di luar rentang wajar: belum bisa dinilai.
                    // Label diambil dari sel pertama baris tabel, jadi tidak
                    // perlu salinan daftar label di JS.
                    kurang.push(selLabel ? selLabel.textContent.trim() : parameter);

                    if (selSkor) {
                        selSkor.textContent = '-';
                        selSkor.className = 'px-3 py-2 text-center text-slate-300';
                    }
                    return;
                }

                total += skor;
                terukur++;
                selesai++;

                if (selSkor) {
                    selSkor.textContent = skor;
                    selSkor.className = 'px-3 py-2 text-center font-bold border rounded ' + WARNA_SKOR[skor];
                }
            });

            // Lengkap = semua parameter terisi ATAU ditandai tidak diukur.
            var lengkap = selesai === PARAMETER_EWS.length;

            // Total SELALU ditampilkan: jumlah berjalan dari parameter yang
            // sudah dinilai. Sebelumnya total disembunyikan sampai 7 parameter
            // lengkap, sehingga angka tidak pernah muncul bagi pengguna yang
            // baru mengisi sebagian.
            var outTotal = document.getElementById('ews_total');
            if (outTotal) {
                outTotal.textContent = String(total);
                outTotal.className = 'px-3 py-2.5 text-center text-lg font-bold ' +
                    (lengkap ? 'text-slate-800' : 'text-amber-600');
            }

            var outKategori = document.getElementById('ews_kategori');
            if (outKategori) {
                outKategori.className = 'inline-block border rounded px-2 py-0.5 text-[11px] font-semibold';
                if (lengkap) {
                    var kategori = total >= 7 ? 'Tinggi' : (total >= 4 ? 'Sedang' : 'Rendah');
                    outKategori.textContent = 'Risiko ' + kategori;
                    outKategori.className += ' ' + BADGE_KATEGORI[kategori];
                } else {
                    // Kategori hanya bermakna bila semua parameter terisi atau
                    // ditandai tidak diukur; skor parsial bisa terlalu rendah.
                    outKategori.textContent = 'Belum lengkap';
                    outKategori.className += ' bg-slate-50 text-slate-400 border-slate-200';
                }
            }

            var outStatus = document.getElementById('ews_status');
            if (outStatus) {
                var teksStatus = lengkap
                    ? 'Lengkap'
                    : 'Belum lengkap (' + selesai + '/' + PARAMETER_EWS.length + ')';
                if (lengkap && jumlahNa > 0) {
                    teksStatus += ' · ' + terukur + ' terukur, ' + jumlahNa + ' tidak diukur';
                }
                outStatus.textContent = teksStatus;
                outStatus.className = lengkap
                    ? 'text-xs text-right text-emerald-600'
                    : 'text-xs text-right text-slate-500';
            }

            var outNaInfo = document.getElementById('ews_na_info');
            if (outNaInfo) {
                outNaInfo.textContent = jumlahNa > 0
                    ? jumlahNa + ' parameter tidak diukur'
                    : '';
            }

            // Sebutkan persis parameter mana yang belum terisi, supaya petugas
            // tahu apa yang perlu diisi atau dicentang "Tidak Diukur".
            var outPeringatan = document.getElementById('ews_peringatan');
            if (outPeringatan) {
                if (lengkap) {
                    outPeringatan.className = 'px-3 py-2.5 rounded-lg border text-xs hidden';
                    outPeringatan.textContent = '';
                } else {
                    outPeringatan.className = 'px-3 py-2.5 rounded-lg border text-xs bg-amber-50 border-amber-200 text-amber-800';
                    outPeringatan.textContent = 'Parameter belum diisi: ' + kurang.join(', ') +
                        '. Isi nilainya, atau centang "Tidak Diukur" bila tidak bisa diukur.';
                }
            }
        }

        // Input EWS adalah input tanda vital/kesadaran itu sendiri, jadi cukup
        // ikat event ke keduanya.
        PARAMETER_EWS.forEach(function (parameter) {
            var el = document.getElementById(parameter);
            if (el) {
                el.addEventListener('input', hitungEws);
                el.addEventListener('change', hitungEws);
            }

            var cb = document.querySelector('input.ews-na[value="' + parameter + '"]');
            if (cb) {
                cb.addEventListener('change', function () {
                    // Mengosongkan nilai vital saat ditandai tidak diukur,
                    // supaya angka sisa tidak ikut diskor DAN tidak tersimpan
                    // seolah-olah pernah diukur.
                    if (cb.checked && el) el.value = '';

                    hitungEws();
                });
            }
        });
        hitungEws();
    });
    </script>

@endsection()