<?php

namespace App\Http\Controllers\EMR\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrasiDetail;

class SoapController extends Controller
{
    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        
        // Form ID untuk SOAP biasanya 3
        $form_id = env('FORM_ID_SOAP', 3);
        
        // Ambil riwayat SOAP untuk pasien ini (dari registrasi_id)
        $riwayat_soap = DB::table('emr')
            ->leftJoin('pegawai', 'emr.pegawai_id', '=', 'pegawai.pegawai_id')
            ->where('emr.registrasi_id', $registrasi_detail->registrasi_id)
            ->where('emr.form_id', $form_id)
            ->whereNull('emr.status_batal')
            ->select('emr.*', 'pegawai.nama_pegawai')
            ->orderBy('emr.tgl_jam', 'desc')
            ->get();
            
        // Jika sedang edit, ambil data
        $edit_soap = null;
        if ($emr_id) {
            $edit_soap = DB::table('emr')
                ->where('emr_id', $emr_id)
                ->whereNull('status_batal')
                ->first();
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

        return view('moduls.emr.soap.index', compact('registrasi_detail', 'riwayat_soap', 'edit_soap', 'form_id', 'history_grouped'));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $request->validate([
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
        ]);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);
        $user_id = session('user_id', 1);
        $pegawai_id = session('pegawai_id', 1);
        $form_id = env('FORM_ID_SOAP', 3);
        $now = now();

        $data_json = json_encode([
            's' => $request->subjective,
            'o' => $request->objective,
            'a' => $request->assessment,
            'p' => $request->plan,
        ]);

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
                'data' => $data_json,
                'input_time' => $now,
                'input_user_id' => $user_id,
            ]);

            // Insert ke tabel emr_detail (S, O, A, P)
            $details = [
                ['objek_id' => null, 'variabel' => 'Subjective', 'value' => $request->subjective],
                ['objek_id' => null, 'variabel' => 'Objective', 'value' => $request->objective],
                ['objek_id' => null, 'variabel' => 'Assessment', 'value' => $request->assessment],
                ['objek_id' => null, 'variabel' => 'Plan', 'value' => $request->plan],
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
        ]);

        $user_id = session('user_id', 1);
        $now = now();

        $data_json = json_encode([
            's' => $request->subjective,
            'o' => $request->objective,
            'a' => $request->assessment,
            'p' => $request->plan,
        ]);

        DB::beginTransaction();
        try {
            // Update emr
            DB::table('emr')
                ->where('emr_id', $emr_id)
                ->update([
                    'data' => $data_json,
                    'mod_time' => $now,
                    'mod_user_id' => $user_id,
                ]);

            // Delete existing details and re-insert
            DB::table('emr_detail')
                ->where('emr_id', $emr_id)
                ->update(['status_batal' => 1, 'mod_time' => $now, 'mod_user_id' => $user_id]);

            // Insert new details
            $details = [
                ['variabel' => 'Subjective', 'value' => $request->subjective],
                ['variabel' => 'Objective', 'value' => $request->objective],
                ['variabel' => 'Assessment', 'value' => $request->assessment],
                ['variabel' => 'Plan', 'value' => $request->plan],
            ];

            foreach ($details as $d) {
                $emr_detail_id = \App\Helpers\SequenceHelper::getNextId('emr_detail');
                DB::table('emr_detail')->insert([
                    'emr_detail_id' => $emr_detail_id,
                    'emr_id' => $emr_id,
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
        $user_id = session('user_id', 1);
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
