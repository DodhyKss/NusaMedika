{{--
    Body Map Nyeri - gambar tubuh + penanda lokasi nyeri (canvas murni, tanpa
    EaselJS). Dipakai form 27 Penilaian Nyeri & form 37 Body Map Nyeri.

    Hasil disimpan sebagai data URL PNG di `emr_detail.gambar_lokasi_nyeri`
    (kolom TEXT 64 KB). Sebelum submit, kanvas dikompresi menjadi setengah ukuran
    supaya muat di bawah 60 KB.

    Props:
      value    -> data URL PNG yang tersimpan (string kosong bila belum ada)
      name     -> nama input di emr_detail (default `gambar_lokasi_nyeri`)
      idPrefix -> prefiks id agar tidak bentrok bila partial dipakai 2x seekali
--}}
@php
    $bodyMapName = $name ?? 'gambar_lokasi_nyeri';
    $bodyMapPrefix = $idPrefix ?? 'bodymap';
    $bodyMapValue = is_string($value ?? null) ? $value : '';
    $bodyMapCanvasId = $bodyMapPrefix.'-canvas';
    $bodyMapInputId = $bodyMapPrefix.'-input';
    $bodyMapInfoId = $bodyMapPrefix.'-info';
    $bodyMapBersihId = $bodyMapPrefix.'-bersih';
@endphp

<div class="p-4 border border-slate-200 rounded-lg" data-bodymap="{{ $bodyMapPrefix }}">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <span class="font-bold text-slate-800">Body Map Lokasi Nyeri</span>
        <button type="button" id="{{ $bodyMapBersihId }}"
                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50">
            Bersihkan Tanda
        </button>
    </div>

    <p class="text-xs text-slate-500 mb-2">
        Klik pada gambar tubuh untuk menandai lokasi nyeri. Gambar otomatis dikompresi
        dan disimpan bersama data EMR.
    </p>

    <div class="inline-block border border-slate-200 rounded bg-white p-1">
        <canvas id="{{ $bodyMapCanvasId }}" width="300" height="300"
                class="block cursor-crosshair" style="width: 300px; height: 300px;"></canvas>
    </div>

    <input type="hidden" id="{{ $bodyMapInputId }}" name="{{ $bodyMapName }}" value="{{ $bodyMapValue }}">

    <div id="{{ $bodyMapInfoId }}" class="mt-2 text-xs text-slate-500">
        Belum ada tanda lokasi.</div>

    @error($bodyMapName)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var canvas = document.getElementById(@json($bodyMapCanvasId));
        var input = document.getElementById(@json($bodyMapInputId));
        var info = document.getElementById(@json($bodyMapInfoId));
        var tombolBersih = document.getElementById(@json($bodyMapBersihId));
        if (!canvas || !input) return;

        var ctx = canvas.getContext('2d');
        var gambarDasar = null;
        var tanda = [];

        function gambarUlang() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            if (gambarDasar) {
                ctx.drawImage(gambarDasar, 0, 0, canvas.width, canvas.height);
            }
            for (var i = 0; i < tanda.length; i++) {
                ctx.beginPath();
                ctx.arc(tanda[i].x, tanda[i].y, 7, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(220, 38, 38, 0.85)';
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#ffffff';
                ctx.stroke();
            }
        }

        function syncInfo() {
            if (!info) return;
            info.textContent = tanda.length === 0
                ? 'Belum ada tanda lokasi.'
                : 'Jumlah tanda lokasi: ' + tanda.length + ' titik.';
        }

        function muatGambar(src) {
            var img = new Image();
            img.onload = function () {
                gambarDasar = img;
                gambarUlang();
            };
            img.onerror = function () {
                if (info) info.textContent = 'Gambar tubuh tidak dapat dimuat, tandai lokasi secara deskriptif.';
            };
            img.src = src;
        }

        // Tanda lama (bila form dibuka untuk edit) sudah tersimpan sebagai gambar,
        // jadi cukup digambar sebagai latar - tidak perlu parsing ulang.
        if (input.value && input.value.indexOf('data:image/png;base64,') === 0) {
            muatGambar(input.value);
        } else {
            muatGambar(@json(asset('img/nyeri/tubuh.png')));
        }

        canvas.addEventListener('mousedown', function (ev) {
            var kotak = canvas.getBoundingClientRect();
            var skalaX = canvas.width / (kotak.width || canvas.width);
            var skalaY = canvas.height / (kotak.height || canvas.height);
            tanda.push({
                x: Math.round((ev.clientX - kotak.left) * skalaX),
                y: Math.round((ev.clientY - kotak.top) * skalaY)
            });
            gambarUlang();
            syncInfo();
        });

        if (tombolBersih) {
            tombolBersih.addEventListener('click', function () {
                tanda = [];
                gambarUlang();
                syncInfo();
                input.value = '';
            });
        }

        // Kompres jadi setengah ukuran sebelum submit supaya muat di kolom TEXT 64 KB.
        var form = canvas.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                // Tanpa tanda baru: biarkan nilai lama (bila ada) tetap utuh.
                if (tanda.length === 0) return;
                var out = document.createElement('canvas');
                out.width = Math.max(1, Math.round(canvas.width / 2));
                out.height = Math.max(1, Math.round(canvas.height / 2));
                out.getContext('2d').drawImage(canvas, 0, 0, out.width, out.height);
                input.value = out.toDataURL('image/png');
            });
        }
    });
</script>