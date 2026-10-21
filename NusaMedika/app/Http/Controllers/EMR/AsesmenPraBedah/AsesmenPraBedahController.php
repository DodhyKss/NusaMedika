<?php

namespace App\Http\Controllers\EMR\AsesmenPraBedah;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Asesmen Pra Bedah (form 85, slug `asesmen_pra_bedah`).
 *
 * Asesmen pra-operasi oleh dokter (KONSEP §3): identitas + keluhan, temuan &
 * diagnosa pra operasi, rencana tindakan (jenis operasi Efektif/Darurat),
 * kebutuhan edukasi pra bedah (5 checkbox + lainnya), hasil penunjang,
 * kemungkinan/rencana komplikasi + back up plan, dan prognosis
 * Dubia ad Bonam / Dubia ad Malam.
 */
class AsesmenPraBedahController extends Controller
{
    private const SLUG = 'asesmen_pra_bedah';

    /** Variabel checkbox grup kebutuhan edukasi (objek 768). */
    private const EDUKASI = [
        'kebutuhan_edukasi_persiapan_mentas',
        'kebutuhan_edukasi_persiapan_lensa',
        'kebutuhan_edukasi_puasa',
        'kebutuhan_edukasi_pemberian_obat',
        'kebutuhan_edukasi_lainnya',
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

        return view('moduls.EMR.AsesmenPraBedah.index', compact(
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

            return redirect()->back()->with('success', 'Asesmen Pra Bedah berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Asesmen Pra Bedah: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Asesmen Pra Bedah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Asesmen Pra Bedah: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Asesmen Pra Bedah berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Asesmen Pra Bedah: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_asesmen' => 'required|date',
            'jam_asesmen' => 'required|date_format:H:i',
            'dokter_penilai_id' => 'required|integer|exists:pegawai,pegawai_id',
            'keluhan' => 'required|string|max:2000',
            'diagnosa_pra_operasi' => 'required|string|max:2000',
            'temuan_fisik_pra_bedah' => 'required|string|max:2000',
            'riwayat_alergi' => 'required|string|max:2000',
            'tanggal_rencana_operasi' => 'required|date|after_or_equal:today',
            'jam_rencana_operasi' => 'required|date_format:H:i',
            'jenis_operasi' => 'required|in:Efektif,Darurat',
            'rencana_tindakan_operasi' => 'required|string|max:2000',
            'kebutuhan_edukasi_persiapan_mentas' => 'nullable|in:0,1',
            'kebutuhan_edukasi_persiapan_lensa' => 'nullable|in:0,1',
            'kebutuhan_edukasi_puasa' => 'nullable|in:0,1',
            'kebutuhan_edukasi_pemberian_obat' => 'nullable|in:0,1',
            'kebutuhan_edukasi_lainnya' => 'nullable|in:0,1',
            'kebutuhan_edukasi_lain' => 'required_if:kebutuhan_edukasi_lainnya,1|string|max:2000',
            'hasil_laboratorium' => 'nullable|string|max:2000',
            'hasil_radiologi' => 'nullable|string|max:2000',
            'hasil_penunjang_lain' => 'nullable|string|max:2000',
            'kemungkinan_komplikasi' => 'required|string|max:2000',
            'rencana_komplikasi' => 'required|string|max:2000',
            'back_up_plan' => 'nullable|string|max:2000',
            'prognosis_dubia_ad_bonam' => 'required|in:Dubia ad Bonam,Dubia ad Malam',
            'catatan_prognosis' => 'nullable|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            // back_up_plan wajib bila ada komplikasi yang direncanakan (KONSEP §3).
            if (($request->filled('kemungkinan_komplikasi') || $request->filled('rencana_komplikasi'))
                && ! $request->filled('back_up_plan')) {
                $validator->errors()->add('back_up_plan', 'Back up plan wajib diisi bila ada kemungkinan / rencana komplikasi.');
            }

            // Minimal satu kebutuhan edukasi. Hanya diuji bila grup checkbox
            // benar-benar dikirim (hidden "0") — payload harness tidak
            // mengirim key nullable in:0,1 sama sekali, jadi guard lewat.
            $adaKirim = false;
            $adaPilih = false;
            foreach (self::EDUKASI as $k) {
                $nilai = $request->input($k);
                if ($nilai !== null) {
                    $adaKirim = true;
                }
                if ($nilai === '1') {
                    $adaPilih = true;
                }
            }
            if ($adaKirim && ! $adaPilih) {
                $validator->errors()->add('kebutuhan_edukasi_persiapan_mentas', 'Minimal satu kebutuhan edukasi pra bedah wajib dipilih.');
            }
        });
        $validator->validate();

        // Semua variabel mapping selalu ikut (nullable yang tidak dikirim → null)
        // supaya setiap baris emr_detail tercipta dan tampil lagi saat edit.
        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Normalisasi checkbox: "1" bila terkirim & terpilih, selain itu "0".
        foreach (self::EDUKASI as $k) {
            $data[$k] = $data[$k] === '1' ? '1' : '0';
        }

        // Teks "edukasi lainnya" hanya berlaku bila checkbox Lainnya tercentang.
        if ($data['kebutuhan_edukasi_lainnya'] !== '1') {
            $data['kebutuhan_edukasi_lain'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        $d = [
            'tanggal_asesmen' => '', 'jam_asesmen' => '', 'dokter_penilai_id' => '',
            'keluhan' => '', 'diagnosa_pra_operasi' => '', 'temuan_fisik_pra_bedah' => '',
            'riwayat_alergi' => '', 'tanggal_rencana_operasi' => '', 'jam_rencana_operasi' => '',
            'jenis_operasi' => '', 'rencana_tindakan_operasi' => '', 'kebutuhan_edukasi_lain' => '',
            'hasil_laboratorium' => '', 'hasil_radiologi' => '', 'hasil_penunjang_lain' => '',
            'kemungkinan_komplikasi' => '', 'rencana_komplikasi' => '', 'back_up_plan' => '',
            'prognosis_dubia_ad_bonam' => '', 'catatan_prognosis' => '',
        ];
        foreach (self::EDUKASI as $k) {
            $d[$k] = '0';
        }

        return $d;
    }
}
