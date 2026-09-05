<?php

namespace App\Http\Controllers\EMR\Soap;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoapController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        // Form ID SOAP: dari tabel form (slug 'soap'), bukan env('FORM_ID_SOAP')
        $form_id = EmrHelper::formIdBySlug('soap');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        // Ambil riwayat SOAP untuk pasien ini (dari registrasi_id)
        $riwayat_soap = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        if (!$emr_id && !($aksesCrud['create'] ?? false) && $riwayat_soap->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => 'soap',
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat_soap->first()->emr_id,
                'action' => 'view'
            ]);
        }

        // ambil riwayat pengkajian (vital sign terakhir dari pengkajian awal/harian)
        $riwayat_pengkajian = [];
        $pengkajian_variabels = [
            'sistolik', 'diastolik', 'nadi', 'suhu', 'pernapasan', 'berat_badan',
            'tinggi_badan', 'oksigen', 'cara_pemberian', 'ett', 'saturasi', 'ews', 'gcs_score',
        ];

        // pengkajian awal keperawatan
        $form_pengkajian_awal = EmrHelper::formIdBySlug('pengkajian_awal_keperawatan');
        if ($form_pengkajian_awal && EmrHelper::latestEmr((int) $form_pengkajian_awal, (int) $registrasi_detail_id)) {
            $riwayat_pengkajian = EmrHelper::latestValuesByVariabel((int) $form_pengkajian_awal, (int) $registrasi_detail_id, $pengkajian_variabels);
        }

        // pengkajian harian keperawatan (menang bila ada)
        $form_pengkajian_harian = EmrHelper::formIdBySlug('pengkajian_harian_keperawatan');
        if ($form_pengkajian_harian && EmrHelper::latestEmr((int) $form_pengkajian_harian, (int) $registrasi_detail_id)) {
            $riwayat_pengkajian = EmrHelper::latestValuesByVariabel((int) $form_pengkajian_harian, (int) $registrasi_detail_id, $pengkajian_variabels);
        }

        // Ambil assesment terakhir (dari riwayat SOAP paling baru)
        $assesment_terakhir = null;
        if ($riwayat_soap->isNotEmpty()) {
            $last_soap = $riwayat_soap->first();
            $assesment_terakhir = EmrHelper::emrDetailByVariabel((int) $last_soap->emr_id)['assessment'] ?? null;
        }

        // Jika sedang edit, ambil data
        $edit_soap = null;
        $formData = [];
        if ($emr_id) {
            $edit_soap = EmrHelper::emrById((int) $emr_id);

            if ($edit_soap) {
                $details = EmrHelper::emrDetailByVariabel((int) $edit_soap->emr_id);

                $formData = [
                    's' => $details['subjective'] ?? '',
                    'o' => $details['objective'] ?? '',
                    'a' => $details['assessment'] ?? '',
                    'p' => $details['planning'] ?? '',
                    'i' => $details['instruksi'] ?? '',
                ];

                // Fallback untuk data lama yang hanya tersimpan di JSON
                if (empty($details)) {
                    $jsonData = json_decode($edit_soap->data, true) ?? [];
                    $formData = [
                        's' => $jsonData['s'] ?? '',
                        'o' => $jsonData['o'] ?? '',
                        'a' => $jsonData['a'] ?? '',
                        'p' => $jsonData['p'] ?? '',
                        'i' => $jsonData['i'] ?? '',
                    ];
                }
            }
        }

        // Ambil riwayat seluruh kunjungan pasien (untuk dropdown History)
        $history_kunjungan = DB::table('registrasi_detail')
            ->join('registrasi', 'registrasi_detail.registrasi_id', '=', 'registrasi.registrasi_id')
            ->leftJoin('bagian', function ($join) {
                $join->on('registrasi_detail.bagian_id', '=', 'bagian.bagian_id')
                    ->where(function ($q) {
                        $q->whereNull('bagian.status_batal')->orWhere('bagian.status_batal', 0);
                    });
            })
            ->where('registrasi.pasien_id', $registrasi_detail->registrasi->pasien_id)
            ->where(function ($q) {
                $q->whereNull('registrasi.status_batal')->orWhere('registrasi.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('registrasi_detail.status_batal')->orWhere('registrasi_detail.status_batal', 0);
            })
            ->select('registrasi.tgl_masuk', 'bagian.nama_bagian', 'registrasi_detail.registrasi_detail_id')
            ->orderBy('registrasi.tgl_masuk', 'desc')
            ->get();

        $history_grouped = [];
        foreach ($history_kunjungan as $hk) {
            $date = date('Y-m-d', strtotime($hk->tgl_masuk));
            if (! isset($history_grouped[$date])) {
                $history_grouped[$date] = [];
            }
            if ($hk->nama_bagian) {
                // Gunakan nama_bagian sebagai key array untuk mencegah duplikasi di hari yang sama
                $history_grouped[$date][$hk->nama_bagian] = $hk->registrasi_detail_id;
            }
        }

        $isView = request('action') === 'view';

        return view('moduls.EMR.Soap.index', compact('registrasi_detail', 'edit_soap', 'formData', 'form_id', 'history_grouped', 'riwayat_pengkajian', 'assesment_terakhir', 'isView', 'aksesCrud'));
    }

    public function print($emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('soap');
        abort_unless($form_id, 404);

        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        return view('moduls.EMR.Soap.print', compact('emr_id'));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug('soap');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'instruction' => 'nullable|string',
        ]);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);

        // jika pegawai_id atau user_id null, maka redirect ke halaman login
        $user = Auth::user();
        if ($user == null || $user->pegawai_id == null || $user->user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->soapData($request), (int) $registrasi_detail_id);

            return redirect()->route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil disimpan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan SOAP: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('soap');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'instruction' => 'nullable|string',
        ]);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->soapData($request));

            return redirect()->route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui SOAP: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('soap');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil dibatalkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan SOAP: '.$e->getMessage());
        }
    }

    /**
     * Petakan field form SOAP (s/o/a/p/i) ke variabel yang tersimpan di emr_detail
     * (subjective, objective, assessment, planning, instruksi).
     */
    private function soapData(Request $request): array
    {
        return [
            'subjective' => $request->input('subjective'),
            'objective' => $request->input('objective'),
            'assessment' => $request->input('assessment'),
            'planning' => $request->input('plan'),
            'instruksi' => $request->input('instruction'),
        ];
    }
}
