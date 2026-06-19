<?php

namespace App\Http\Controllers\Registrasi\Pendaftaran\ListPelayanan;

use App\Http\Controllers\Controller;
use App\Models\Registrasi;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListPelayananController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $jenisLayanan = $request->input('jenis_layanan');
        $pasienId = $request->input('pasien_id');

        $query = Registrasi::with([
            'pasien',
            'registrasiDetails' => function($q) {
                $q->whereNull('status_batal')->with(['bagian', 'billTemp']);
            },
            'rujukanSep',
            'pasienNasabah.nasabah',
            'penanggungRawat.user'
        ])
        ->whereNull('status_batal');

        // Filter Tanggal
        if ($tanggalAwal) {
            $query->whereDate('tgl_masuk', '>=', $tanggalAwal);
        }
        if ($tanggalAkhir) {
            $query->whereDate('tgl_masuk', '<=', $tanggalAkhir);
        }

        // Filter Jenis Layanan
        if ($jenisLayanan) {
            $query->where('jenis_rawat', $jenisLayanan);
        }

        // Filter Pasien
        if ($pasienId) {
            $query->where('pasien_id', $pasienId);
        }

        $kunjungan = $query->orderBy('tgl_masuk', 'desc')->paginate(15)->withQueryString();

        return view('moduls.registrasi.pendaftaran.list_pelayanan.list_pelayanan_pasien', compact(
            'kunjungan', 'tanggalAwal', 'tanggalAkhir', 'jenisLayanan', 'pasienId'
        ));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $registrasi = Registrasi::findOrFail($id);
            $registrasi->status_batal = 1;
            $registrasi->save();

            // Cancel details
            foreach ($registrasi->registrasiDetails as $detail) {
                $detail->status_batal = 1;
                $detail->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Layanan berhasil dihapus/dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan layanan: ' . $e->getMessage());
        }
    }
}
