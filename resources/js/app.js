$(document).ready(function () {
    $('.select2-pasien').each(function () {
        var searchUrl = $(this).data('url') || '/api/pasien/search';
        
        $(this).select2({
            placeholder: 'Ketik No MR / Nama...',
            allowClear: true,
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });
    });
    // Select2 untuk select statis (mis. pilih Bagian pada form pegawai)
    $('.select2').each(function () {
        var placeholder = $(this).find('option[value=""]').first().text() || 'Pilih...';
        $(this).select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%'
        });
    });

    // Cascade wilayah: provinsi -> kabupaten -> kecamatan -> kelurahan
    function initWilayahCascade($root) {
        $root.each(function () {
            var $c = $(this);
            var sel = {
                provinsi: $c.find('select[data-wilayah="provinsi"]'),
                kabupaten: $c.find('select[data-wilayah="kabupaten"]'),
                kecamatan: $c.find('select[data-wilayah="kecamatan"]'),
                kelurahan: $c.find('select[data-wilayah="kelurahan"]')
            };
            var parentKey = { kabupaten: 'provinsi_id', kecamatan: 'kabupaten_id', kelurahan: 'kecamatan_id' };
            var parentSel = { kabupaten: 'provinsi', kecamatan: 'kabupaten', kelurahan: 'kecamatan' };
            var prefill = $c.data('prefill') || null;

            function muatWilayah(level) {
                var $s = sel[level];
                var url = $s.data('url');
                if (!url) return;
                var params = {};
                if (parentSel[level] && sel[parentSel[level]].val()) {
                    params[parentKey[level]] = sel[parentSel[level]].val();
                }
                $s.empty();
                $s.append($('<option>').val('').text($s.data('placeholder') || '-- Pilih --'));
                $.getJSON(url, params, function (data) {
                    $.each(data.results, function (_, r) {
                        $s.append($('<option>').val(r.id).text(r.text));
                    });
                    if (prefill && prefill[level + '_id']) {
                        $s.val(prefill[level + '_id']).trigger('change');
                    }
                });
            }

            $.each(['provinsi', 'kabupaten', 'kecamatan'], function (_, level) {
                sel[level].on('change', function () {
                    var children = level === 'provinsi'
                        ? ['kabupaten', 'kecamatan', 'kelurahan']
                        : level === 'kabupaten' ? ['kecamatan', 'kelurahan'] : ['kelurahan'];
                    $.each(children, function (_, child) {
                        sel[child].empty().append($('<option>').val('').text(sel[child].data('placeholder') || '-- Pilih --'));
                    });
                    muatWilayah(children[0]);
                });
            });

            muatWilayah('provinsi');
        });
    }

    $('.wilayah-cascade').each(function () {
        initWilayahCascade($(this));
    });
    // Sinkronkan Select2 saat form di-reset
    $('form').on('reset', function () {
        var $form = $(this);
        setTimeout(function () {
            $form.find('.select2').val(null).trigger('change');
        }, 0);
    });
    // Drag to scroll global handler
    // Tambahkan class 'drag-scroll' ke elemen yang ingin bisa digeser
    const sliders = document.querySelectorAll('.drag-scroll');
    
    sliders.forEach(slider => {
        let isDown = false;
        let startX;
        let scrollLeft;

        // Apply initial grab cursor
        slider.classList.add('cursor-grab');

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('cursor-grabbing');
            slider.classList.remove('cursor-grab');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('cursor-grabbing');
            slider.classList.add('cursor-grab');
        });
        
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('cursor-grabbing');
            slider.classList.add('cursor-grab');
        });
        
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.5; // Scroll speed multiplier
            slider.scrollLeft = scrollLeft - walk;
        });
    });
});