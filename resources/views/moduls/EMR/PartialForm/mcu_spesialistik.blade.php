{{--
    Partial reusable untuk form MCU spesialis (74–81, 83).

    Diberi parameter (bukan @props, dipanggil dengan @include):
      $judulPanel  -> judul panel pemeriksaan
      $organ       -> daftar ['kunci' => ..., 'label' => ...]
      $emr_data    -> EmrDataWrapper (HANYA mengimplementasikan ArrayAccess —
                      WAJIB pakai $emr_data['x'], bukan {{ $emr_data }})
      $isView      -> mode lihat (fieldset disabled di level form)

    Pola "Tidak Ada / Ada": kolom uraian hanya aktif bila radio "Ada" dipilih.
    Show/hide memakai `style.display` (BUKAN class `hidden`) — di Tailwind v4
    `.hidden` tercetak sebelum `.inline-flex` sehingga utility display lain
    menang dan elemen tetap terlihat. `name` juga dilepas saat disembunyikan
    supaya field kosong tidak terkirim.
--}}

<div class="border border-slate-200 rounded-sm shadow-sm">
    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
        <span class="text-cyan-600 font-medium text-[15px]">{{ $judulPanel }}</span>
    </div>

    <div class="p-5 bg-white text-sm space-y-3">
        @foreach ($organ as $o)
            @php
                $kunci = $o['kunci'];
                $ada = (string) ($emr_data[$kunci . '_ada'] ?? '0') === '1';
                $namaUraian = 'mcu_uraian_' . $kunci;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-start border rounded-sm px-3 py-2">
                <div class="md:col-span-1">
                    <span class="text-sm font-medium text-slate-700">{{ $o['label'] }} <span class="text-red-500">*</span></span>
                </div>

                <div class="md:col-span-1 flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="{{ $kunci }}_ada" value="0" class="form-radio"
                            {{ $ada ? '' : 'checked' }}>
                        <span class="text-sm">Tidak Ada</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="{{ $kunci }}_ada" value="1" class="form-radio"
                            {{ $ada ? 'checked' : '' }}>
                        <span class="text-sm">Ada</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <textarea name="{{ $kunci }}" rows="2"
                        class="w-full border rounded-sm px-3 py-2 text-sm"
                        style="{{ $ada ? '' : 'display:none' }}"
                        data-uraian="{{ $kunci }}"
                        placeholder="Uraian temuan">{{ old($kunci, $emr_data[$kunci] ?? '') }}</textarea>
                    @error($kunci) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        // Show/hide kolom uraian mengikuti radio "Ada". Memakai style.display
        // (bukan class hidden) — lihat catatan di header partial.
        document.querySelectorAll('[data-uraian]').forEach(function (ta) {
            const kunci = ta.dataset.uraian;
            const radios = document.querySelectorAll('[name="' + kunci + '_ada"]');

            function sinkron() {
                const tercentang = document.querySelector('[name="' + kunci + '_ada"]:checked');
                const ada = tercentang && tercentang.value === '1';
                ta.style.display = ada ? '' : 'none';
                // Lepas `name` saat disembunyikan supaya field kosong tidak terkirim.
                if (ada) { ta.setAttribute('name', kunci); } else { ta.removeAttribute('name'); }
            }

            radios.forEach(function (r) { r.addEventListener('change', sinkron); });
            sinkron();
        });
    })();
</script>