<?php

namespace App\Http\Controllers\EMR\PengkajianHarianKeperawatan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\RisikoJatuhHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pengkajian Harian Keperawatan (form slug `pengkajian_harian_keperawatan`).
 *
 * Diisi setiap shift selama pasien dirawat: keluhan, tanda vital, nyeri, risiko
 * jatuh (dinilai ulang harian), serta balance cairan dan eliminasi.
 */
class PengkajianHarianKeperawatanController extends Controller
{
    private const SLUG = 'pengkajian_harian_keperawatan';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $riwayatHarian = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        // Kalau tidak boleh membuat data baru, langsung tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayatHarian->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayatHarian->first()->emr_id,
                'action' => 'view',
            ]);
        }

        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData([
                'keluhan' => '',
                'catatan_keperawatan' => '',
                'kesadaran' => '', 'dpo' => '',
                'gcs_e' => '', 'gcs_m' => '', 'gcs_v' => '', 'gcs_jumlah' => '',
                'td' => '', 'nadi' => '', 'suhu' => '', 'pernapasan' => '',
                'berat_badan' => '', 'tinggi_badan' => '', 'saturasi' => '', 'ews' => '',
                'pemberian_o2' => '', 'cara_pemberian_o2' => '', 'ett' => '',
                'nyeri' => 'tidak', 'alergi' => 'tidak',
                'eliminasi' => '', 'intake_cairan' => '',
                'intake_makanan' => '', 'tidur' => '', 'catatan_tambahan' => '',
                'risiko_jatuh_instrumen' => RisikoJatuhHelper::instrumenUntukUsia($usia),
                'risiko_jatuh_skor' => '', 'risiko_jatuh_label' => '',
                'risiko_jatuh_intervensi' => '', 'risiko_jatuh_ringkasan' => '',
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

        // Riwayat KUNJUNGAN untuk dropdown "History" di panel kiri.
        // Riwayat emr sendiri sudah diambil komponen x-emr-history-table.
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        return view('moduls.EMR.PengkajianHarianKeperawatan.index', compact(
            'registrasi_detail',
            'riwayatHarian',
            'emr_data',
            'historyGrouped',
            'formAction',
            'isEdit',
            'deleteAction',
            'isView',
            'emr_id',
            'aksesCrud',
            'usia'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($request, (int) $form_id, $usia), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pengkajian Harian Keperawatan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pengkajian harian: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        $usia = RisikoJatuhHelper::hitungUsia($registrasi_detail->registrasi->pasien->tgl_lahir ?? null);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($request, (int) $form_id, $usia));

            return redirect()->back()->with('success', 'Pengkajian Harian Keperawatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pengkajian harian: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pengkajian harian berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pengkajian harian: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terdaftar di mapping form yang disimpan. Skor risiko jatuh
     * dihitung ulang di server dari jawaban mentah (bukan dari hidden input).
     */
    private function filteredData(Request $request, int $formId, ?int $usia = null): array
    {
        $mapped = array_flip(EmrHelper::objekVariabels($formId));
        $data = array_intersect_key($request->all(), $mapped);

        $kode = RisikoJatuhHelper::normalisasiKode($request->input('risiko_jatuh_instrumen'))
            ?: RisikoJatuhHelper::deteksiInstrumen($data);

        $hasil = RisikoJatuhHelper::hitung($kode, $data, $usia);

        // Belum ada item yang terisi -> simpan kosong, bukan "Belum Dinilai".
        $data['risiko_jatuh_instrumen'] = $kode;
        $data['risiko_jatuh_skor'] = $hasil['skor'];
        $data['risiko_jatuh_label'] = $hasil['lengkap'] ? $hasil['label'] : '';
        $data['risiko_jatuh_intervensi'] = $hasil['intervensi'];
        $data['risiko_jatuh_ringkasan'] = RisikoJatuhHelper::ringkasan($hasil);

        return $data;
    }
}
