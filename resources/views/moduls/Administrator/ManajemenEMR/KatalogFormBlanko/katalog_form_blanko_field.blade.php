@php
    // Kartu field: pakai input `old('fields')` bila validasi gagal (edit
    // pengguna tidak hilang), selain itu normalisasi dari model DB.
    $kartuKosong = [
        'form_blanko_field_id' => null,
        'kode_field' => '', 'label' => '', 'tipe_kontrol' => 'text', 'grup' => '',
        'wajib' => false, 'lebar_kolom' => 6,
        'depends_on_field' => '', 'depends_on_value' => '',
        'opsi_text' => '', 'min' => '', 'max' => '', 'maxLength' => '', 'pattern' => '',
        'placeholder' => '', 'rows' => '', 'source' => '', 'multiple' => false, 'default' => '',
    ];

    if (is_array(old('fields'))) {
        $kartus = array_values(old('fields'));
    } else {
        $normNum = function ($x) {
            if ($x === null || $x === '') {
                return '';
            }

            return (is_numeric($x) && (float) (int) $x === (float) $x) ? (string) (int) $x : (string) $x;
        };
        $kartus = $fields->map(function ($m) use ($normNum) {
            $v = (array) ($m->validasi ?? []);
            $s = (array) ($m->setelan ?? []);
            $lines = [];
            foreach ((array) ($m->opsi ?? []) as $op) {
                $val = (string) ($op['value'] ?? '');
                $lab = (string) ($op['label'] ?? '');
                $lines[] = ($val === $lab) ? $val : $val.'|'.$lab;
            }

            return [
                'form_blanko_field_id' => $m->form_blanko_field_id,
                'kode_field' => $m->kode_field,
                'label' => $m->label,
                'tipe_kontrol' => $m->tipe_kontrol,
                'grup' => $m->grup ?? '',
                'wajib' => (bool) $m->wajib,
                'lebar_kolom' => $m->lebar_kolom,
                'depends_on_field' => $m->depends_on_field ?? '',
                'depends_on_value' => $m->depends_on_value ?? '',
                'opsi_text' => implode("\n", $lines),
                'min' => $normNum($v['min'] ?? ''),
                'max' => $normNum($v['max'] ?? ''),
                'maxLength' => $normNum($v['maxLength'] ?? ''),
                'pattern' => $v['pattern'] ?? '',
                'placeholder' => $s['placeholder'] ?? '',
                'rows' => $s['rows'] ?? '',
                'source' => $s['source'] ?? '',
                'multiple' => ! empty($s['multiple']),
                'default' => $s['default'] ?? '',
            ];
        })->values()->all();
    }
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Field Builder</h2>
            <p class="text-xs text-slate-500 mt-1">
                Urutan baris = urutan tampil (pakai tombol Naik/Turun). Opsi: per baris
                <code class="bg-slate-100 px-1 rounded">value</code> atau
                <code class="bg-slate-100 px-1 rounded">value|Label</code>.
                Kode field: snake_case, unik, dilarang memakai <code class="bg-slate-100 px-1 rounded">__pasien/__tanggal/__petugas</code>.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">Field: <strong id="field-count">{{ count($kartus) }}</strong>/120</span>
            <span id="field-limit" class="text-xs text-red-500 hidden">Batas 120 field tercapai.</span>
            <button type="button" id="field-tambah"
                    class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Field
            </button>
        </div>
    </div>

    <div id="field-list" class="space-y-4">
        @foreach ($kartus as $idx => $f)
            @include('moduls.Administrator.ManajemenEMR.KatalogFormBlanko._kartu_field', ['f' => $f, 'idx' => $idx])
        @endforeach
    </div>

    <template id="tpl-field">
        @include('moduls.Administrator.ManajemenEMR.KatalogFormBlanko._kartu_field', ['f' => $kartuKosong, 'idx' => null])
    </template>
</div>

@push('scripts')
<script>
(function () {
    var list = document.getElementById('field-list');
    var tpl = document.getElementById('tpl-field');
    var tambah = document.getElementById('field-tambah');
    if (!list || !tpl || !tambah) return;

    function renumber() {
        var n = 0;
        list.querySelectorAll('.kartu-field').forEach(function (card) {
            var i = n++;
            var urut = card.querySelector('.kartu-urut');
            if (urut) urut.textContent = (i + 1);
            card.querySelectorAll('[data-f]').forEach(function (el) {
                el.name = 'fields[' + i + '][' + el.dataset.f + ']';
            });
        });
        var cnt = document.getElementById('field-count');
        if (cnt) cnt.textContent = n;
        var lim = document.getElementById('field-limit');
        if (lim) lim.classList.toggle('hidden', n < 120);
        tambah.disabled = n >= 120;
        tambah.classList.toggle('opacity-50', n >= 120);
    }

    function syncTipe(card) {
        var sel = card.querySelector('[data-f="tipe_kontrol"]');
        if (!sel) return;
        var tipe = sel.value;
        var ops = card.querySelector('.blok-opsi');
        var src = card.querySelector('.blok-source');
        var rows = card.querySelector('.blok-rows');
        var showOps = ['select', 'radio', 'checkbox'].indexOf(tipe) !== -1;
        if (ops) { ops.classList.toggle('hidden', !showOps); ops.style.display = showOps ? '' : 'none'; }
        if (src) { var s = tipe === 'select2'; src.classList.toggle('hidden', !s); src.style.display = s ? '' : 'none'; }
        if (rows) { var r = tipe === 'textarea'; rows.classList.toggle('hidden', !r); rows.style.display = r ? '' : 'none'; }
    }

    list.addEventListener('change', function (e) {
        var card = e.target.closest('.kartu-field');
        if (card) syncTipe(card);
    });

    list.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-aksi]');
        if (!b) return;
        var card = b.closest('.kartu-field');
        if (!card) return;
        var aksi = b.dataset.aksi;
        if (aksi === 'hapus') {
            card.remove();
            renumber();
        } else if (aksi === 'naik') {
            var prev = card.previousElementSibling;
            if (prev) { list.insertBefore(card, prev); renumber(); }
        } else if (aksi === 'turun') {
            var next = card.nextElementSibling;
            if (next) { list.insertBefore(next, card); renumber(); }
        }
    });

    tambah.addEventListener('click', function () {
        if (list.querySelectorAll('.kartu-field').length >= 120) return;
        var node = tpl.content.firstElementChild.cloneNode(true);
        list.appendChild(node);
        renumber();
        syncTipe(node);
        var label = node.querySelector('[data-f="label"]');
        if (label) label.focus();
    });

    renumber();
    list.querySelectorAll('.kartu-field').forEach(syncTipe);
})();
</script>
@endpush
