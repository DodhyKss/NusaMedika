{{-- Component: Profile Modal (klik blok profile di navbar) --}}
{{-- Dua tab: update data pegawai & update akun login. Route: profile.pegawai / profile.akun --}}

@php
    $tgl = function ($v) {
        return $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
    };
    $val = function ($key, $current) {
        return old($key, $current ?? '');
    };
    $inputClass = 'w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700';
    $labelClass = 'block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider';
    // Field yang hanya bisa diubah dari menu Administrator (data master / penugasan), terkunci di sini.
    $lockedClass = 'w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed appearance-none outline-none';
    $lockedLabelClass = 'flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider';
@endphp

<div id="profile-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="profile-modal-title">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-profile-close></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-slate-200 bg-blue-50 flex items-center gap-3 flex-shrink-0">
            <div class="w-11 h-11 rounded-full bg-blue-600 text-white flex items-center justify-center text-base font-bold flex-shrink-0">
                {{ strtoupper(substr($user->nama_pegawai ?? $user->user_name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="profile-modal-title" class="text-base font-semibold text-slate-800 truncate">{{ $user->nama_pegawai ?? $user->user_name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data pegawai & akun login Anda.</p>
            </div>
            <button type="button" data-profile-close class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="px-5 pt-4 border-b border-slate-200 flex gap-1.5 flex-shrink-0">
            <button type="button" data-profile-tab="pegawai"
                class="profile-tab px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 border-blue-600 text-blue-700 bg-blue-50/60 transition-colors">
                Data Pegawai
            </button>
            <button type="button" data-profile-tab="akun"
                class="profile-tab px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
                Akun Login
            </button>
        </div>

        <div class="p-5 overflow-y-auto grow">
            @if (! $pegawai)
                <div class="mb-4 px-4 py-3 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg">
                    Akun ini belum terhubung dengan data pegawai, sehingga data pegawai tidak dapat diubah.
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ============================ TAB: DATA PEGAWAI ============================ --}}
            <div data-profile-panel="pegawai" class="space-y-5">
                <form method="POST" action="{{ route('profile.pegawai') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Identitas</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="pf_nama_pegawai" class="{{ $labelClass }}">Nama Pegawai <span class="text-red-500">*</span></label>
                                <input type="text" id="pf_nama_pegawai" name="nama_pegawai" required
                                       value="{{ $val('nama_pegawai', $pegawai->nama_pegawai ?? null) }}"
                                       class="{{ $inputClass }} @error('nama_pegawai') border-red-400 @enderror">
                                @error('nama_pegawai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_nip" class="{{ $labelClass }}">NIP</label>
                                <input type="text" id="pf_nip" name="nip" value="{{ $val('nip', $pegawai->nip ?? null) }}"
                                       class="{{ $inputClass }} @error('nip') border-red-400 @enderror">
                                @error('nip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_nik" class="{{ $labelClass }}">NIK</label>
                                <input type="text" id="pf_nik" name="nik" value="{{ $val('nik', $pegawai->nik ?? null) }}"
                                       class="{{ $inputClass }} @error('nik') border-red-400 @enderror">
                                @error('nik')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_no_rfid" class="{{ $labelClass }}">No. RFID</label>
                                <input type="text" id="pf_no_rfid" name="no_rfid" value="{{ $val('no_rfid', $pegawai->no_rfid ?? null) }}"
                                       class="{{ $inputClass }} @error('no_rfid') border-red-400 @enderror">
                                @error('no_rfid')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_id_satu_sehat" class="{{ $labelClass }}">ID Satu Sehat</label>
                                <input type="text" id="pf_id_satu_sehat" name="id_satu_sehat" value="{{ $val('id_satu_sehat', $pegawai->id_satu_sehat ?? null) }}"
                                       class="{{ $inputClass }} @error('id_satu_sehat') border-red-400 @enderror">
                                @error('id_satu_sehat')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_inacbg_id" class="{{ $labelClass }}">ID INACBG</label>
                                <input type="text" id="pf_inacbg_id" name="inacbg_id" value="{{ $val('inacbg_id', $pegawai->inacbg_id ?? null) }}"
                                       class="{{ $inputClass }} @error('inacbg_id') border-red-400 @enderror">
                                @error('inacbg_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Kepegawaian</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="pf_bagian_id" class="{{ $lockedLabelClass }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path></svg>
                                    Bagian / Ruangan
                                </label>
                                <select id="pf_bagian_id" disabled class="{{ $lockedClass }}">
                                    @foreach ($bagianGroups as $group)
                                        <optgroup label="{{ $group['label'] }}">
                                            @foreach ($group['items'] as $b)
                                                <option value="{{ $b->bagian_id }}" @selected((string) ($pegawai->bagian_id ?? '') === (string) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                    @if (empty($pegawai->bagian_id))
                                        <option value="">-- Tanpa Bagian --</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="pf_profesi_id" class="{{ $lockedLabelClass }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path></svg>
                                    Profesi
                                </label>
                                <select id="pf_profesi_id" disabled class="{{ $lockedClass }}">
                                    @foreach ($profesi as $p)
                                        <option value="{{ $p->profesi_id }}" @selected((string) ($pegawai->profesi_id ?? '') === (string) $p->profesi_id)>{{ $p->nama_profesi }}</option>
                                    @endforeach
                                    @if (empty($pegawai->profesi_id))
                                        <option value="">-- Tanpa Profesi --</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="pf_jabatan_id" class="{{ $lockedLabelClass }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path></svg>
                                    Jabatan
                                </label>
                                <select id="pf_jabatan_id" disabled class="{{ $lockedClass }}">
                                    @foreach ($jabatan as $j)
                                        <option value="{{ $j->jabatan_id }}" @selected((string) ($pegawai->jabatan_id ?? '') === (string) $j->jabatan_id)>{{ $j->nama_jabatan }}</option>
                                    @endforeach
                                    @if (empty($pegawai->jabatan_id))
                                        <option value="">-- Tanpa Jabatan --</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="pf_status_kepegawaian_id" class="{{ $lockedLabelClass }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z"></path></svg>
                                    Status Kepegawaian
                                </label>
                                <select id="pf_status_kepegawaian_id" disabled class="{{ $lockedClass }}">
                                    @foreach ($statusKepegawaian as $s)
                                        <option value="{{ $s->status_kepegawaian_id }}" @selected((string) ($pegawai->status_kepegawaian_id ?? '') === (string) $s->status_kepegawaian_id)>{{ $s->nama_status_kepegawaian }}</option>
                                    @endforeach
                                    @if (empty($pegawai->status_kepegawaian_id))
                                        <option value="">-- Tanpa Status --</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <p class="mt-2 flex items-start gap-1.5 text-xs text-slate-400">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Bagian, Profesi, Jabatan, dan Status Kepegawaian <strong class="font-semibold text-slate-500">tidak dapat diubah dari sini</strong> — diatur oleh Administrator pada menu Master Pegawai.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">SIP &amp; STR</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="pf_sip" class="{{ $labelClass }}">No. SIP</label>
                                <input type="text" id="pf_sip" name="sip" value="{{ $val('sip', $pegawai->sip ?? null) }}"
                                       class="{{ $inputClass }} @error('sip') border-red-400 @enderror">
                                @error('sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_ttd" class="{{ $labelClass }}">TTD</label>
                                <input type="text" id="pf_ttd" name="ttd" value="{{ $val('ttd', $pegawai->ttd ?? null) }}"
                                       class="{{ $inputClass }} @error('ttd') border-red-400 @enderror">
                                @error('ttd')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_tgl_awal_sip" class="{{ $labelClass }}">Tgl. Awal SIP</label>
                                <input type="date" id="pf_tgl_awal_sip" name="tgl_awal_sip" value="{{ old('tgl_awal_sip', $tgl($pegawai->tgl_awal_sip ?? null)) }}"
                                       class="{{ $inputClass }} @error('tgl_awal_sip') border-red-400 @enderror">
                                @error('tgl_awal_sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_tgl_akhir_sip" class="{{ $labelClass }}">Tgl. Akhir SIP</label>
                                <input type="date" id="pf_tgl_akhir_sip" name="tgl_akhir_sip" value="{{ old('tgl_akhir_sip', $tgl($pegawai->tgl_akhir_sip ?? null)) }}"
                                       class="{{ $inputClass }} @error('tgl_akhir_sip') border-red-400 @enderror">
                                @error('tgl_akhir_sip')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_str" class="{{ $labelClass }}">No. STR</label>
                                <input type="text" id="pf_str" name="str" value="{{ $val('str', $pegawai->str ?? null) }}"
                                       class="{{ $inputClass }} @error('str') border-red-400 @enderror">
                                @error('str')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_tgl_akhir_str" class="{{ $labelClass }}">Tgl. Akhir STR</label>
                                <input type="date" id="pf_tgl_akhir_str" name="tgl_akhir_str" value="{{ old('tgl_akhir_str', $tgl($pegawai->tgl_akhir_str ?? null)) }}"
                                       class="{{ $inputClass }} @error('tgl_akhir_str') border-red-400 @enderror">
                                @error('tgl_akhir_str')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-1">
                        <button type="button" data-profile-close class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" @disabled(! $pegawai) @class([
                            'px-4 py-2 text-sm font-semibold text-white rounded-lg shadow-sm transition-all flex items-center gap-1.5',
                            'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20' => $pegawai,
                            'bg-slate-300 cursor-not-allowed shadow-none' => ! $pegawai,
                        ])>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Data Pegawai
                        </button>
                    </div>
                </form>
            </div>

            {{-- ============================ TAB: AKUN LOGIN ============================ --}}
            <div data-profile-panel="akun" class="hidden">
                <form method="POST" action="{{ route('profile.akun') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Kredensial</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="pf_user_name" class="{{ $labelClass }}">Username <span class="text-red-500">*</span></label>
                                <input type="text" id="pf_user_name" name="user_name" required autocomplete="username"
                                       value="{{ old('user_name', $user->user_name) }}"
                                       class="{{ $inputClass }} @error('user_name') border-red-400 @enderror">
                                @error('user_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_user_password" class="{{ $labelClass }}">Password Baru</label>
                                <input type="password" id="pf_user_password" name="user_password" autocomplete="new-password"
                                       class="{{ $inputClass }} @error('user_password') border-red-400 @enderror">
                                <p class="mt-1 text-xs text-slate-400">Kosongkan bila tidak ingin mengubah password (minimal 3 karakter).</p>
                                @error('user_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pf_user_password_konfirmasi" class="{{ $labelClass }}">Ulangi Password Baru</label>
                                <input type="password" id="pf_user_password_konfirmasi" name="user_password_konfirmasi" autocomplete="new-password"
                                       class="{{ $inputClass }} @error('user_password_konfirmasi') border-red-400 @enderror">
                                @error('user_password_konfirmasi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500 space-y-1">
                        <p><span class="font-semibold text-slate-600">User ID:</span> {{ $user->user_id }}</p>
                        <p><span class="font-semibold text-slate-600">Pegawai ID:</span> {{ $user->pegawai_id ?? '-' }}</p>
                        <p><span class="font-semibold text-slate-600">Password diubah terakhir:</span> {{ $user->last_update_pass ? \Illuminate\Support\Carbon::parse($user->last_update_pass)->format('d/m/Y H:i') : 'Belum pernah' }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-1">
                        <button type="button" data-profile-close class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@once
<script>
(function () {
    const modal = document.getElementById('profile-modal');
    if (!modal) { return; }

    const opener = document.getElementById('profile-trigger');
    const tabs = modal.querySelectorAll('[data-profile-tab]');
    const panels = modal.querySelectorAll('[data-profile-panel]');

    function showTab(name) {
        tabs.forEach(function (t) {
            const on = t.dataset.profileTab === name;
            t.className = 'profile-tab px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 transition-colors ' +
                (on ? 'border-blue-600 text-blue-700 bg-blue-50/60' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50');
        });
        panels.forEach(function (p) {
            p.classList.toggle('hidden', p.dataset.profilePanel !== name);
        });
    }

    function openModal(tab) {
        showTab(tab || 'pegawai');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (opener) {
        opener.addEventListener('click', function () { openModal('pegawai'); });
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function () { showTab(t.dataset.profileTab); });
    });

    modal.querySelectorAll('[data-profile-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) { closeModal(); }
    });

    // Buka otomatis ke tab yang gagal validasi (atau tab pertama bila ada error).
    @if ($errors->any())
        openModal(@if ($errors->has('user_name') || $errors->has('user_password') || $errors->has('user_password_konfirmasi'))'akun'@else 'pegawai'@endif);
    @endif
})();
</script>
@endonce
