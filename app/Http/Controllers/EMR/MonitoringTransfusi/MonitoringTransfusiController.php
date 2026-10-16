<?php

namespace App\Http\Controllers\EMR\MonitoringTransfusi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Form Monitoring Transfusi Darah (form 110, slug `monitoring_transfusi`).
 *
 * Pemantauan reaksi transfusi per ronde (KONSEP_ONKOLOGI_TRANSFUSI §7).
 * **Time-series berbasis `flag_abnormal`** — sama seperti Observasi Restraint
 * (form 122): indeks ronde disimpan di `emr_detail.flag_abnormal`, sehingga
 * pembacaan WAJIB memakai `EmrHelper::emrDetailRonde()` (grouping per ronde).
 * `emrDetailByVariabel()` DILARANG di form ini — pluck per variabel menimpa
 * baris antar ronde.
 *
 * Deviasi terdokumentasi: payload FLAT (bukan `ronde.N.*` bersarang) —
 * `emr_detail` tidak mengenal struktur bersarang, dan flat menyederhanakan
 * validasi sekaligus harness E2E.
 *
 * Aturan yang ditegakkan SERVER:
 * 1. `darah_masuk` ronde N harus >= ronde N-1 (kumulatif).
 * 2. `jumlah_ronde` TIDAK dimapping & tanpa rule (turunan navigasi view).
 */
class MonitoringTransfusiController extends Controller
{
    private const SLUG = 'monitoring_transfusi';

    /** Jumlah item gejala & tanda reaksi transfusi. */
    public const MAKS_GEJALA = 10;

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

        $rondes = [];
        $rondeAktif = 1;

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong());
            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            // Pembacaan time-series: grouping per flag_abnormal — JANGAN
            // emrDetailByVariabel() (baris antar ronde saling menimpa).
            $rondes = EmrHelper::emrDetailRonde((int) $emr_id);

            $rondeRequest = (int) request('ronde', 0);
            $rondeAktif = $rondeRequest > 0 ? $rondeRequest : (int) (max(array_keys($rondes)) ?: 1);

            $emr_data = EmrHelper::wrapData($rondes[$rondeAktif] ?? $this->kosong());
            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        $rondeBaru = $rondeAktif + 1;

        return view('moduls.EMR.MonitoringTransfusi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'rondes', 'rondeAktif', 'rondeBaru'
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

            // EmrHelper::insert belum mengisi flag_abnormal — ronde pertama
            // ditulis di sini (indeks ronde = flag_abnormal).
            $emrId = DB::transaction(function () use ($form_id, $data, $registrasi_detail_id) {
                $id = EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);
                DB::table('emr_detail')
                    ->where('emr_id', $id)
                    ->whereNull('flag_abnormal')
                    ->update(['flag_abnormal' => 1]);

                return $id;
            });

            return redirect()->back()->with('success', 'Monitoring Transfusi Darah berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Monitoring Transfusi Darah: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        // Nomor ronde datang dari navigasi view (default 1). Bila ronde berikut
        // belum ada, "Tambah Ronde" membuka URL update dgn ronde baru → payload
        // kosong → sisipkan ronde baru tanpa menyentuh ronde lama.
        $ronde = max(1, (int) $request->input('ronde', 1));

        try {
            $data = $this->validated($request, (int) $form_id, (int) $emr_id, $ronde);

            DB::transaction(function () use ($emr_id, $form_id, $data, $ronde) {
                EmrHelper::hapusRonde((int) $emr_id, $ronde);
                EmrHelper::insertRonde((int) $emr_id, (int) $form_id, $data, $ronde);
            });

            return redirect()->back()->with('success', 'Monitoring Transfusi Darah (Ronde '.$ronde.') berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Monitoring Transfusi Darah: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Monitoring Transfusi Darah berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Monitoring Transfusi Darah: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id, ?int $emr_id = null, int $ronde = 1): array
    {
        // Payload FLAT (deviasi terdokumentasi dari bentuk `ronde.N.*` KONSEP).
        $rules = [
            'tanggal_evaluasi' => 'required|date',
            'jam' => 'required|date_format:H:i',
            'no_labu_darah' => 'required|string|max:50',
            'darah_masuk' => 'required|integer|min:0|max:10000',
            'komponen_darah' => 'required|in:Whole Blood,Packed Red Cell,Plasma,Platelet,Cryoprecipitate',
            'sistolik' => 'required|integer|min:50|max:300',
            'diastolik' => 'required|integer|min:20|max:200',
            'nadi' => 'required|integer|min:20|max:250',
            'pernapasan' => 'required|integer|min:5|max:80',
            'suhu' => 'required|numeric|min:30|max:45',
            'tindakan' => 'required|string|max:1000',
            'keluhan' => 'required|string|max:500',
            'skala_nyeri' => 'nullable|integer|min:0|max:10',
        ];

        // Loop dibaca harness E2E via expandRuleLoops() — bentuk WAJIB
        // $rules['prefix_'.$i] = '...'.
        for ($i = 1; $i <= self::MAKS_GEJALA; $i++) {
            $rules['gejala_'.$i] = 'nullable|boolean';
            $rules['tanda_'.$i] = 'nullable|boolean';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request, $emr_id, $ronde) {
            // `darah_masuk` kumulatif: ronde berikut tidak boleh lebih kecil
            // dari ronde sebelumnya (banding DB; inert untuk ronde 1).
            if (! $emr_id || $ronde <= 1) {
                return;
            }

            $sebelum = DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->where('flag_abnormal', $ronde - 1)
                ->where('variabel', 'darah_masuk')
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->value('value');

            if ($sebelum !== null && (int) $request->input('darah_masuk', 0) < (int) $sebelum) {
                $validator->errors()->add('darah_masuk', "Darah masuk ronde {$ronde} tidak boleh kurang dari ronde sebelumnya (nilai kumulatif).");
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
        $d = [
            'tanggal_evaluasi' => '', 'jam' => '', 'no_labu_darah' => '',
            'darah_masuk' => '', 'komponen_darah' => '', 'tindakan' => '', 'keluhan' => '',
            'skala_nyeri' => null,
            'sistolik' => '', 'diastolik' => '', 'nadi' => '', 'pernapasan' => '', 'suhu' => '',
        ];

        for ($i = 1; $i <= self::MAKS_GEJALA; $i++) {
            $d['gejala_'.$i] = null;
            $d['tanda_'.$i] = null;
        }

        return $d;
    }
}
