{{-- Component: Confirm Alert (SweetAlert-style, custom) --}}
{{-- Pemakaian:
     1. Form: <form method="POST" data-confirm-message="Pesan..." data-confirm-title="Judul" data-confirm-text="Ya, Lanjutkan" data-confirm-danger="true">
     2. JS:   nusaConfirm({ title, message, confirmText, cancelText, danger, onConfirm });
--}}

<div id="nusa-confirm" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="nusa-confirm-title">
    <div id="nusa-confirm-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div id="nusa-confirm-box" class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transition-all duration-200 ease-out scale-95 opacity-0 translate-y-2">
        <div id="nusa-confirm-icon" class="mx-auto w-14 h-14 rounded-full flex items-center justify-center bg-amber-100 text-amber-500 mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 id="nusa-confirm-title" class="text-base font-bold text-slate-800"></h3>
        <p id="nusa-confirm-message" class="text-sm text-slate-500 mt-2 leading-relaxed"></p>
        <div id="nusa-confirm-input-wrap" class="mt-4 text-left hidden">
            <label id="nusa-confirm-input-label" class="block text-xs font-semibold text-slate-500 mb-1.5"></label>
            <input type="text" id="nusa-confirm-input" class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="">
        </div>
        <div class="flex gap-2 mt-6">
            <button type="button" id="nusa-confirm-cancel" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
            <button type="button" id="nusa-confirm-ok" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm shadow-blue-600/20">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

@once
<script>
(function () {
    if (window.nusaConfirm) { return; }

    var modal = document.getElementById('nusa-confirm');
    var box = modal.querySelector('#nusa-confirm-box');
    var icon = modal.querySelector('#nusa-confirm-icon');
    var title = modal.querySelector('#nusa-confirm-title');
    var message = modal.querySelector('#nusa-confirm-message');
    var cancelBtn = modal.querySelector('#nusa-confirm-cancel');
    var okBtn = modal.querySelector('#nusa-confirm-ok');
    var inputWrap = modal.querySelector('#nusa-confirm-input-wrap');
    var inputLabel = modal.querySelector('#nusa-confirm-input-label');
    var input = modal.querySelector('#nusa-confirm-input');
    var inputActive = false;
    var requireValue = false;
    var onConfirm = null;

    function open(opts) {
        opts = opts || {};
        var danger = opts.danger === true;
        title.textContent = opts.title || 'Konfirmasi';
        message.textContent = opts.message || 'Apakah Anda yakin ingin melanjutkan aksi ini?';
        cancelBtn.textContent = opts.cancelText || 'Batal';
        okBtn.textContent = opts.confirmText || 'Ya, Lanjutkan';
        okBtn.className = 'flex-1 px-4 py-2.5 text-sm font-semibold text-white rounded-lg transition-colors shadow-sm ' +
            (danger ? 'bg-red-600 hover:bg-red-700 shadow-red-600/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20');
        icon.className = 'mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4 ' +
            (danger ? 'bg-red-100 text-red-500' : 'bg-amber-100 text-amber-500');
        onConfirm = opts.onConfirm || null;

        inputActive = !!(opts.input && (opts.input.placeholder !== undefined || opts.input.label !== undefined || opts.input.required));
        requireValue = !!(opts.input && opts.input.required);
        inputWrap.classList.toggle('hidden', ! inputActive);
        if (inputActive) {
            inputLabel.textContent = opts.input.label || '';
            input.placeholder = opts.input.placeholder || '';
            input.value = opts.input.value || '';
            input.classList.remove('border-red-300');
            setTimeout(function () { input.focus(); }, 80);
        } else {
            input.value = '';
            requireValue = false;
        }

        modal.classList.remove('hidden');
        box.classList.remove('scale-95', 'opacity-0', 'translate-y-2');
        box.classList.add('scale-100', 'opacity-100', 'translate-y-0');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { document.addEventListener('keydown', onKey, true); }, 50);
    }

    function close() {
        modal.classList.add('hidden');
        box.classList.remove('scale-100', 'opacity-100', 'translate-y-0');
        box.classList.add('scale-95', 'opacity-0', 'translate-y-2');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', onKey, true);
        onConfirm = null;
        inputActive = false;
        requireValue = false;
        inputWrap.classList.add('hidden');
        input.value = '';
        input.classList.remove('border-red-300');
    }

    function onKey(e) {
        if (e.key === 'Escape') { close(); }
        if (e.key === 'Enter') { okBtn.click(); }
    }

    okBtn.addEventListener('click', function () {
        if (inputActive && requireValue && ! input.value.trim()) {
            input.classList.add('border-red-300');
            input.focus();
            return;
        }
        var value = inputActive ? input.value.trim() : '';
        var fn = onConfirm;
        close();
        if (typeof fn === 'function') { fn(value); }
    });
    cancelBtn.addEventListener('click', close);
    modal.querySelector('#nusa-confirm-backdrop').addEventListener('click', close);

    // Intercept semua form yang punya atribut data-confirm
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-confirm]');
        if (!form) { return; }
        e.preventDefault();
        e.stopPropagation();
        open({
            title: form.getAttribute('data-confirm-title') || undefined,
            message: form.getAttribute('data-confirm-message'),
            confirmText: form.getAttribute('data-confirm-text') || undefined,
            danger: form.getAttribute('data-confirm-danger') === 'true',
            onConfirm: function () { form.submit(); }
        });
    });

    window.nusaConfirm = function (opts) { open(opts); };
})();
</script>
@endonce