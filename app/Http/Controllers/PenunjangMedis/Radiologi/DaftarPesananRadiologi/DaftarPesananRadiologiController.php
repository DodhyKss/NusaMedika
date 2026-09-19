<?php

namespace App\Http\Controllers\PenunjangMedis\Radiologi\DaftarPesananRadiologi;

use App\Helpers\PenunjangHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaftarPesananRadiologiController extends Controller
{
    private const JENIS = 'rad';

    private const SESSION_BAGIAN = 'rad_bagian_id';

    public function index(Request $request)
    {
        $bagianList = Bagian::aktif()
            ->where('referensi_bagian_id', 6)
            ->orderBy('nama_bagian')
            ->get();

        $bagianTujuanId = (int) session(self::SESSION_BAGIAN, 0);
        if ($bagianTujuanId && ! $bagianList->contains('bagian_id', $bagianTujuanId)) {
            $bagianTujuanId = 0;
        }

        $tglAwal = $request->input('tgl_awal');
        $tglAkhir = $request->input('tgl_akhir');
        $jenisRawat = $request->input('jenis_rawat');
        $noOrder = $request->input('no_order');
        $pasienId = $request->input('pasien_id');
        $status = $request->input('status');

        $hasFilter = $tglAwal || $tglAkhir || $jenisRawat || $noOrder || $pasienId || ($status !== null && $status !== '');

        $query = PenunjangHelper::daftarOrder(self::JENIS, $bagianTujuanId ?: null);

        if ($hasFilter) {
            if ($tglAwal !== null && $tglAwal !== '') {
                $query->whereDate('order_radiologi.tanggal_order', '>=', $tglAwal);
            }

            if ($tglAkhir !== null && $tglAkhir !== '') {
                $query->whereDate('order_radiologi.tanggal_order', '<=', $tglAkhir);
            }

            if ($jenisRawat !== null && $jenisRawat !== '') {
                $query->whereHas('registrasiDetail.registrasi', function ($rq) use ($jenisRawat) {
                    $rq->where('jenis_rawat', $jenisRawat);
                });
            }

            if ($noOrder !== null && $noOrder !== '') {
                $query->where('order_radiologi.no_order', 'like', '%'.$noOrder.'%');
            }

            if ($pasienId !== null && $pasienId !== '') {
                $query->where('order_radiologi.pasien_id', (int) $pasienId);
            }

            if ($status !== null && $status !== '') {
                $query->where('order_radiologi.status_order', (int) $status);
            }

            $orderList = $query->orderByDesc('order_radiologi.order_radiologi_id')->paginate(10)->withQueryString();
        } else {
            $orderList = null;
        }

        return view('moduls.PenunjangMedis.Radiologi.DaftarPesananRadiologi.daftar_pesanan_radiologi', compact(
            'bagianList',
            'bagianTujuanId',
            'orderList',
            'tglAwal',
            'tglAkhir',
            'jenisRawat',
            'noOrder',
            'pasienId',
            'status'
        ));
    }

    public function pilihBagian(Request $request)
    {
        $request->validate([
            'bagian_tujuan_id' => 'required|integer|exists:bagian,bagian_id',
        ]);

        $bagian = Bagian::aktif()
            ->where('bagian_id', $request->input('bagian_tujuan_id'))
            ->where('referensi_bagian_id', 6)
            ->first();

        if (! $bagian) {
            return back()->with('error', 'Bagian radiologi tidak valid.');
        }

        session()->put(self::SESSION_BAGIAN, (int) $bagian->bagian_id);

        return redirect()->route('daftar_pesanan_radiologi.index')->with('success', 'Bagian aktif: '.$bagian->nama_bagian.'.');
    }

    public function detail($orderRadiologi)
    {
        $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderRadiologi);

        return view('moduls.PenunjangMedis.Radiologi.DaftarPesananRadiologi.detail', compact('order'));
    }

    public function terima($orderRadiologi)
    {
        try {
            PenunjangHelper::terima(self::JENIS, (int) $orderRadiologi, $this->pegawaiId());

            return redirect()->route('daftar_pesanan_radiologi.detail', $orderRadiologi)
                ->with('success', 'Order Radiologi diterima.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menerima order: '.$e->getMessage());
        }
    }

    public function simpanHasil(Request $request, $orderRadiologi)
    {
        $hasilItems = [];
        $hasils = (array) $request->input('hasil', []);
        $flags = (array) $request->input('flag_abnormal', []);

        foreach ($hasils as $detailId => $hasil) {
            $detailId = (int) $detailId;
            if ($detailId <= 0) {
                continue;
            }

            $hasilItems[$detailId] = [
                'hasil' => is_string($hasil) ? trim($hasil) : null,
                'flag_abnormal' => array_key_exists($detailId, $flags) ? 1 : 0,
            ];
        }

        try {
            PenunjangHelper::simpanHasil(self::JENIS, (int) $orderRadiologi, $hasilItems, $this->pegawaiId());

            return redirect()->route('daftar_pesanan_radiologi.detail', $orderRadiologi)
                ->with('success', 'Hasil radiologi berhasil disimpan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan hasil: '.$e->getMessage());
        }
    }

    public function selesai($orderRadiologi)
    {
        try {
            PenunjangHelper::finalisasi(self::JENIS, (int) $orderRadiologi, $this->pegawaiId());
            $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderRadiologi);

            return redirect()->route('daftar_pesanan_radiologi.detail', $orderRadiologi)
                ->with('success', 'Order '.$order->no_order.' diselesaikan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyelesaikan order: '.$e->getMessage());
        }
    }

    public function batal($orderRadiologi)
    {
        try {
            PenunjangHelper::batalkan(self::JENIS, (int) $orderRadiologi);
            $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderRadiologi);

            return redirect()->route('daftar_pesanan_radiologi.index')
                ->with('success', 'Order '.$order->no_order.' dibatalkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order: '.$e->getMessage());
        }
    }

    public function cetak($orderRadiologi)
    {
        $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderRadiologi);

        return view('moduls.PenunjangMedis.Radiologi.DaftarPesananRadiologi.cetak', compact('order'));
    }

    private function pegawaiId(): ?int
    {
        return Auth::user()->pegawai_id ?? null;
    }
}
