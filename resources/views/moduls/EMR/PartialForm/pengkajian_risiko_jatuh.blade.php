@php
    /**
     * Partial: Pengkajian Risiko Jatuh.
     *
     * Instrumen dipilih otomatis dari usia pasien (RisikoJatuhHelper), tetapi
     * perawat boleh menggantinya secara manual. Skor dan tingkat risiko
     * dihitung ulang oleh JS supaya responsif, lalu disimpan lewat input
     * tersembunyi: risiko_jatuh_instrumen, risiko_jatuh_skor,
     * risiko_jatuh_label, risiko_jatuh_intervensi, risiko_jatuh_ringkasan.
     * Nilai FINAL yang disimpan tetap hasil hitungan server (lihat
     * RisikoJatuhHelper::hitung() di controller).
     */
    use App\Helpers\RisikoJatuhHelper;

    $usia = $usia ?? null;
    $semuaInstrumen = RisikoJatuhHelper::semuaInstrumen();
    $instrumenAktif = RisikoJatuhHelper::normalisasiKode($emr_data['risiko_jatuh_instrumen'] ?? '')
        ?: RisikoJatuhHelper::instrumenUntukUsia($usia);
    $defAktif = RisikoJatuhHelper::definisi($instrumenAktif) ?? RisikoJatuhHelper::morse();
    $norma = RisikoJatuhHelper::normaTug($usia);
@endphp

<x-emr-accordion id="acc-risiko-jatuh" title="F. Pengkajian Risiko Jatuh" bgClass="bg-yellow-50" :isOpen="true">
    <div class="text-sm space-y-4">

        {{-- Keterapan usia & pilihan instrumen --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end bg-slate-50 border border-slate-200 rounded-lg p-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Usia Pasien</label>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-cyan-700 leading-none">{{ $usia !== null ? $usia : '-' }}</span>
                    <span class="text-xs text-slate-500">tahun</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Instrumen default dipilih otomatis sesuai usia.</p>
            </div>
            <div>
                <label for="risiko_jatuh_instrumen" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Instrumen Penilaian</label>
                <select id="risiko_jatuh_instrumen_select" class="w-full text-sm border border-slate-300 rounded px-2.5 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    @foreach ($semuaInstrumen as $def)
                        <option value="{{ $def['kode'] }}" @selected($def['kode'] === $instrumenAktif)>
                            {{ $def['nama'] }} ({{ $def['rentang_usia'] }})
                        </option>
                    @endforeach
                </select>
                <p id="risiko_jatuh_sumber" class="text-[11px] text-slate-400 mt-1">{{ $defAktif['sumber'] }}</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Norma TUG</label>
                <div class="text-xs text-slate-600 leading-relaxed">
                    @if ($norma['nilai'])
                        Kelompok usia <strong>{{ $norma['label'] }}</strong><br>
                        Rata-rata {{ number_format((float) $norma['rata'], 1, ',', '.') }} detik,
                        batas atas <strong>{{ number_format((float) $norma['batas'], 1, ',', '.') }}</strong> detik.
                    @else
                        {{ $norma['label'] }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Wadah item per instrumen (diisi server) --}}
        <div id="risiko_jatuh_items">
            @foreach ($semuaInstrumen as $def)
                @include('moduls.EMR.PartialForm._risiko_jatuh_instrumen', ['def' => $def])
            @endforeach
        </div>

        {{-- Hasil penilaian --}}
        <div id="risiko_jatuh_hasil" class="border border-slate-300 rounded-sm overflow-hidden bg-white shadow-sm">
            <div class="bg-slate-100 border-b border-slate-200 px-3 py-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Hasil Penilaian Risiko Jatuh</span>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-center">
                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Skor</div>
                    <div id="risiko_jatuh_skor_view" class="text-4xl font-bold text-cyan-700 leading-tight">-</div>
                </div>
                <div class="text-center md:text-left">
                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Tingkat Risiko</div>
                    <div id="risiko_jatuh_label_view">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Belum Dinilai</span>
                    </div>
                    <div id="risiko_jatuh_skala_view" class="text-[11px] text-slate-400 mt-1"></div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Intervensi</div>
                    <div id="risiko_jatuh_intervensi_view" class="text-xs text-slate-600 mt-1">-</div>
                </div>
            </div>
            <div class="px-4 pb-4">
                <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Tabel Kriteria &amp; Intervensi</div>
                <div class="overflow-x-auto">
                    <table id="risiko_jatuh_tabel_kategori" class="w-full text-xs text-left border-collapse text-slate-700" style="min-width: 420px;">
                        <tbody class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Input tersembunyi yang disimpan ke emr_detail --}}
        <input type="hidden" name="risiko_jatuh_instrumen" id="risiko_jatuh_instrumen" value="{{ $emr_data['risiko_jatuh_instrumen'] ?? $instrumenAktif }}">
        <input type="hidden" name="risiko_jatuh_skor" id="risiko_jatuh_skor" value="{{ $emr_data['risiko_jatuh_skor'] ?? '' }}">
        <input type="hidden" name="risiko_jatuh_label" id="risiko_jatuh_label" value="{{ $emr_data['risiko_jatuh_label'] ?? '' }}">
        <input type="hidden" name="risiko_jatuh_intervensi" id="risiko_jatuh_intervensi" value="{{ $emr_data['risiko_jatuh_intervensi'] ?? '' }}">
        <input type="hidden" name="risiko_jatuh_ringkasan" id="risiko_jatuh_ringkasan" value="{{ $emr_data['risiko_jatuh_ringkasan'] ?? '' }}">
    </div>
</x-emr-accordion>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.getElementById('risiko_jatuh_items');
    if (!wrap) return;

    var INST = @json($semuaInstrumen);
    var USIA = @json($usia);
    var NORMA = @json(RisikoJatuhHelper::normaTug($usia));
    var RISIKO_AWAL = {!! json_encode([
        'skor' => $emr_data['risiko_jatuh_skor'] ?? '',
        'label' => $emr_data['risiko_jatuh_label'] ?? '',
        'intervensi' => $emr_data['risiko_jatuh_intervensi'] ?? '',
    ]) !!};

    var elInstrumen = document.getElementById('risiko_jatuh_instrumen_select');
    var elSumber = document.getElementById('risiko_jatuh_sumber');
    var elSkorView = document.getElementById('risiko_jatuh_skor_view');
    var elLabelView = document.getElementById('risiko_jatuh_label_view');
    var elSkalaView = document.getElementById('risiko_jatuh_skala_view');
    var elIntervensiView = document.getElementById('risiko_jatuh_intervensi_view');
    var elTabel = document.getElementById('risiko_jatuh_tabel_kategori').querySelector('tbody');

    var WARNA = {
        slate: ['bg-slate-100', 'text-slate-600'],
        emerald: ['bg-emerald-100', 'text-emerald-700'],
        amber: ['bg-amber-100', 'text-amber-700'],
        red: ['bg-red-100', 'text-red-700']
    };

    function defInstrumen(kode) {
        for (var i = 0; i < INST.length; i++) {
            if (INST[i].kode === kode) return INST[i];
        }
        return INST[1];
    }

    // Skor dari satu item: HDS (bobot_ya), MFS/Sydney (opsi), atau angka (TUG).
    function skorItem(item, nilai) {
        if (nilai === '' || nilai === null || nilai === undefined) return 0;
        if (item.bobot_ya !== undefined) return nilai === 'Ya' ? Number(item.bobot_ya) : 0;
        if (item.opsi) {
            var v = item.opsi[nilai];
            return v === undefined ? 0 : Number(v);
        }
        var n = parseFloat(String(nilai).replace(',', '.'));
        return isNaN(n) ? 0 : n;
    }

    function hitungSydney(def) {
        var ts = 0, ms = 0;
        def.item.forEach(function (it) {
            var v = skorItem(it, nilaiItem(it));
            if (it.var.indexOf('syd_ts_') === 0) ts += v;
            if (it.var.indexOf('syd_ms_') === 0) ms += v;
        });
        var jumlah = ts + ms;
        return jumlah <= 2 ? 0 : (jumlah <= 6 ? 7 : jumlah);
    }

    function hitungTug(detik) {
        if (detik >= 30) return 2;
        if (!NORMA || NORMA.batas === null) return 1;
        return detik <= NORMA.batas ? 0 : 1;
    }

    function kategoriUntuk(def, total) {
        if (def.kode === 'TUG') return hitungTug(total);
        for (var i = 0; i < def.kategori.length; i++) {
            if (total <= def.kategori[i].maks) return i;
        }
        return def.kategori.length - 1;
    }

    function badge(warna, teks) {
        var c = WARNA[warna] || WARNA.slate;
        return '<span class="inline-flex px-3 py-1 rounded-full text-xs font-bold ' + c[0] + ' ' + c[1] + '">' + teks + '</span>';
    }

    function hitungSekarang() {
        var kode = elInstrumen.value;
        var def = defInstrumen(kode);
        var total = 0, terisi = 0;

        def.item.forEach(function (item) {
            var nilai = nilaiItem(item);
            if (nilai === '' || nilai === null) return;
            terisi++;
            total += skorItem(item, nilai);
        });

        if (kode === 'SYDNEY') total = hitungSydney(def);
        if (kode === 'HDS') total = Math.min(def.skor_maks, def.skor_dasar + total);

        var skorTampil = kode === 'TUG' ? Math.round(total * 10) / 10 : Math.round(total);

        // Tabel kriteria
        elTabel.innerHTML = def.kategori.map(function (c, i) {
            var krit = kode === 'TUG' ? normaKriteriaTug() : '';
            var risiko = '';
            if (i < def.kategori.length - 1) risiko = 'skor &le; ' + c.maks;
            else risiko = 'skor &gt; ' + def.kategori[i - 1].maks;
            return '<tr><td class="px-2 py-1.5 font-medium">' + c.label + '</td>'
                + '<td class="px-2 py-1.5">' + risiko + (krit ? ' <span class="text-slate-400">(' + krit + ')</span>' : '') + '</td>'
                + '<td class="px-2 py-1.5">' + c.intervensi + '</td></tr>';
        }).join('');

        if (!terisi) {
            elSkorView.textContent = '-';
            elLabelView.innerHTML = badge('slate', 'Belum Dinilai');
            elSkalaView.textContent = '';
            elIntervensiView.textContent = '-';
            setHidden('risiko_jatuh_skor', '');
            setHidden('risiko_jatuh_label', '');
            setHidden('risiko_jatuh_intervensi', '');
            setHidden('risiko_jatuh_ringkasan', '');
            return;
        }

        var idx = kategoriUntuk(def, total);
        var kat = def.kategori[idx];

        elSkorView.textContent = skorTampil;
        elLabelView.innerHTML = badge(kat.warna, kat.label);
        elSkalaView.textContent = def.kategori.length + ' tingkat, skor minimal ' + def.skor_min
            + (def.skor_maks ? ' maksimal ' + def.skor_maks : '');
        elIntervensiView.textContent = kat.intervensi;

        setHidden('risiko_jatuh_instrumen', kode);
        setHidden('risiko_jatuh_skor', skorTampil);
        setHidden('risiko_jatuh_label', kat.label);
        setHidden('risiko_jatuh_intervensi', kat.intervensi);

        var ringkas = skorTampil + ' - ' + kat.label;
        if (kode === 'TUG' && NORMA && NORMA.nilai) {
            ringkas += ' (norma ' + NORMA.label + ': maksimal ' + NORMA.nilai + ' detik)';
        }
        setHidden('risiko_jatuh_ringkasan', ringkas);
    }

    function normaKriteriaTug() {
        if (!NORMA || NORMA.nilai === null) return 'tanpa norma khusus';
        return 'norma ' + NORMA.label + ' &le; ' + String(NORMA.batas).replace('.', ',') + ' dtk';
    }

    function setHidden(id, nilai) {
        var el = document.getElementById(id);
        if (el) el.value = nilai === null || nilai === undefined ? '' : nilai;
    }

    // Kumpulkan reference ke seluruh input item.
    //
    // PENTING: untuk item berobeksi (radio) kita menyimpan SELURUH grup, bukan
    // hanya radio pertama. Nilai radio group dibaca dari radio yang sedang
    // checked; kalau cuma radio pertama yang disimpan, memilih opsi kedua akan
    // terbaca sebagai kosong karena radio pertama ikut ter-uncheck.
    function kumpulkanRef() {
        INST.forEach(function (def) {
            var panel = document.getElementById('rj-def-' + def.kode);
            if (!panel) return;

            def.item.forEach(function (item) {
                var found = panel.querySelectorAll('[name="' + item.var + '"]');
                item.els = found.length ? Array.prototype.slice.call(found) : [];
            });
        });
    }

    // Nilai jawaban sebuah item. Radio group -> nilai radio yang checked.
    function nilaiItem(item) {
        if (!item.els || !item.els.length) return '';

        for (var i = 0; i < item.els.length; i++) {
            var el = item.els[i];
            if (el.type === 'radio') {
                if (el.checked) return el.value;
            } else {
                return el.value;
            }
        }

        return '';
    }

    function pasangListener() {
        INST.forEach(function (def) {
            var panel = document.getElementById('rj-def-' + def.kode);
            if (!panel) return;
            panel.querySelectorAll('input, select').forEach(function (el) {
                el.addEventListener('change', function () {
                    if (def.kode === elInstrumen.value) hitungSekarang();
                });
                el.addEventListener('input', function () {
                    if (def.kode === elInstrumen.value) hitungSekarang();
                });
            });
        });
    }

    // Hanya panel instrumen aktif yang tampil & submitting.
    function togglePanel() {
        var kode = elInstrumen.value;
        var def = defInstrumen(kode);
        INST.forEach(function (d) {
            var panel = document.getElementById('rj-def-' + d.kode);
            if (!panel) return;
            var aktif = d.kode === kode;
            panel.classList.toggle('hidden', !aktif);
            panel.querySelectorAll('input, select').forEach(function (el) {
                el.disabled = !aktif;
            });
        });
        elSumber.textContent = def.sumber;
        hitungSekarang();
    }

    elInstrumen.addEventListener('change', togglePanel);

    kumpulkanRef();
    pasangListener();
    togglePanel();

    // Tampilkan hasil lama saat membuka form untuk pertama kali (data lama tanpa skor).
    if (RISIKO_AWAL.skor !== '' && RISIKO_AWAL.label !== '') {
        elSkorView.textContent = RISIKO_AWAL.skor;
        elLabelView.innerHTML = badge('slate', RISIKO_AWAL.label);
        elIntervensiView.textContent = RISIKO_AWAL.intervensi || '-';
    }
});
</script>