<?php

namespace App\Http\Controllers\EMR;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\McuHelper;
use App\Helpers\McuSpesialistikHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Base controller untuk form Medical Check Up spesialis yang berpola
 * "Tidak Ada / Ada" + uraian (form 74–81 dan 83).
 *
 * Sembilan form MCU berbagi struktur identik:
 *   keluhan saat ini → parameter per organ (`<kunci>_ada` + `<kunci>`)
 *   → diagnosa → rekomendasi
 *
 * Subclass hanya meng-override `$slug` dan (bila perlu) `rulesTambahan()`;
 * definisi parameter organ diambil dari `McuSpesialistikHelper::organ()` yang
 * sudah diverifikasi terhadap `objek_form_control`.
 *
 * FORM YANG TIDAK memakai base ini (lihat `McuSpesialistikHelper::FORM_BESPOKE`):
 * 72 `visus_mata_mcu`, 73 `obstetri_mcu`, 82 `gizi_mcu`, 84 `psikologi_mcu`
 * — semuanya punya bentuk field yang berbeda.
 *
 * POLA "TIDAK ADA / ADA" (docs/KONSEP_FORM_MCU.md §3.4):
 * - `<kunci>_ada` = '0' → field uraian kosong dan **dibuang** di `filteredData()`.
 * - `<kunci>_ada` = '1' → field uraian **wajib** diisi.
 */
abstract class McuSpesialistikController extends Controller
{
    /** Slug form; di-override tiap subclass. */
    protected $slug = '';

    /** Judul panel di view. */
    protected $judulPanel = '';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug($this->slug);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Ringkasan temuan organ lain untuk form ini — panel read-only.
        $ringkasan = McuHelper::ringkasan($registrasi_detail);

        $organ = McuSpesialistikHelper::organ($this->slug);
        $judulPanel = $this->judulPanel !== '' ? $this->judulPanel : McuSpesialistikHelper::definisi($this->slug)['judul'];

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => $this->slug,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong($organ));
            $formAction = route('emr.form.store', ['form_name' => $this->slug, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);
            $formAction = route('emr.form.update', ['form_name' => $this->slug, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => $this->slug, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        return view($this->viewName(), compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'organ', 'judulPanel', 'ringkasan'
        ) + [
            'slug' => $this->slug,
            'anamnesisEkstra' => $this->anamnesisEkstra(),
        ]);
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug($this->slug);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        $organ = McuSpesialistikHelper::organ($this->slug);

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id, $organ), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', $this->labelForm().' berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan '.$this->labelForm().': '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug($this->slug);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);
        $organ = McuSpesialistikHelper::organ($this->slug);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id, $organ));

            return redirect()->back()->with('success', $this->labelForm().' berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui '.$this->labelForm().': '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug($this->slug);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', $this->labelForm().' berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus '.$this->labelForm().': '.$e->getMessage());
        }
    }

    /**
     * Aturan validasi dasar. Subclass boleh menambah/menimpa lewat
     * {@see rulesTambahan()}.
     */
    protected function validated(Request $request): array
    {
        $rules = [
            'keluhan_saat_ini' => 'nullable|string|max:1000',
            'diagnosa' => 'required|string|max:2000',
            'rekomendasi' => 'required|string|max:2000',
        ];

        $opsional = ['keluhan_saat_ini' => null];
        foreach (McuSpesialistikHelper::organ($this->slug) as $o) {
            $rules[$o['kunci'].'_ada'] = 'required|in:0,1';
            $rules[$o['kunci']] = 'nullable|string|max:2000';
            $opsional[$o['kunci']] = null;
        }

        return array_merge($opsional, $request->validate(array_merge($rules, $this->rulesTambahan())), $opsional);
    }

    /**
     * Hook subclass: aturan tambahan spesifik form.
     *
     * @return array<string, mixed>
     */
    protected function rulesTambahan(): array
    {
        return [];
    }

    /**
     * Hook subclass: field anamnesis tambahan yang dirender di panel
     * "Keluhan Saat Ini" (di luar pola `_ada`).
     *
     * @return array<string, string> variabel => label
     */
    protected function anamnesisEkstra(): array
    {
        return [];
    }

    /**
     * Normalisasi & bersihkan payload.
     *
     * - `<kunci>_ada` dinormalkan ke '0'/'1'.
     * - Bila `<kunci>_ada` = '0', field uraian di-set **null** (bukan `unset()`,
     *   supaya baris `emr_detail` tetap ada dan kelengkapan mapping terverifikasi).
     */
    protected function filteredData(array $data, int $formId, array $organ): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach ($organ as $o) {
            $ada = McuSpesialistikHelper::normalisasiAda($data[$o['kunci'].'_ada'] ?? '0');
            $data[$o['kunci'].'_ada'] = $ada;
            if ($ada === '0') {
                $data[$o['kunci']] = null;
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * Nama view blade untuk form ini (stem `Str::studly($slug)`).
     */
    protected function viewName(): string
    {
        return 'moduls.EMR.'.Str::studly($this->slug).'.index';
    }

    /**
     * Label form untuk pesan flash.
     */
    protected function labelForm(): string
    {
        return $this->judulPanel !== '' ? $this->judulPanel : ucfirst(str_replace('_', ' ', $this->slug));
    }

    /**
     * Nilai kosong untuk form baru.
     */
    protected function kosong(array $organ): array
    {
        $d = [
            'keluhan_saat_ini' => '',
            'diagnosa' => '',
            'rekomendasi' => '',
        ];
        foreach ($this->anamnesisEkstra() as $key => $label) {
            $d[$key] = '';
        }
        foreach ($organ as $o) {
            $d[$o['kunci'].'_ada'] = '0';
            $d[$o['kunci']] = '';
        }

        return $d;
    }
}
