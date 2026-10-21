<?php

namespace App\Http\Controllers\EMR\PenandaanPasien;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Penandaan Pasien (form 96, slug `penandaan_pasien`).
 *
 * Penandaan gelang risiko pasien (alergi, hepatitis, HIV, TB, geriatri) —
 * legacy `penandaan_pasien.php` (KONSEP §14). Setiap penanda opsional;
 * field terkait wajib bila penanda-nya aktif.
 */
class PenandaanPasienController extends Controller
{
    private const SLUG = 'penandaan_pasien';

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

        return view('moduls.EMR.PenandaanPasien.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
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

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Penandaan Pasien berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Penandaan Pasien: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Penandaan Pasien berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Penandaan Pasien: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Penandaan Pasien berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Penandaan Pasien: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_penandaan' => 'required|date',
            'jam_penandaan' => 'required|date_format:H:i',
            'penanda_alergi' => 'required|in:0,1',
            'alergi_1' => 'nullable|string|max:2000',
            'alergi_2' => 'nullable|string|max:2000',
            'alergi_3' => 'nullable|string|max:2000',
            'alergi_4' => 'nullable|string|max:2000',
            'alergi_5' => 'nullable|string|max:2000',
            'penanda_hepatitis' => 'required|in:0,1',
            'jenis_hepatitis' => 'required_if:penanda_hepatitis,1|in:B,C',
            'penanda_hiv' => 'required|in:0,1',
            'penanda_tb' => 'required|in:0,1',
            'penanda_geriatri' => 'required|in:0,1',
            'alasan_geriatri' => 'required_if:penanda_geriatri,1|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            if (request('penanda_alergi') === '1') {
                $terisi = 0;
                for ($i = 1; $i <= 5; $i++) {
                    if (trim((string) request('alergi_'.$i, '')) !== '') {
                        $terisi++;
                    }
                }
                if ($terisi < 1) {
                    $validator->errors()->add('alergi_1', 'Minimal satu daftar alergi wajib diisi bila penanda alergi diaktifkan.');
                }
            }
        });
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Field kondisional di-null-kan bila penanda terkait tidak aktif.
        if (($data['penanda_alergi'] ?? null) !== '1') {
            for ($i = 1; $i <= 5; $i++) {
                $data['alergi_'.$i] = null;
            }
        }
        if (($data['penanda_hepatitis'] ?? null) !== '1') {
            $data['jenis_hepatitis'] = null;
        }
        if (($data['penanda_geriatri'] ?? null) !== '1') {
            $data['alasan_geriatri'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'tanggal_penandaan' => '', 'jam_penandaan' => '',
            'penanda_alergi' => '0', 'alergi_1' => '', 'alergi_2' => '', 'alergi_3' => '',
            'alergi_4' => '', 'alergi_5' => '',
            'penanda_hepatitis' => '0', 'jenis_hepatitis' => '',
            'penanda_hiv' => '0', 'penanda_tb' => '0',
            'penanda_geriatri' => '0', 'alasan_geriatri' => '',
        ];
    }
}
