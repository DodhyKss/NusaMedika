<?php

namespace App\Http\Controllers\EMR\KalaPersalinan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Kala I-IV (form 62, slug `kala_persalinan`).
 *
 * Observasi per kala persalinan; satu baris kala = satu record `emr`.
 * Server menolak `kala` yang sama terisi 2x pada `registrasi_detail_id` yang
 * sama (kecuali saat update) dan menolak `tanggal_kala` lebih tua dari
 * `tanggal_pengkajian` form 60 bila sudah ada. Panel APGAR menit 1 & 5
 * ditampilkan read-only dari form 66 (tidak disimpan ganda).
 */
class KalaPersalinanController extends Controller
{
    private const SLUG = 'kala_persalinan';

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

        // APGAR menit 1 & 5 (read-only) dari form 66 pada kunjungan yang sama.
        $apgarFormId = EmrHelper::formIdBySlug('penilaian_bayi_dengan_apgar_score');
        $apgar = $apgarFormId ? EmrHelper::latestValuesByVariabel(
            (int) $apgarFormId,
            (int) $registrasi_detail_id,
            ['apgar_total_1', 'apgar_total_5']
        ) : [];

        return view('moduls.EMR.KalaPersalinan.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud', 'apgar'
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

        $data = $this->validated($request, (int) $form_id, (int) $registrasi_detail_id, null);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Kala Persalinan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Kala Persalinan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id, (int) $registrasi_detail_id, (int) $emr_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Kala Persalinan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Kala Persalinan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Kala Persalinan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Kala Persalinan: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $data = [
            'kala' => '', 'tanggal_kala' => now()->format('Y-m-d'), 'jam_kala' => now()->format('H:i'),
            'pemeriksaan_dalam' => '', 'his' => '', 'djj' => '',
        ];
        for ($k = 1; $k <= 4; $k++) {
            $data['masalah_kala_'.$k] = '';
            $data['masalah_kala_detail_'.$k] = '';
            $data['penatalaksanaan_kala_'.$k] = '';
        }

        return $data;
    }

    private function validated(Request $request, int $formId, int $registrasiDetailId, ?int $emrId): array
    {
        $validator = \Validator::make($request->all(), [
            'kala' => 'required|in:1,2,3,4',
            'tanggal_kala' => 'required|date|before_or_equal:today',
            'jam_kala' => 'required|date_format:H:i',
            'pemeriksaan_dalam' => 'required_if:kala,1|nullable|string|max:2000',
            'his' => 'nullable|string|max:2000',
            'djj' => 'nullable|string|max:255',
            'masalah_kala_1' => 'required|in:Tidak,Ya',
            'masalah_kala_2' => 'required|in:Tidak,Ya',
            'masalah_kala_3' => 'required|in:Tidak,Ya',
            'masalah_kala_4' => 'required|in:Tidak,Ya',
            'masalah_kala_detail_1' => 'required_if:masalah_kala_1,Ya|nullable|string|max:255',
            'masalah_kala_detail_2' => 'required_if:masalah_kala_2,Ya|nullable|string|max:255',
            'masalah_kala_detail_3' => 'required_if:masalah_kala_3,Ya|nullable|string|max:255',
            'masalah_kala_detail_4' => 'required_if:masalah_kala_4,Ya|nullable|string|max:255',
            'penatalaksanaan_kala_1' => 'nullable|string|max:255',
            'penatalaksanaan_kala_2' => 'nullable|string|max:255',
            'penatalaksanaan_kala_3' => 'nullable|string|max:255',
            'penatalaksanaan_kala_4' => 'nullable|string|max:255',
        ]);

        $validator->after(function ($validator) use ($request, $formId, $registrasiDetailId, $emrId) {
            // Satu kala hanya boleh sekali per kunjungan.
            $kala = $request->input('kala');
            if ($kala !== null && $kala !== '') {
                $duplikat = DB::table('emr')
                    ->where('form_id', $formId)
                    ->where('registrasi_detail_id', $registrasiDetailId)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->when($emrId, fn ($q) => $q->where('emr_id', '!=', $emrId))
                    ->whereExists(function ($q) use ($kala) {
                        $q->select(DB::raw(1))
                            ->from('emr_detail')
                            ->whereColumn('emr_detail.emr_id', 'emr.emr_id')
                            ->where('emr_detail.variabel', 'kala')
                            ->where('emr_detail.value', $kala)
                            ->where(function ($qq) {
                                $qq->whereNull('emr_detail.status_batal')->orWhere('emr_detail.status_batal', 0);
                            });
                    })
                    ->exists();
                if ($duplikat) {
                    $validator->errors()->add('kala', 'Kala '.$kala.' sudah pernah diisikan pada kunjungan ini.');
                }
            }

            // Tanggal kala tidak boleh lebih tua dari pengkajian perina (form 60).
            $perinaFormId = EmrHelper::formIdBySlug('pengkajian_perina');
            if ($perinaFormId) {
                $tgl = EmrHelper::latestValuesByVariabel((int) $perinaFormId, $registrasiDetailId, ['tanggal_pengkajian']);
                $tglPengkajian = $tgl['tanggal_pengkajian'] ?? null;
                $tglKala = $request->input('tanggal_kala');
                if ($tglPengkajian && $tglKala && $tglKala < $tglPengkajian) {
                    $validator->errors()->add('tanggal_kala', 'Tanggal kala tidak boleh lebih tua dari tanggal pengkajian perina.');
                }
            }
        });
        $validator->validate();

        $data = array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());

        // Field kondisional: hanya kala I yang punya pemeriksaan dalam / HIS / DJJ.
        if (($data['kala'] ?? '') !== '1') {
            $data['pemeriksaan_dalam'] = null;
            $data['his'] = null;
            $data['djj'] = null;
        }
        // Masalah "Tidak" -> detail & penatalaksanaan dikosongkan.
        for ($k = 1; $k <= 4; $k++) {
            if (($data['masalah_kala_'.$k] ?? '') !== 'Ya') {
                $data['masalah_kala_detail_'.$k] = null;
                $data['penatalaksanaan_kala_'.$k] = null;
            }
        }

        return $data;
    }
}
