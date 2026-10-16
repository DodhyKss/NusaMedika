@extends('layouts.iframe')

@section('content')
    @php
        $dataTersimpan = (array) ($pengisian->data ?? []);
        $printUrl = $pengisian_id
            ? route('emr.katalog_form_blanko.print', ['form_blanko' => $blanko->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id, 'pengisian_id' => $pengisian_id])
            : '';
        $subTitle = $blanko->deskripsi ?: 'Isi formulir sesuai kebutuhan pasien.';
    @endphp

    <x-emr-split-layout
        :titleForm="$blanko->nama_blanko"
        titleRiwayat="Riwayat {{ $blanko->nama_blanko }}"
        :subtitleForm="$subTitle"
        :historyGrouped="$historyGrouped"
        :routeName="null"
        :routeUrl="url('emr/katalog-form-blanko/'.$blanko->form_blanko_id)"
        :registrasiDetailId="$registrasi_detail_id"
        :formAction="$formAction"
        :isEdit="$isEdit"
        :deleteAction="$deleteAction"
        :emrId="$pengisian_id"
        :isView="$isView"
        :printUrl="$printUrl"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            @include('moduls.EMR.KatalogFormBlanko.partials.riwayat')
        </x-slot>

        <fieldset class="space-y-4" {{ $isView ? 'disabled' : '' }}>
            @if ($blanko->tampilkan_identitas_pasien)
                <x-informasi-pasien />
            @endif

            {{-- Baris identitas field sistem (KONSEP §4.2) — bukan field tersimpan --}}
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-slate-600">
                <span><span class="font-semibold text-slate-700">Tanggal:</span> {{ now()->format('d/m/Y H:i') }}</span>
                <span><span class="font-semibold text-slate-700">Petugas:</span> {{ auth()->user()->nama_pegawai ?? '-' }}</span>
                <span><span class="font-semibold text-slate-700">Form:</span> {{ $blanko->nama_blanko }} (v{{ $blanko->versi }})</span>
            </div>

            @foreach ($fields->groupBy('grup') as $grup => $fieldsGrup)
                <x-emr-accordion :title="$grup ?: 'Detail'" :id="'grp-blanko-'.$loop->index" :isOpen="true">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        @foreach ($fieldsGrup as $field)
                            @include('moduls.EMR.KatalogFormBlanko.partials.field', ['field' => $field])
                        @endforeach
                    </div>
                </x-emr-accordion>
            @endforeach
        </fieldset>
    </x-emr-split-layout>

    <div id="cek129Isi" data-penanda="katalog-form-blanko-isi"></div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function nilaiKelompok(kode) {
            var els = document.querySelectorAll('[name="data[' + kode + ']"]');
            if (!els.length) return null;
            var first = els[0];
            if (first.type === 'radio') {
                for (var i = 0; i < els.length; i++) { if (els[i].checked) return els[i].value; }
                return null;
            }
            if (first.type === 'checkbox') {
                var vals = [];
                els.forEach(function (el) { if (el.checked) vals.push(el.value); });
                return vals.join(',');
            }
            if (first.type === 'hidden') {
                for (var j = 0; j < els.length; j++) {
                    if (els[j].type === 'checkbox') return els[j].checked ? els[j].value : first.value;
                }
                return first.value;
            }
            return first.value;
        }

        // Tampil/sembunyi field bersyarat (KONSEP §4.1). WAJIB lepas/pasang
        // class hidden + set style.display (gotcha Tailwind v4), dan disable
        // input tersembunyi supaya nilainya T ikut terkirim saat submit.
        function sinkronConditional() {
            document.querySelectorAll('[data-depends-on]').forEach(function (wrap) {
                var sumber = nilaiKelompok(wrap.dataset.dependsOn);
                var cocok = sumber !== null && sumber === String(wrap.dataset.dependsValue);
                wrap.classList.toggle('hidden', !cocok);
                wrap.style.display = cocok ? '' : 'none';
                wrap.querySelectorAll('input, select, textarea').forEach(function (el) {
                    el.disabled = !cocok;
                });
            });
        }
        document.addEventListener('change', sinkronConditional);
        sinkronConditional();

        // Tanda tangan: kanvas → data URL PNG ke input tersembunyi.
        document.querySelectorAll('canvas[id^="cv_"]').forEach(function (cv) {
            var kode = cv.id.slice(3);
            var input = document.getElementById('fld_' + kode);
            if (!input) return;
            var ctx = cv.getContext('2d');
            function bersihkan() {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, cv.width, cv.height);
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#1e293b';
            }
            bersihkan();
            if (input.value) {
                var img = new Image();
                img.onload = function () { ctx.drawImage(img, 0, 0, cv.width, cv.height); };
                img.src = input.value;
            }
            var menggambar = false;
            function posisi(e) {
                var r = cv.getBoundingClientRect();
                var p = e.touches ? e.touches[0] : e;
                return {
                    x: (p.clientX - r.left) * (cv.width / r.width),
                    y: (p.clientY - r.top) * (cv.height / r.height)
                };
            }
            cv.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                menggambar = true;
                var pt = posisi(e);
                ctx.beginPath();
                ctx.moveTo(pt.x, pt.y);
                ctx.lineTo(pt.x + 0.1, pt.y);
                ctx.stroke();
            });
            cv.addEventListener('pointermove', function (e) {
                if (!menggambar) return;
                e.preventDefault();
                var pt = posisi(e);
                ctx.lineTo(pt.x, pt.y);
                ctx.stroke();
            });
            window.addEventListener('pointerup', function () {
                if (!menggambar) return;
                menggambar = false;
                input.value = cv.toDataURL('image/png');
            });
        });
        document.querySelectorAll('[data-hapus-ttd]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var cv = document.getElementById(btn.dataset.hapusTtd);
                if (!cv) return;
                var input = document.getElementById('fld_' + cv.id.slice(3));
                var ctx = cv.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, cv.width, cv.height);
                if (input) input.value = '';
            });
        });
    });
</script>
@endpush
@endsection
