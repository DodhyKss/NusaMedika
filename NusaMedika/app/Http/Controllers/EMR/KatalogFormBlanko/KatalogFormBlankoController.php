<?php

namespace App\Http\Controllers\EMR\KatalogFormBlanko;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\FormBlanko;
use App\Models\FormBlankoPengisian;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form 129 "Katalog Form Blanko" — alur pengisian pasien (KONSEP_KATALOG_FORM_BLANKO.md §6).
 *
 * Data disimpan di tabel sendiri (`form_blanko`/`_field`/`_pengisian`),
 * BUKAN `emr`/`emr_detail`. Rute manual di routes/web.php (bukan rute
 * generik DynamicFormController), tetapi controller sengaja berada di path
 * auto-detect `EMR\{Studly}\{Studly}Controller` sehingga link dashboard
 * `/emr/form/katalog_form_blanko/{rdid}` (lihat EmrDashboard) ikut
 * ter-delegasi ke `index()` ini.
 *
 * Deviasi terdokumentasi vs KONSEP §4/§6:
 * - Tipe `select2` dirender sebagai select statis (bila opsi diisi) atau
 *   input teks — mesin AJAX Select2 tidak diintegrasikan di halaman EMR
 *   (endpoint sumber per tipe master belum baku). Admin tetap wajib mengisi
 *   `source` sebagai dokumentasi intent.
 * - `filter_adaptif` difilter di PHP (`Collection::filter`) — jalur fallback
 *   yang diizinkan KONSEP §6.2, kompatibel dua driver tanpa whereJsonContains.
 */
class KatalogFormBlankoController extends Controller
{
    public const SLUG = 'katalog_form_blanko';

    /** index: daftar template (katalog) — dipanggil via rute manual & delegasi DynamicFormController. */
    public function index(Request $request, $registrasi_detail_id)
    {
        $formId = $this->formId();
        abort_unless(AksesEhr::can($formId, 'read'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $filter = [
            'kategori_umur' => $request->query('kategori_umur'),
            'jenis_kelamin' => $request->query('jenis_kelamin'),
            'jenis_rawat' => $request->query('jenis_rawat'),
            'profesi_id' => $request->query('profesi_id'),
        ];

        $daftar = FormBlanko::aktif()->get()
            ->filter(fn ($b) => $this->cocokFilter($b, $filter))
            ->values();

        return view('moduls.EMR.KatalogFormBlanko.index', [
            'registrasi_detail' => $registrasi_detail,
            'registrasi_detail_id' => (int) $registrasi_detail_id,
            'daftar' => $daftar,
            'filter' => $filter,
            'aksesCrud' => AksesEhr::flags($formId),
        ]);
    }

    /** isi/edit/view satu template pada satu registrasi_detail. */
    public function isi(Request $request, $form_blanko, $registrasi_detail_id, $pengisian_id = null)
    {
        $formId = $this->formId();
        abort_unless(AksesEhr::can($formId, 'read'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $pengisian = $pengisian_id ? $this->findPengisian($blanko, $registrasi_detail_id, $pengisian_id) : null;

        $isEdit = $pengisian !== null;
        $isView = $request->query('action') === 'view';
        $aksesCrud = AksesEhr::flags($formId);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        $daftarDetailId = collect($historyGrouped)->flatten()->values()->all();
        if (! in_array((int) $registrasi_detail_id, array_map('intval', $daftarDetailId), true)) {
            $daftarDetailId[] = (int) $registrasi_detail_id;
        }

        $riwayat = DB::table('form_blanko_pengisian as fp')
            ->leftJoin('users as u', 'u.user_id', '=', 'fp.input_user_id')
            ->where('fp.form_blanko_id', $blanko->form_blanko_id)
            ->whereIn('fp.registrasi_detail_id', $daftarDetailId)
            ->where(function ($q) {
                $q->whereNull('fp.status_batal')->orWhere('fp.status_batal', 0);
            })
            ->select('fp.*', 'u.nama_pegawai')
            ->orderByDesc('fp.form_blanko_pengisian_id')
            ->limit(50)
            ->get();

        if (! $isEdit) {
            $formAction = route('emr.katalog_form_blanko.store', [
                'form_blanko' => $blanko->form_blanko_id,
                'registrasi_detail_id' => $registrasi_detail_id,
            ]);
            $deleteAction = '';
        } else {
            $formAction = route('emr.katalog_form_blanko.update', [
                'form_blanko' => $blanko->form_blanko_id,
                'registrasi_detail_id' => $registrasi_detail_id,
                'pengisian_id' => $pengisian->form_blanko_pengisian_id,
            ]);
            $deleteAction = $isView ? '' : route('emr.katalog_form_blanko.destroy', [
                'form_blanko' => $blanko->form_blanko_id,
                'registrasi_detail_id' => $registrasi_detail_id,
                'pengisian_id' => $pengisian->form_blanko_pengisian_id,
            ]);
        }

        return view('moduls.EMR.KatalogFormBlanko.isi', [
            'blanko' => $blanko,
            'fields' => $blanko->fields,
            'registrasi_detail' => $registrasi_detail,
            'registrasi_detail_id' => (int) $registrasi_detail_id,
            'pengisian' => $pengisian,
            'pengisian_id' => $pengisian?->form_blanko_pengisian_id,
            'riwayat' => $riwayat,
            'historyGrouped' => $historyGrouped,
            'formAction' => $formAction,
            'deleteAction' => $deleteAction,
            'isEdit' => $isEdit,
            'isView' => $isView,
            'aksesCrud' => $aksesCrud,
        ]);
    }

    public function store(Request $request, $form_blanko, $registrasi_detail_id)
    {
        $formId = $this->formId();
        abort_unless(AksesEhr::can($formId, 'create'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);
        RegistrasiDetail::findOrFail($registrasi_detail_id);

        $data = $this->validatedData($request, $blanko);

        $pengisian = new FormBlankoPengisian;
        $pengisian->form_blanko_id = $blanko->form_blanko_id;
        $pengisian->registrasi_detail_id = (int) $registrasi_detail_id;
        $pengisian->data = $data['bersih'];
        $pengisian->ringkasan = $data['ringkasan'];
        $pengisian->jumlah_terisi = $data['jumlah'];
        $pengisian->input_time = now();
        $pengisian->input_user_id = Auth::id();
        $pengisian->status_batal = 0;
        $pengisian->save();

        return back()->with('success', 'Form blanko "'.e($blanko->nama_blanko).'" berhasil disimpan.');
    }

    public function update(Request $request, $form_blanko, $registrasi_detail_id, $pengisian_id)
    {
        $formId = $this->formId();
        abort_unless(AksesEhr::can($formId, 'update'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);
        $pengisian = $this->findPengisian($blanko, $registrasi_detail_id, $pengisian_id);

        $data = $this->validatedData($request, $blanko);

        $pengisian->data = $data['bersih'];
        $pengisian->ringkasan = $data['ringkasan'];
        $pengisian->jumlah_terisi = $data['jumlah'];
        $pengisian->mod_time = now();
        $pengisian->mod_user_id = Auth::id();
        $pengisian->save();

        return back()->with('success', 'Form blanko "'.e($blanko->nama_blanko).'" berhasil diperbarui.');
    }

    public function destroy($form_blanko, $registrasi_detail_id, $pengisian_id)
    {
        $formId = $this->formId();
        abort_unless(AksesEhr::can($formId, 'delete'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);
        $pengisian = $this->findPengisian($blanko, $registrasi_detail_id, $pengisian_id);

        $pengisian->status_batal = 1;
        $pengisian->mod_time = now();
        $pengisian->mod_user_id = Auth::id();
        $pengisian->save();

        return back()->with('success', 'Pengisian form blanko berhasil dihapus.');
    }

    /** Cetak blanko kosong (tanpa data pasien) — KONSEP §9.1. */
    public function cetakBlanko($form_blanko)
    {
        abort_unless(AksesEhr::can($this->formId(), 'read'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);

        return view('moduls.EMR.KatalogFormBlanko.print_blanko', [
            'blanko' => $blanko,
            'fields' => $blanko->fields,
        ]);
    }

    /** Cetak pengisian tersimpan — KONSEP §9.2. */
    public function print($form_blanko, $registrasi_detail_id, $pengisian_id)
    {
        abort_unless(AksesEhr::can($this->formId(), 'read'), 403);

        $blanko = FormBlanko::aktif()->findOrFail($form_blanko);
        $pengisian = $this->findPengisian($blanko, $registrasi_detail_id, $pengisian_id);
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $petugas = DB::table('users')->where('user_id', $pengisian->input_user_id)->value('nama_pegawai');

        return view('moduls.EMR.KatalogFormBlanko.print', [
            'blanko' => $blanko,
            'fields' => $blanko->fields,
            'pengisian' => $pengisian,
            'registrasi_detail' => $registrasi_detail,
            'petugas' => $petugas,
        ]);
    }

    private function formId(): int
    {
        return (int) EmrHelper::formIdBySlug(self::SLUG);
    }

    private function findPengisian(FormBlanko $blanko, $registrasi_detail_id, $pengisian_id): FormBlankoPengisian
    {
        $pengisian = FormBlankoPengisian::aktif()
            ->where('form_blanko_pengisian_id', $pengisian_id)
            ->where('registrasi_detail_id', $registrasi_detail_id)
            ->firstOrFail();

        abort_unless((int) $pengisian->form_blanko_id === (int) $blanko->form_blanko_id, 404);

        return $pengisian;
    }

    /**
     * Cocokkan template terhadap filter adaptif (KONSEP §6.2, dijalankan di
     * PHP): template TANPA batasan pada suatu dimensi = lolos untuk semua
     * nilai (0/semua). Template DENGAN batasan hanya cocok bila nilai filter
     * ada dalam daftarnya.
     */
    private function cocokFilter(FormBlanko $blanko, array $filter): bool
    {
        $batas = (array) ($blanko->filter_adaptif ?? []);

        foreach (['kategori_umur', 'jenis_kelamin', 'jenis_rawat'] as $k) {
            $v = $filter[$k] ?? null;
            if ($v === null || $v === '') {
                continue;
            }
            $daftar = array_map('strval', (array) ($batas[$k] ?? []));
            if ($daftar !== [] && ! in_array((string) $v, $daftar, true)) {
                return false;
            }
        }

        $profesi = (int) ($filter['profesi_id'] ?? 0);
        if ($profesi > 0) {
            $daftar = array_map('intval', (array) ($batas['profesi_id'] ?? []));
            if ($daftar !== [] && ! in_array($profesi, $daftar, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validasi pengisian dinamis dari definisi field (KONSEP §8.2 + §8.3)
     * lalu sanitasi: buang key di luar definisi (anti gateway), normalisasi
     * checkbox multi, buang nilai kosong, sisipkan __versi, susun ringkasan.
     */
    private function validatedData(Request $request, FormBlanko $blanko): array
    {
        $fields = $blanko->fields;
        $rules = $this->rulesDariDefinisi($fields);
        $attributes = [];
        foreach ($fields as $f) {
            $attributes['data.'.$f->kode_field] = $f->label;
        }

        // §8.3 — field bergantung: wajib hanya saat kondisinya terpenuhi.
        $inputData = (array) $request->input('data', []);
        foreach ($fields->whereNotNull('depends_on_field') as $f) {
            $kondisi = $inputData[$f->depends_on_field] ?? null;
            if ($kondisi !== null && (string) $kondisi === (string) $f->depends_on_value) {
                $rules['data.'.$f->kode_field] = ($rules['data.'.$f->kode_field] ?? 'nullable').'|required';
            }
        }

        $request->validate($rules, [], $attributes);

        $input = (array) $request->input('data', []);
        $kodeValid = $fields->pluck('kode_field')->all();
        $bersih = array_intersect_key($input, array_flip($kodeValid));

        foreach ($fields->where('tipe_kontrol', 'checkbox') as $f) {
            $bersih[$f->kode_field] = array_values((array) ($bersih[$f->kode_field] ?? []));
        }

        foreach ($fields->where('tipe_kontrol', 'select')->where(fn ($f) => (bool) ($f->setelan['multiple'] ?? false)) as $f) {
            $bersih[$f->kode_field] = array_values((array) ($bersih[$f->kode_field] ?? []));
        }

        foreach ($fields->where('tipe_kontrol', 'signature') as $f) {
            $v = $bersih[$f->kode_field] ?? null;
            $bersih[$f->kode_field] = (is_string($v) && str_starts_with($v, 'data:image/')) ? $v : null;
        }

        $bersih = array_filter($bersih, fn ($v) => $v !== null && $v !== '' && $v !== []);

        $jumlah = count($bersih);
        $bersih['__versi'] = $blanko->versi;

        return [
            'bersih' => $bersih,
            'ringkasan' => $this->ringkasan($fields, $bersih),
            'jumlah' => $jumlah,
        ];
    }

    private function rulesDariDefinisi($fields): array
    {
        $rules = [];

        foreach ($fields as $f) {
            $v = (array) ($f->validasi ?? []);

            $base = match ($f->tipe_kontrol) {
                'number' => 'numeric',
                'date', 'datetime' => 'date',
                'time' => 'date_format:H:i',
                'checkbox' => 'array',
                'select' => ($f->setelan['multiple'] ?? false) ? 'array' : 'string',
                default => 'string',
            };

            $parts = [$f->wajib ? 'required' : 'nullable', $base];

            if (isset($v['min'])) {
                $parts[] = 'min:'.$v['min'];
            }
            if (isset($v['max'])) {
                $parts[] = 'max:'.$v['max'];
            }
            if (isset($v['maxLength'])) {
                $parts[] = 'max:'.$v['maxLength'];
            }
            if (isset($v['pattern']) && $v['pattern'] !== '') {
                $parts[] = 'regex:'.$v['pattern'];
            }
            if (isset($v['step'])) {
                $parts[] = 'numeric';
            }

            $rules['data.'.$f->kode_field] = implode('|', $parts);
        }

        return $rules;
    }

    /** Ringkasan tabel riwayat: 3 field pertama yang terisi (KONSEP §6.4). */
    private function ringkasan($fields, array $data): array
    {
        $out = [];
        foreach ($fields as $f) {
            if (count($out) >= 3) {
                break;
            }
            $val = $data[$f->kode_field] ?? null;
            if ($val === null || $val === '' || $val === []) {
                continue;
            }
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            $out[$f->kode_field] = mb_strimwidth((string) $val, 0, 40, '...');
        }

        return $out;
    }
}
