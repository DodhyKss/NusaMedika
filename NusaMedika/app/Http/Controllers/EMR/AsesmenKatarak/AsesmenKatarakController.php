<?php

namespace App\Http\Controllers\EMR\AsesmenKatarak;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Form Asesmen Katarak (form 101, slug `asesmen_katarak`).
 *
 * Asesmen keperawatan khusus operasi katarak (KONSEP §19): skrining
 * risiko jatuh, nyeri (VAS 0-10), diagnosis keperawatan, intervensi
 * pre/post operasi, jadwal pemberian obat & TTV + visus harian.
 *
 * Guard: bila `resiko_jatuh = 'Risiko Tinggi'` maka `intervensi_pre_operasi`
 * wajib memuat intervensi (min. 5 karakter — pola form 90).
 */
class AsesmenKatarakController extends Controller
{
    private const SLUG = 'asesmen_katarak';

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

        return view('moduls.EMR.AsesmenKatarak.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Katarak berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Katarak: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Katarak berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Katarak: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Katarak berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Katarak: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'jam_asesmen' => 'required|date_format:H:i',
            'dpjp_utama' => 'required|integer|exists:pegawai,pegawai_id',
            'keluhan' => 'required|string|max:2000',
            'kesadaran' => ['required', Rule::in(array_column(SelectOption::get('kesadaran'), 'value'))],
            'resiko_jatuh' => 'required|in:Risiko Tinggi,Tidak Berisiko',
            'edukasi' => 'required|string|max:2000',
            'diagnosa_keperawatan' => 'required|string|max:2000',
            'tujuan_kriteria_hasil' => 'required|string|max:2000',
            'intervensi_pre_operasi' => 'required|string|max:2000',
            'intervensi_post_operasi' => 'required|string|max:2000',
            'skala_nyeri' => 'required|integer|between:0,10',
            'jam_pemberian_obat_1' => 'nullable|date_format:H:i',
            'nama_obat_1' => 'nullable|string|max:2000',
            'sistolik_1' => 'required|integer|min:0',
            'diastolik_1' => 'required|integer|min:0',
            'nadi_1' => 'required|integer|min:0',
            'suhu_1' => 'required|numeric|min:0',
            'pernapasan_1' => 'required|integer|min:0',
            'skala_nyeri_1' => 'required|integer|between:0,10',
            'visus_kanan_1' => 'required|string|max:2000',
            'visus_kiri_1' => 'required|string|max:2000',
            'jam_pemberian_obat_2' => 'nullable|date_format:H:i',
            'nama_obat_2' => 'nullable|string|max:2000',
            'sistolik_2' => 'required|integer|min:0',
            'diastolik_2' => 'required|integer|min:0',
            'nadi_2' => 'required|integer|min:0',
            'suhu_2' => 'required|numeric|min:0',
            'pernapasan_2' => 'required|integer|min:0',
            'skala_nyeri_2' => 'required|integer|between:0,10',
            'visus_kanan_2' => 'required|string|max:2000',
            'visus_kiri_2' => 'required|string|max:2000',
            'jam_pemberian_obat_3' => 'nullable|date_format:H:i',
            'nama_obat_3' => 'nullable|string|max:2000',
            'sistolik_3' => 'required|integer|min:0',
            'diastolik_3' => 'required|integer|min:0',
            'nadi_3' => 'required|integer|min:0',
            'suhu_3' => 'required|numeric|min:0',
            'pernapasan_3' => 'required|integer|min:0',
            'skala_nyeri_3' => 'required|integer|between:0,10',
            'visus_kanan_3' => 'required|string|max:2000',
            'visus_kiri_3' => 'required|string|max:2000',
            'jam_pemberian_obat_4' => 'nullable|date_format:H:i',
            'nama_obat_4' => 'nullable|string|max:2000',
            'sistolik_4' => 'required|integer|min:0',
            'diastolik_4' => 'required|integer|min:0',
            'nadi_4' => 'required|integer|min:0',
            'suhu_4' => 'required|numeric|min:0',
            'pernapasan_4' => 'required|integer|min:0',
            'skala_nyeri_4' => 'required|integer|between:0,10',
            'visus_kanan_4' => 'required|string|max:2000',
            'visus_kiri_4' => 'required|string|max:2000',
            'jam_pemberian_obat_5' => 'nullable|date_format:H:i',
            'nama_obat_5' => 'nullable|string|max:2000',
            'sistolik_5' => 'required|integer|min:0',
            'diastolik_5' => 'required|integer|min:0',
            'nadi_5' => 'required|integer|min:0',
            'suhu_5' => 'required|numeric|min:0',
            'pernapasan_5' => 'required|integer|min:0',
            'skala_nyeri_5' => 'required|integer|between:0,10',
            'visus_kanan_5' => 'required|string|max:2000',
            'visus_kiri_5' => 'required|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) {
            if (request('resiko_jatuh') === 'Risiko Tinggi'
                && strlen(trim((string) request('intervensi_pre_operasi', ''))) < 5) {
                $validator->errors()->add('intervensi_pre_operasi', 'Intervensi pre operasi wajib memuat uraian intervensi bila skrining risiko jatuh Tinggi.');
            }
        });
        $validator->validate();

        return array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );
    }

    private function kosong(): array
    {
        $kosong = [
            'tanggal_asesmen' => '', 'jam_asesmen' => '', 'dpjp_utama' => '', 'keluhan' => '',
            'kesadaran' => '', 'resiko_jatuh' => '', 'edukasi' => '',
            'diagnosa_keperawatan' => '', 'tujuan_kriteria_hasil' => '',
            'intervensi_pre_operasi' => '', 'intervensi_post_operasi' => '', 'skala_nyeri' => '',
        ];
        for ($i = 1; $i <= 5; $i++) {
            $kosong['jam_pemberian_obat_'.$i] = '';
            $kosong['nama_obat_'.$i] = '';
            $kosong['sistolik_'.$i] = '';
            $kosong['diastolik_'.$i] = '';
            $kosong['nadi_'.$i] = '';
            $kosong['suhu_'.$i] = '';
            $kosong['pernapasan_'.$i] = '';
            $kosong['skala_nyeri_'.$i] = '';
            $kosong['visus_kanan_'.$i] = '';
            $kosong['visus_kiri_'.$i] = '';
        }

        return $kosong;
    }
}
