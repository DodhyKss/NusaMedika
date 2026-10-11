<?php

namespace App\Http\Controllers\EMR\CatatanKeperawatanRajal;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Catatan Keperawatan Rawat Jalan (form 54, slug `catatan_keperawatan_rajal`).
 *
 * SOAP ringkas per kunjungan rawat jalan: keluhan, kesadaran, tanda vital,
 * catatan keperawatan, dan alergi. Seluruh field reuse objek yang sudah ada -
 * dokumen ini tidak menambah objek baru sama sekali.
 *
 * Tanda vital di sini bersifat OPSIONAL (bila tidak diukur boleh dikosongkan),
 * berbeda dengan form Tanda Vital yang mewajibkan seluruh parameter.
 */
class CatatanKeperawatanRajalController extends Controller
{
    private const SLUG = 'catatan_keperawatan_rajal';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
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

        return view('moduls.EMR.CatatanKeperawatanRajal.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Catatan Keperawatan Rajal berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Catatan Keperawatan Rajal: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Catatan Keperawatan Rajal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Catatan Keperawatan Rajal: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Catatan Keperawatan Rajal berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Catatan Keperawatan Rajal: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'keluhan' => '', 'kesadaran' => '',
            'td_sistolik' => '', 'td_diastolik' => '', 'nadi' => '',
            'pernapasan' => '', 'suhu' => '', 'saturasi' => '',
            'catatan_keperawatan' => '', 'alergi' => '',
            'tanggal_catatan' => '', 'waktu_catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'td_sistolik' => null,
            'td_diastolik' => null,
            'nadi' => null,
            'pernapasan' => null,
            'suhu' => null,
            'saturasi' => null,
            'alergi' => null,
        ];

        return array_merge($opsional, $request->validate([
            'keluhan' => 'required|string|max:1000',
            'kesadaran' => ['required', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'catatan_keperawatan' => 'required|string|max:2000',
            'alergi' => 'nullable|string|max:500',
            'tanggal_catatan' => 'required|date',
            'waktu_catatan' => 'required|date_format:H:i',

            'td_sistolik' => 'nullable|integer|between:40,300',
            'td_diastolik' => 'nullable|integer|between:20,200',
            'nadi' => 'nullable|integer|between:20,250',
            'pernapasan' => 'nullable|integer|between:4,80',
            'suhu' => 'nullable|numeric|between:30,45',
            'saturasi' => 'nullable|integer|between:50,100',
        ]));
    }

    /**
     * Hanya field terpetakan yang disimpan. Tidak ada field turunan pada form ini.
     */
    private function filteredData(array $data, int $formId): array
    {
        return array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));
    }
}
