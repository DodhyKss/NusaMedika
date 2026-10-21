<?php

namespace App\Http\Controllers\EMR\Dar;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form DAR (D-Rekognisi) — form 21, slug `dar`.
 *
 * Catatan kejadian untuk keperluan hukum/forensik. Tiga isi utama: dasar
 * laporan, kronologi kejadian, temuan pemeriksaan, lalu kesimpulan dan
 * tindakan. Panel ringkasan klinis di atas form bersifat read-only — diambil
 * dari form Triage IGD atau Pengkajian Awal Keperawatan pada kunjungan yang sama
 * dan tidak disimpan di form ini.
 */
class DarController extends Controller
{
    private const SLUG = 'dar';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);
        $ringkasan = $this->ringkasanKlinis((int) $registrasi_detail_id);

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

        return view('moduls.EMR.Dar.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'ringkasan'
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

            return redirect()->back()->with('success', 'DAR berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan DAR: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'DAR berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui DAR: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'DAR berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus DAR: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. Tanggal default hari ini.
     */
    private function kosong(): array
    {
        return [
            'tanggal_dar' => now()->toDateString(),
            'dasar_laporan' => '',
            'kronologi_kejadian' => '',
            'temuan_pemeriksaan' => '',
            'kesimpulan_dar' => '',
            'tindakan_dar' => '',
            'meninggal' => '',
        ];
    }

    private function validated(Request $request): array
    {
        return array_merge([
            'meninggal' => null,
        ], $request->validate([
            'tanggal_dar' => 'required|date',
            'dasar_laporan' => 'required|string|max:2000',
            'kronologi_kejadian' => 'required|string|max:3000',
            'temuan_pemeriksaan' => 'required|string|max:3000',
            'kesimpulan_dar' => 'required|string|max:3000',
            'tindakan_dar' => 'required|string|max:3000',
            'meninggal' => ['nullable', Rule::in(array_column(SelectOption::get('penyebab_kematian'), 'value'))],
        ]));
    }

    /**
     * Klasifikasi kematian hanya bermakna bila menyertai kesimpulan yang
     * menyebut kematian; bila kosong, nilainya dibuang supaya tidak ada baris
     * yatim di emr_detail.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        if (trim((string) ($data['meninggal'] ?? '')) === '') {
            $data['meninggal'] = null;
        }

        return $data;
    }

    /**
     * Ringkasan klinis read-only dari TRIAGE IGD, atau Pengkajian Awal
     * Keperawatan bila triase belum diisi. Nilai diambil dari EMR TERBARU
     * pada registrasi_detail yang sama.
     *
     * @return array<string, string>
     */
    private function ringkasanKlinis(int $registrasiDetailId): array
    {
        $sumber = [
            [
                'slug' => 'triage_igd',
                'label' => [
                    'keadaan_umum' => 'Keadaan Umum',
                    'kesadaran' => 'Kesadaran',
                    'td_sistolik' => 'Tekanan Darah Sistolik',
                    'td_diastolik' => 'Tekanan Darah Diastolik',
                    'nadi' => 'Nadi (/menit)',
                    'frekuensi_nafas' => 'Pernapasan (/menit)',
                    'suhu' => 'Suhu',
                    'berat_badan' => 'Berat Badan (Kg)',
                    'tinggi_badan' => 'Tinggi Badan (Cm)',
                    'alasan_kunjungan' => 'Keluhan Utama',
                    'risiko_jatuh' => 'Risiko Jatuh',
                ],
            ],
            [
                'slug' => 'pengkajian_awal_keperawatan',
                'label' => [
                    'kesadaran' => 'Kesadaran',
                    'td' => 'Tekanan Darah',
                    'nadi' => 'Nadi (/menit)',
                    'pernapasan' => 'Pernapasan (/menit)',
                    'suhu' => 'Suhu',
                    'berat_badan' => 'Berat Badan (Kg)',
                    'tinggi_badan' => 'Tinggi Badan (Cm)',
                    'keluhan' => 'Keluhan Utama',
                    'risiko_jatuh_skor' => 'Skor Risiko Jatuh',
                ],
            ],
        ];

        foreach ($sumber as $s) {
            $form_id = EmrHelper::formIdBySlug($s['slug']);
            if (! $form_id) {
                continue;
            }

            $nilai = EmrHelper::latestValuesByVariabel((int) $form_id, $registrasiDetailId, array_keys($s['label']));
            if ($nilai === []) {
                continue;
            }

            $baris = [];
            foreach ($s['label'] as $variabel => $label) {
                $isi = trim((string) ($nilai[$variabel] ?? ''));
                if ($isi !== '') {
                    $baris[$label] = $isi;
                }
            }

            if ($baris !== []) {
                return $baris;
            }
        }

        return [];
    }
}
