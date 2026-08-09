$(document).ready(function () {
    $('.select2-pasien').each(function () {
        var searchUrl = $(this).data('url') || '/api/pasien/search';
        var field = $(this).data('field');
        var placeholder = $(this).data('placeholder') || 'Ketik No MR / Nama...';

        $(this).select2({
            placeholder: placeholder,
            allowClear: true,
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var data = {
                        q: params.term // search term
                    };
                    if (field) {
                        data.field = field;
                    }
                    return data;
                },
                processResults: function (data) {
                    var res = typeof data === 'string' ? JSON.parse(data) : data;
                    return {
                        results: res.results || (Array.isArray(res) ? res : [])
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

    // Cascade wilayah: provinsi -> kabupaten -> kecamatan -> kelurahan (Select2 AJAX)
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
            var childrenMap = {
                provinsi: ['kabupaten', 'kecamatan', 'kelurahan'],
                kabupaten: ['kecamatan', 'kelurahan'],
                kecamatan: ['kelurahan'],
                kelurahan: []
            };
            var prefill = $c.data('prefill') || null;

            $.each(sel, function (level, $s) {
                // Option awal untuk menampilkan label nilai prefill (edit)
                if (prefill && prefill[level + '_id'] && prefill[level + '_nama']) {
                    $s.append($('<option>').val(prefill[level + '_id']).text(prefill[level + '_nama']));
                }

                $s.select2({
                    placeholder: $s.data('placeholder') || '-- Pilih --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: $s.data('url'),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var query = {};
                            var parent = parentSel[level];
                            if (parent && sel[parent].val()) {
                                query[parentKey[level]] = sel[parent].val();
                            }
                            if (params.term) {
                                query.q = params.term;
                            }
                            return query;
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        },
                        cache: true
                    }
                });

                // Saat nilai berubah, kosongkan semua turunan
                $s.on('change', function () {
                    $.each(childrenMap[level], function (_, child) {
                        sel[child].val(null).trigger('change');
                    });
                });
            });

            // Terapkan nilai prefill setelah semua select2 diinisialisasi
            $.each(sel, function (level, $s) {
                if (prefill && prefill[level + '_id']) {
                    $s.val(prefill[level + '_id']).trigger('change');
                }
            });
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
            $form.find('select[data-wilayah]').val(null).trigger('change');
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