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