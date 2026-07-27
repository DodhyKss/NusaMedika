<?php

namespace App\Http\Controllers\EMR\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrasiDetail;
use Illuminate\Support\Facades\Auth;

class SoapController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {

        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        
        // Form ID untuk SOAP biasanya 3
        $form_id = env('FORM_ID_SOAP');
        
        // Ambil riwayat SOAP untuk pasien ini (dari registrasi_id)
        $riwayat_soap = DB::table('emr')
            ->leftJoin('pegawai', 'emr.pegawai_id', '=', 'pegawai.pegawai_id')
            ->where('emr.registrasi_id', $registrasi_detail->registrasi_id)
            ->where('emr.form_id', $form_id)
            ->whereNull('emr.status_batal')
            ->select('emr.*', 'pegawai.nama_pegawai')
            ->orderBy('emr.tgl_jam', 'desc')
            ->paginate(5)
            ->withQueryString();

        // Ambil emr_detail untuk riwayat_soap yang sedang tampil (hindari N+1 query)
        $emr_ids = $riwayat_soap->pluck('emr_id')->toArray();
        $riwayat_details_raw = DB::table('emr_detail')
            ->whereIn('emr_id', $emr_ids)
            ->get();
            
        $riwayat_details = [];
        foreach ($riwayat_details_raw as $rd) {
            $riwayat_details[$rd->emr_id][$rd->objek_id] = $rd->value;
        }


        // ambil riwayat pengkajian
        $riwayat_pengkajian = [];

        // pengkajian awal keperawatan
        $emr_pengkajian_awal_keperawatan = DB::table('emr')
            ->where('registrasi_detail_id', $registrasi_detail_id)
            ->where('form_id', env('FORM_ID_PENGKAJIAN_AWAL_KEPERAWATAN'))
            ->whereNull('status_batal')
            ->orderBy('tgl_jam', 'desc')
            ->first();

        // pengkajian harian keperawatan
        $emr_pengkajian_harian_keperawatan = DB::table('emr')
            ->where('registrasi_detail_id', $registrasi_detail_id)
            ->where('form_id', env('FORM_ID_PENGKAJIAN_HARIAN_KEPERAWATAN'))
            ->whereNull('status_batal')
            ->orderBy('tgl_jam', 'desc')
            ->first();

        if ($emr_pengkajian_awal_keperawatan) {
            $riwayat_pengkajian = DB::table('emr_detail')
                ->where('emr_id', $emr_pengkajian_awal_keperawatan->emr_id)
                ->whereIn('objek_id', [
                    env('OBJEK_ID_SISTOLIK'),
                    env('OBJEK_ID_DIASTOLIK'),
                    env('OBJEK_ID_NADI'),
                    env('OBJEK_ID_SUHU'),
                    env('OBJEK_ID_PERNAPASAN'),
                    env('OBJEK_ID_BERAT_BADAN'),
                    env('OBJEK_ID_TINGGI_BADAN'),
                    env('OBJEK_ID_OKSIGEN'),
                    env('OBJEK_ID_CARA_PEMBERIAN'),
                    env('OBJEK_ID_ETT'),
                    env('OBJEK_ID_SATURASI'),
                    env('OBJEK_ID_EWS'),
                    env('OBJEK_ID_GCS_SCORE')
                ])
                ->whereNull('status_batal')
                ->pluck('value', 'objek_id');
        }

        if ($emr_pengkajian_harian_keperawatan) {
            $riwayat_pengkajian = DB::table('emr_detail')
                ->where('emr_id', $emr_pengkajian_harian_keperawatan->emr_id)
                ->whereIn('objek_id', [
                    env('OBJEK_ID_SISTOLIK'),
                    env('OBJEK_ID_DIASTOLIK'),
                    env('OBJEK_ID_NADI'),
                    env('OBJEK_ID_SUHU'),
                    env('OBJEK_ID_PERNAPASAN'),
                    env('OBJEK_ID_BERAT_BADAN'),
                    env('OBJEK_ID_TINGGI_BADAN'),
                    env('OBJEK_ID_OKSIGEN'),
                    env('OBJEK_ID_CARA_PEMBERIAN'),
                    env('OBJEK_ID_ETT'),
                    env('OBJEK_ID_SATURASI'),
                    env('OBJEK_ID_EWS'),
                    env('OBJEK_ID_GCS_SCORE')
                ])
                ->whereNull('status_batal')
                ->pluck('value', 'objek_id');
        }

        // Ambil assesment terakhir (dari riwayat SOAP paling baru)
        $assesment_terakhir = null;
        if ($riwayat_soap->isNotEmpty()) {
            $last_soap = $riwayat_soap->first();
            $assesment_terakhir = DB::table('emr_detail')
                ->where('emr_id', $last_soap->emr_id)
                ->where('objek_id', env('OBJEK_ID_ASSESSMENT'))
                ->value('value');
        }

        // Jika sedang edit, ambil data
        $edit_soap = null;
        $formData = [];
        if ($emr_id) {
            $edit_soap = DB::table('emr')
                ->where('emr_id', $emr_id)
                ->whereNull('status_batal')
                ->first();
                
            if ($edit_soap) {
                $details = DB::table('emr_detail')
                    ->where('emr_id', $edit_soap->emr_id)
                    ->pluck('value', 'objek_id')
                    ->toArray();
                
                $formData = [
                    's' => $details[env('OBJEK_ID_SUBJECTIVE')] ?? '',
                    'o' => $details[env('OBJEK_ID_OBJECTIVE')] ?? '',
                    'a' => $details[env('OBJEK_ID_ASSESSMENT')] ?? '',
                    'p' => $details[env('OBJEK_ID_PLANNING')] ?? '',
                    'i' => $details[env('OBJEK_ID_INSTRUKSI')] ?? ''
                ];

                // Fallback untuk data lama yang hanya tersimpan di JSON
                if (empty($details)) {
                    $jsonData = json_decode($edit_soap->data, true) ?? [];
                    $formData = [
                        's' => $jsonData['s'] ?? '',
                        'o' => $jsonData['o'] ?? '',
                        'a' => $jsonData['a'] ?? '',
                        'p' => $jsonData['p'] ?? '',
                        'i' => $jsonData['i'] ?? ''
                    ];
                }
            }
        }

        // Ambil riwayat seluruh kunjungan pasien (untuk dropdown History)
        $history_kunjungan = DB::table('registrasi_detail')
            ->join('registrasi', 'registrasi_detail.registrasi_id', '=', 'registrasi.registrasi_id')
            ->leftJoin('bagian', 'registrasi_detail.bagian_id', '=', 'bagian.bagian_id')
            ->where('registrasi.pasien_id', $registrasi_detail->registrasi->pasien_id)
            ->whereNull('registrasi.status_batal')
            ->whereNull('registrasi_detail.status_batal')
            ->select('registrasi.tgl_masuk', 'bagian.nama_bagian', 'registrasi_detail.registrasi_detail_id')
            ->orderBy('registrasi.tgl_masuk', 'desc')
            ->get();

        $history_grouped = [];
        foreach ($history_kunjungan as $hk) {
            $date = date('Y-m-d', strtotime($hk->tgl_masuk));
            if (!isset($history_grouped[$date])) {
                $history_grouped[$date] = [];
            }
            if ($hk->nama_bagian) {
                // Gunakan nama_bagian sebagai key array untuk mencegah duplikasi di hari yang sama
                $history_grouped[$date][$hk->nama_bagian] = $hk->registrasi_detail_id;
            }
        }

        $isView = request('action') === 'view';

        return view('moduls.emr.soap.index', compact('registrasi_detail', 'riwayat_soap', 'riwayat_details', 'edit_soap', 'formData', 'form_id', 'history_grouped', 'riwayat_pengkajian', 'assesment_terakhir', 'isView'));
    }

    public function print($emr_id)
    {
        return view('moduls.emr.soap.print', compact('emr_id'));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'instruction' => 'nullable|string'
        ]);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);
        $user_id = Auth::user()->user_id;
        $pegawai_id = Auth::user()->pegawai_id;
        $form_id = env('FORM_ID_SOAP');
        $now = now();

        // jika pegawai_id atau user_id null, maka redirect ke halaman login
        if($pegawai_id == null || $user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        DB::beginTransaction();
        try {
            $emr_id = \App\Helpers\SequenceHelper::getNextId('emr');

            // Insert ke tabel emr
            DB::table('emr')->insert([
                'emr_id' => $emr_id,
                'form_id' => $form_id,
                'pegawai_id' => $pegawai_id,
                'tgl_jam' => $now,
                'registrasi_detail_id' => $registrasi_detail_id,
                'pasien_id' => $registrasi_detail->registrasi->pasien_id,
                'registrasi_id' => $registrasi_detail->registrasi_id,
                'input_time' => $now,
                'input_user_id' => $user_id,
            ]);

            // Insert ke tabel emr_detail (S, O, A, P, I)
            $details = [
                ['objek_id' => env('OBJEK_ID_SUBJECTIVE'), 'variabel' => 'subjective', 'value' => $request->subjective],
                ['objek_id' => env('OBJEK_ID_OBJECTIVE'), 'variabel' => 'objective', 'value' => $request->objective],
                ['objek_id' => env('OBJEK_ID_ASSESSMENT'), 'variabel' => 'assessment', 'value' => $request->assessment],
                ['objek_id' => env('OBJEK_ID_PLANNING'), 'variabel' => 'planning', 'value' => $request->plan],
                ['objek_id' => env('OBJEK_ID_INSTRUKSI'), 'variabel' => 'instruksi', 'value' => $request->instruction],
            ];

            foreach ($details as $d) {
                $emr_detail_id = \App\Helpers\SequenceHelper::getNextId('emr_detail');
                DB::table('emr_detail')->insert([
                    'emr_detail_id' => $emr_detail_id,
                    'emr_id' => $emr_id,
                    'objek_id' => $d['objek_id'],
                    'variabel' => $d['variabel'],
                    'value' => $d['value'],
                    'input_time' => $now,
                    'input_user_id' => $user_id,
                ]);
            }

            DB::commit();
            return redirect()->route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan SOAP: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'instruction' => 'nullable|string',
        ]);

        $user_id = Auth::user()->user_id;
        $now = now();

        DB::beginTransaction();
        try {
            // Update emr
            DB::table('emr')
                ->where('emr_id', $emr_id)
                ->update([
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            // Delete existing details and re-insert
            DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->update(['status_batal' => 1, 'mod_time' => $now, 'mod_user_id' => $user_id]);

            // Insert new details
            $details = [
                ['objek_id' => env('OBJEK_ID_SUBJECTIVE'), 'variabel' => 'subjective', 'value' => $request->subjective],
                ['objek_id' => env('OBJEK_ID_OBJECTIVE'), 'variabel' => 'objective', 'value' => $request->objective],
                ['objek_id' => env('OBJEK_ID_ASSESSMENT'), 'variabel' => 'assessment', 'value' => $request->assessment],
                ['objek_id' => env('OBJEK_ID_PLANNING'), 'variabel' => 'planning', 'value' => $request->plan],
                ['objek_id' => env('OBJEK_ID_INSTRUKSI'), 'variabel' => 'instruksi', 'value' => $request->instruction],
            ];

            foreach ($details as $d) {
                $emr_detail_id = \App\Helpers\SequenceHelper::getNextId('emr_detail');
                DB::table('emr_detail')->insert([
                    'emr_detail_id' => $emr_detail_id,
                    'emr_id' => $emr_id,
                    'objek_id' => $d['objek_id'],
                    'variabel' => $d['variabel'],
                    'value' => $d['value'],
                    'input_time' => $now,
                    'input_user_id' => $user_id,
                ]);
            }

            DB::commit();
            return redirect()->route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui SOAP: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $user_id = Auth::user()->user_id;
        $now = now();

        DB::beginTransaction();
        try {
            DB::table('emr')
                ->where('emr_id', $emr_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            DB::commit();
            return redirect()->route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail_id])->with('success', 'SOAP berhasil dibatalkan');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal membatalkan SOAP: ' . $e->getMessage());
        }
    }
}
