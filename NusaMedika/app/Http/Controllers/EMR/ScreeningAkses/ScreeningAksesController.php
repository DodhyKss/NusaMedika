<?php

namespace App\Http\Controllers\EMR\ScreeningAkses;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Screening Akses Vaskular (form 120, slug `screening_akses`).
 *
 * Screening kelayakan AV Shunt/Cimino vs CDL sebelum pemasangan/penggantian
 * (KONSEP_SURVEILANS_INFEKSI.md §7).
 *
 * Aturan server:
 * - `jenis_spesimen` menentukan blok mana yang berlaku: blok yang TIDAK
 *   berlaku di-set null di `filteredData()` (bukan hanya disabled di UI).
 * - Peringatan kontraindikasi (steal/exit site) = badge JS, non-blocking.
 * - Deviasi terdokumentasi: nilai `jenis_spesimen` memakai dua nilai legacy
 *   huruf kapital persis (rule literal, bukan Rule::in(SelectOption)).
 */
class ScreeningAksesController extends Controller
{
    private const SLUG = 'screening_akses';

    private const JAWABAN = ['Ya', 'Tidak'];

    private const AV = [
        'aks_area_1', 'aks_area_2', 'aks_area_3',
        'aks_steal_1', 'aks_steal_2', 'aks_steal_3', 'aks_steal_4', 'aks_steal_5',
        'aks_steal_6', 'aks_steal_7', 'aks_steal_8', 'aks_steal_9',
    ];

    private const CDL = [
        'aks_cdl_1', 'aks_cdl_2', 'aks_cdl_3', 'aks_cdl_4',
        'aks_exit_1', 'aks_exit_2', 'aks_exit_3', 'aks_exit_4', 'aks_exit_5', 'aks_exit_6',
        'aks_pasien_1',
        'aks_disfungsi_1', 'aks_disfungsi_2', 'aks_disfungsi_3',
        'aks_nyeri_1', 'aks_nyeri_2', 'aks_nyeri_3', 'aks_nyeri_4',
    ];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong());
            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);
            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        $opsiJenis = SelectOption::get('jenis_screening_akses');

        return view('moduls.EMR.ScreeningAkses.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'opsiJenis'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Screening Akses berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Screening Akses: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validated($request, (int) $form_id);
            $data = $this->filteredData($data);

            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Screening Akses berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Screening Akses: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Screening Akses berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Screening Akses: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'jenis_spesimen' => 'required|in:SCREENING AKSES AV SHUNT/CIMINO,SCREENING AKSES VASKULER CDL',
        ];

        foreach (array_merge(self::AV, self::CDL) as $key) {
            $rules[$key] = 'required|in:'.implode(',', self::JAWABAN);
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    /**
     * Blok di luar `jenis_spesimen` yang dipilih di-set null — baris
     * `emr_detail` tetap ada (simpan E2E penuh) tapi tidak menyesatkan.
     */
    private function filteredData(array $data): array
    {
        $jenis = $data['jenis_spesimen'] ?? '';
        $keAv = $jenis === 'SCREENING AKSES AV SHUNT/CIMINO';

        foreach (($keAv ? self::CDL : self::AV) as $key) {
            $data[$key] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = ['jenis_spesimen' => ''];

        foreach (array_merge(self::AV, self::CDL) as $key) {
            $d[$key] = '';
        }

        return $d;
    }
}
