<?php

namespace App\Http\Controllers\EMR\InisiasiMenyusuiDini;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Inisiasi Menyusui Dini (form 68, slug `inisiasi_menyusui_dini`).
 *
 * Dokumentasi IMD (skin-to-skin 1 jam pertama). Nilai di luar 60 menit TIDAK
 * ditolak — hanya diberi badge peringatan (IMD > 1 jam tetap mungkin).
 *
 * CATATAN penamaan: variabel `inisisasi_menyususui_dini` dan
 * `alasan_tidak_menyususui_dini` memakai ejaan typo legacy (objek 599) dan
 * sudah terkunci di ALOKASI_ID_GLOBAL — jangan "diperbaiki".
 */
class InisiasiMenyusuiDiniController extends Controller
{
    private const SLUG = 'inisiasi_menyusui_dini';

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
            $emr_data = EmrHelper::wrapData([
                'inisisasi_menyususui_dini' => '',
                'waktu_menyusui_dini' => '',
                'durasi_menyusui_dini' => '',
                'alasan_tidak_menyususui_dini' => '',
            ]);
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

        return view('moduls.EMR.InisiasiMenyusuiDini.index', compact(
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
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Inisiasi Menyusui Dini berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Inisiasi Menyusui Dini: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Inisiasi Menyusui Dini berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Inisiasi Menyusui Dini: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Inisiasi Menyusui Dini berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Inisiasi Menyusui Dini: '.$e->getMessage());
        }
    }

    private function validated(Request $request): array
    {
        $validator = \Validator::make($request->all(), [
            'inisisasi_menyususui_dini' => 'required|in:Ya,Tidak',
            'waktu_menyusui_dini' => 'required_if:inisisasi_menyususui_dini,Ya|nullable|string|max:100',
            'durasi_menyusui_dini' => 'nullable|string|max:100',
            'alasan_tidak_menyususui_dini' => 'required_if:inisisasi_menyususui_dini,Tidak|nullable|string|max:255',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('inisisasi_menyususui_dini') !== 'Ya') {
                return;
            }
            $waktu = $request->input('waktu_menyusui_dini');
            if (is_string($waktu) && preg_match('/(\d+)/', $waktu, $m) && (int) $m[1] > 60) {
                // Bukan blocking — hanya peringatan di view; sengaja tidak
                // menambah error.
            }
        });
        $validator->validate();

        return array_merge([
            'inisisasi_menyususui_dini' => null,
            'waktu_menyusui_dini' => null,
            'durasi_menyusui_dini' => null,
            'alasan_tidak_menyususui_dini' => null,
        ], $validator->validated());
    }
}
