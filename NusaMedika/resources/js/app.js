import './select';

// =============================================================================
// RESPONSIF MOBILE
// =============================================================================

/**
 * Bungkus setiap tabel dengan container yang bisa digeser horizontal.
 *
 * Tabel di aplikasi ini banyak yang lebar (min-width 1000px+). Tanpa wrapper
 * scroll, tabel terpotong di layar HP. Yang sudah punya wrapper sendiri
 * (overflow-x-auto) dilewati.
 */
function bungkusTabel() {
    document.querySelectorAll('table').forEach((table) => {
        const parent = table.parentElement;

        if (!parent || parent.classList.contains('nusa-table-scroll')) {
            return;
        }

        const overflowX = window.getComputedStyle(parent).overflowX;

        if (['auto', 'scroll'].includes(overflowX)) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'nusa-table-scroll drag-scroll';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

/**
 * Drag to scroll global handler.
 * Tambahkan class 'drag-scroll' ke elemen yang ingin bisa digeser.
 */
function initDragScroll() {
    const sliders = document.querySelectorAll('.drag-scroll');

    sliders.forEach(slider => {
        if (slider.dataset.dragScrollReady) {
            return;
        }

        slider.dataset.dragScrollReady = '1';

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
            slider.classList.add('cursor-grab');
            slider.classList.remove('cursor-grabbing');
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.add('cursor-grab');
            slider.classList.remove('cursor-grabbing');
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.5; // Scroll speed multiplier
            slider.scrollLeft = scrollLeft - walk;
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    bungkusTabel();
    initDragScroll();
});
