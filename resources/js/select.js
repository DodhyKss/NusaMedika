import $ from 'jquery';
import select2 from 'select2';

// Pasang plugin Select2 ke instance jQuery yang sama, lalu ekspos sebagai global
select2(window, $);
window.$ = window.jQuery = $;

$(function () {
    if (typeof $.fn.select2 !== 'function') {
        console.error('[select2] Plugin Select2 tidak termuat — periksa bundle.');
        return;
    }

    // Inisialisasi aman: jika elemen sudah punya instance Select2, hancurkan dulu.
    // Select2 mengabaikan opsi baru (mis. konfigurasi ajax) bila elemen sudah ter-init,
    // sehingga pencarian pasien bisa terbuka tanpa memanggil API.
    function pasangSelect2($el, opts) {
        if (!$el.length) return;
        if ($el.data('select2')) {
            console.warn('[select2] init ulang: #' + ($el.attr('id') || $el.attr('name') || 'elemen') + ' — instance lama dihancurkan.');
            $el.select2('destroy');
        }
        $el.select2(opts);
    }

    // Select2 pencarian pasien (No. MR / NIK / Nama)
    // METODE: data dimuat dari API sekali via $.getJSON lalu dijadikan <option> asli,
    // dan Select2 dijalankan STATIS + matcher (TANPA modul ajax Select2).
    // Dengan begitu dropdown pasti berisi data saat dibuka dan ketik memfilter di klien.
    $('.select2-pasien').each(function () {
        var $el = $(this);
        var url = $el.data('url');
        console.log('[select2] pasien #' + ($el.attr('id') || '?') + ' -> muat dari: ' + url);

        $.getJSON(url, { limit: 1000 }, function (res) {
            var results = (res && res.results) || [];
            results.forEach(function (p) {
                if (p && p.id != null && !$el.find('option[value="' + p.id + '"]').length) {
                    var opt = new Option(p.text, p.id);
                    if (p.extra) {
                        $(opt).attr('data-extra', JSON.stringify(p.extra));
                    }
                    $el.append(opt);
                }
            });
            pasangSelect2($el, {
                placeholder: $el.data('placeholder') || 'Ketik No MR / Nama...',
                allowClear: true,
                width: '100%',
                matcher: function (params, data) {
                    if (!params.term) {
                        return data;
                    }
                    var term = params.term.toLowerCase();
                    var text = String(data.text || '').toLowerCase();
                    return text.indexOf(term) !== -1 ? data : null;
                }
            });

            // Handle card update
            $el.on('change', function () {
                var selectedOpt = $(this).find(':selected');
                var cardId = '#pasien-card-' + $(this).attr('id');
                var $card = $(cardId);
                
                if (!$card.length) return;

                var extraStr = selectedOpt.attr('data-extra');
                if (extraStr) {
                    try {
                        var extra = JSON.parse(extraStr);
                        $card.find('.p-ktp').text(extra.ktp || '-');
                        $card.find('.p-kelamin').text(extra.jenis_kelamin || '-');
                        $card.find('.p-ttl').text((extra.tempat_lahir || '-') + ' / ' + (extra.tgl_lahir || '-'));
                        $card.find('.p-nohp').text(extra.no_hp || '-');
                        $card.find('.p-goldar').text(extra.gol_darah || '-');
                        $card.find('.p-agama').text(extra.agama || '-');
                        $card.find('.p-kawin').text(extra.status_perkawinan || '-');
                        $card.find('.p-kerja').text(extra.pekerjaan || '-');
                        $card.find('.p-ibu').text(extra.nama_ibu_kandung || '-');
                        $card.find('.p-alamat').text(extra.alamat || '-');
                        $card.removeClass('hidden');
                    } catch (e) {
                        $card.addClass('hidden');
                    }
                } else {
                    $card.addClass('hidden');
                }
            });
        });
    });

    // Select2 pencarian ICD (Statis + Matcher) mengikuti rule pasien
    $('.select2-icd').each(function () {
        var $el = $(this);
        var url = $el.data('url');
        console.log('[select2] icd #' + ($el.attr('id') || '?') + ' -> muat dari: ' + url);
        
        $.getJSON(url, { limit: 1000 }, function (res) {
            var results = (res && res.results) || [];
            
            if (!$el.find('option[value=""]').length) {
                $el.append(new Option('', ''));
            }

            results.forEach(function (p) {
                if (p && p.id != null && !$el.find('option[value="' + p.id + '"]').length) {
                    $el.append(new Option(p.text, p.id));
                }
            });

            $el.val(null);

            pasangSelect2($el, {
                placeholder: $el.data('placeholder') || 'Ketik Kode / Nama Diagnosa...',
                allowClear: true,
                width: '100%',
                matcher: function (params, data) {
                    if (!params.term) {
                        return data;
                    }
                    var term = params.term.toLowerCase();
                    var text = String(data.text || '').toLowerCase();
                    return text.indexOf(term) !== -1 ? data : null;
                }
            });
        });
    });
    // Select2 untuk select statis (mis. pilih Bagian pada form pegawai / Poliklinik / Ruang Perawatan / Dokter)
    $('.select2, .select2-poliklinik, .select2-ruangan, .select2-dokter').not('.select2-pasien').each(function () {
        var placeholder = $(this).find('option[value=""]').first().text() || 'Pilih...';
        pasangSelect2($(this), {
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

                pasangSelect2($s, {
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
});
