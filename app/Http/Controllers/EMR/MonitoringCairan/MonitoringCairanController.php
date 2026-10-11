<?php

namespace App\Http\Controllers\EMR\MonitoringCairan;

use App\Helpers\AksesEhr;
use App\Helpers\CairanHelper;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Monitoring Cairan (form 26, slug `monitoring_cairan`) — TIME SERIES.
 *
 * SATU RONDE OBSERVASI = SATU BARIS `emr`. Tabel `emr_detail` tidak punya
 * kolom ronde, jadi nomor ronde dihitung saat BACA lewat `CairanHelper::ronde()`
 * (urut tgl_jam lalu emr_id), dan tidak bergantung pada urutan array.
 *
 * Semua field readonly (`total_intake`, `output_iwl`, `total_output`,
 * `kebutuhan_cairan`, `balance_cairan`, `total_intake_24jam`, `sisa_infus_N`)
 * dihitung ulang di server; nilai dari browser dibuang.
 *
 * `EmrHelper::storeDetails()` belum mengisi `emr_detail.flag_abnormal`, jadi
 * penulisan flag dilakukan di controller ini (tidak mengubah EmrHelper).
 */
class MonitoringCairanController extends Controller
{
    private const SLUG = 'monitoring_cairan';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan ronde terakhir.
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

        $barangs = CairanHelper::barangs();
        $ronde = CairanHelper::ronde((int) $form_id, (int) $registrasi_detail->registrasi_detail_id);

        return view('moduls.EMR.MonitoringCairan.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'barangs', 'ronde'
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
            $siap = $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id, 0);

            DB::transaction(function () use ($form_id, $siap, $registrasi_detail_id) {
                $emr_id = EmrHelper::insert((int) $form_id, $siap['data'], (int) $registrasi_detail_id);

                // Total intake 24 jam dihitung ulang setelah baris ini ikut
                // tersimpan, lalu ditulis balik supaya ikut ter-flag.
                $kumulatif = CairanHelper::akumulasiIntakeHariIni(
                    (int) $form_id,
                    (int) $registrasi_detail_id,
                    $siap['data']['tanggal_monitoring'] ?? null,
                    $emr_id
                );
                DB::table('emr_detail')
                    ->where('emr_id', $emr_id)
                    ->where('variabel', 'total_intake_24jam')
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->update(['value' => $kumulatif]);

                CairanHelper::tulisFlags($emr_id, $siap['flags']);
            });

            return redirect()->back()->with('success', 'Monitoring Cairan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Monitoring Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            $siap = $this->filteredData($data, (int) $form_id, (int) $registrasi_detail_id, (int) $emr_id);

            DB::transaction(function () use ($form_id, $siap, $registrasi_detail_id, $emr_id) {
                EmrHelper::update((int) $emr_id, (int) $form_id, $siap['data']);

                $kumulatif = CairanHelper::akumulasiIntakeHariIni(
                    (int) $form_id,
                    (int) $registrasi_detail_id,
                    $siap['data']['tanggal_monitoring'] ?? null,
                    (int) $emr_id
                );
                DB::table('emr_detail')
                    ->where('emr_id', $emr_id)
                    ->where('variabel', 'total_intake_24jam')
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->update(['value' => $kumulatif]);

                CairanHelper::tulisFlags((int) $emr_id, $siap['flags']);
            });

            return redirect()->back()->with('success', 'Monitoring Cairan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Monitoring Cairan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Monitoring Cairan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Monitoring Cairan: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru (50 variabel form 26). Tanggal & jam diisi
     * default hari ini, sisanya kosong.
     */
    private function kosong(): array
    {
        $d = [
            'tanggal_monitoring' => now()->format('Y-m-d'),
            'waktu_monitoring' => now()->format('H:i'),
            'berat_badan' => '',
            'suhu' => '',
            'total_intake' => '',
            'output_iwl' => '',
            'total_output' => '',
            'kebutuhan_cairan' => '',
            'balance_cairan' => '',
            'total_intake_24jam' => '',
            'keterangan_monitoring' => '',
        ];

        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            $d['nama_infus_'.$i] = '';
            $d['dosis_infus_'.$i] = '';
            $d['volume_infus_'.$i] = '';
            $d['sisa_infus_'.$i] = '';
            $d['ivfd_'.$i] = '';
        }

        foreach (['intake_oral', 'intake_spoel', 'intake_transfusi', 'intake_lainnya'] as $kunci) {
            $d[$kunci] = '';
        }

        foreach (CairanHelper::OUTPUT_26 as $kunci) {
            $d[$kunci] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'suhu' => null,
            'intake_oral' => null,
            'intake_spoel' => null,
            'intake_transfusi' => null,
            'intake_lainnya' => null,
            'output_urine' => null,
            'output_drain' => null,
            'output_ngt' => null,
            'output_wsd' => null,
            'output_lainnya' => null,
            'keterangan_monitoring' => null,
        ];

        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            $opsional['nama_infus_'.$i] = null;
            $opsional['dosis_infus_'.$i] = null;
            $opsional['volume_infus_'.$i] = null;
            $opsional['ivfd_'.$i] = null;
        }

        return array_merge($opsional, $request->validate([
            'tanggal_monitoring' => 'required|date',
            'waktu_monitoring' => 'required|date_format:H:i',
            'berat_badan' => 'required|numeric|between:1,400',
            'suhu' => 'nullable|numeric|between:30,45',

            'nama_infus_1' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_1' => 'nullable|required_with:volume_infus_1|numeric|min:0',
            'volume_infus_1' => 'nullable|numeric|min:0',
            'ivfd_1' => 'nullable|numeric|min:0',

            'nama_infus_2' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_2' => 'nullable|required_with:volume_infus_2|numeric|min:0',
            'volume_infus_2' => 'nullable|numeric|min:0',
            'ivfd_2' => 'nullable|numeric|min:0',

            'nama_infus_3' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_3' => 'nullable|required_with:volume_infus_3|numeric|min:0',
            'volume_infus_3' => 'nullable|numeric|min:0',
            'ivfd_3' => 'nullable|numeric|min:0',

            'nama_infus_4' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_4' => 'nullable|required_with:volume_infus_4|numeric|min:0',
            'volume_infus_4' => 'nullable|numeric|min:0',
            'ivfd_4' => 'nullable|numeric|min:0',

            'nama_infus_5' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_5' => 'nullable|required_with:volume_infus_5|numeric|min:0',
            'volume_infus_5' => 'nullable|numeric|min:0',
            'ivfd_5' => 'nullable|numeric|min:0',

            'nama_infus_6' => 'nullable|integer|exists:barang,barang_id',
            'dosis_infus_6' => 'nullable|required_with:volume_infus_6|numeric|min:0',
            'volume_infus_6' => 'nullable|numeric|min:0',
            'ivfd_6' => 'nullable|numeric|min:0',

            'intake_oral' => 'nullable|numeric|min:0',
            'intake_spoel' => 'nullable|numeric|min:0',
            'intake_transfusi' => 'nullable|numeric|min:0',
            'intake_lainnya' => 'nullable|numeric|min:0',

            'output_urine' => 'nullable|numeric|min:0',
            'output_drain' => 'nullable|numeric|min:0',
            'output_ngt' => 'nullable|numeric|min:0',
            'output_wsd' => 'nullable|numeric|min:0',
            'output_lainnya' => 'nullable|numeric|min:0',

            'keterangan_monitoring' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Semua nilai turunan dihitung ulang di server; baris infus tanpa nama
     * dibuang seluruh kolomnya; volume tidak boleh melebihi dosis.
     *
     * @return array{data: array<string, mixed>, flags: array<string, int|null>}
     */
    private function filteredData(array $data, int $formId, int $registrasiDetailId, int $emrId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $flags = [];
        $totalIntake = 0.0;

        for ($i = 1; $i <= CairanHelper::MAKS_BARIS; $i++) {
            // Baris yatim: nama cairan kosong -> semua kolom baris itu dibuang.
            if (blank($data['nama_infus_'.$i] ?? null)) {
                unset(
                    $data['dosis_infus_'.$i],
                    $data['volume_infus_'.$i],
                    $data['sisa_infus_'.$i],
                    $data['ivfd_'.$i]
                );

                continue;
            }

            $dosis = (float) ($data['dosis_infus_'.$i] ?? 0);
            $volume = (float) ($data['volume_infus_'.$i] ?? 0);

            // Volume tidak boleh melebihi sisa infus (dosis).
            $volume = min($volume, $dosis > 0 ? $dosis : $volume);
            $data['volume_infus_'.$i] = $volume;
            $data['sisa_infus_'.$i] = $dosis > 0 ? round($dosis - $volume, 0) : null;

            $totalIntake += $volume;

            if ($dosis > 0 && $dosis - $volume < 0) {
                $flags['sisa_infus_'.$i] = 1;
            }
        }

        foreach (['intake_oral', 'intake_spoel', 'intake_transfusi', 'intake_lainnya'] as $kunci) {
            $totalIntake += (float) ($data[$kunci] ?? 0);
        }
        $data['total_intake'] = round($totalIntake, 0);

        $beratBadan = (float) ($data['berat_badan'] ?? 0);
        $suhu = (float) ($data['suhu'] ?? 0);

        $data['output_iwl'] = CairanHelper::hitungIWL($beratBadan, $suhu);
        $data['total_output'] = CairanHelper::total($data, array_merge(CairanHelper::OUTPUT_26, ['output_iwl']));

        $kebutuhan = CairanHelper::kebutuhanCairan($beratBadan);
        $data['kebutuhan_cairan'] = $kebutuhan['teks'] ?? '';
        $data['balance_cairan'] = round((float) $data['total_intake'] - (float) $data['total_output'], 0);

        // Kumulatif 24 jam: dijumlahkan ulang setelah baris ini ikut tersimpan.
        $data['total_intake_24jam'] = CairanHelper::akumulasiIntakeHariIni(
            $formId,
            $registrasiDetailId,
            (string) ($data['tanggal_monitoring'] ?? ''),
            $emrId
        );

        // Flag abnormal (konvensi global: 1 = abnormal, 0 = normal, NULL = tidak dinilai).
        $flags['total_intake'] = $kebutuhan ? ((float) $data['total_intake'] > $kebutuhan['maks'] ? 1 : 0) : null;
        $flags['total_output'] = $kebutuhan ? ((float) $data['total_output'] < $kebutuhan['min'] * 0.5 ? 1 : 0) : null;
        $flags['balance_cairan'] = abs((float) $data['balance_cairan']) > 500 ? 1 : 0;

        return ['data' => $data, 'flags' => $flags];
    }
}
