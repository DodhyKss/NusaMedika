<?php

namespace App\Http\Controllers\PenunjangMedis\Laboratorium\DaftarPesananLaboratorium;

use App\Helpers\PenunjangHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DaftarPesananLaboratoriumController extends Controller
{
    private const JENIS = 'lab';

    private const SESSION_BAGIAN = 'lab_bagian_id';

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
                $query->whereDate('order_laboratorium.tanggal_order', '>=', $tglAwal);
            }

            if ($tglAkhir !== null && $tglAkhir !== '') {
                $query->whereDate('order_laboratorium.tanggal_order', '<=', $tglAkhir);
            }

            if ($jenisRawat !== null && $jenisRawat !== '') {
                $query->whereHas('registrasiDetail.registrasi', function ($rq) use ($jenisRawat) {
                    $rq->where('jenis_rawat', $jenisRawat);
                });
            }

            if ($noOrder !== null && $noOrder !== '') {
                $query->where('order_laboratorium.no_order', 'like', '%'.$noOrder.'%');
            }

            if ($pasienId !== null && $pasienId !== '') {
                $query->where('order_laboratorium.pasien_id', (int) $pasienId);
            }

            if ($status !== null && $status !== '') {
                $query->where('order_laboratorium.status_order', (int) $status);
            }

            $orderList = $query->orderByDesc('order_laboratorium.order_laboratorium_id')->paginate(10)->withQueryString();
        } else {
            $orderList = null;
        }

        return view('moduls.PenunjangMedis.Laboratorium.DaftarPesananLaboratorium.daftar_pesanan_laboratorium', compact(
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
            return back()->with('error', 'Bagian laboratorium tidak valid.');
        }

        session()->put(self::SESSION_BAGIAN, (int) $bagian->bagian_id);

        return redirect()->route('daftar_pesanan_laboratorium.index')->with('success', 'Bagian aktif: '.$bagian->nama_bagian.'.');
    }

    public function detail($orderLaboratorium)
    {
        $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderLaboratorium);

        return view('moduls.PenunjangMedis.Laboratorium.DaftarPesananLaboratorium.detail', compact('order'));
    }

    public function terima($orderLaboratorium)
    {
        try {
            PenunjangHelper::terima(self::JENIS, (int) $orderLaboratorium, $this->pegawaiId());

            return redirect()->route('daftar_pesanan_laboratorium.detail', $orderLaboratorium)
                ->with('success', 'Order Laboratorium diterima.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menerima order: '.$e->getMessage());
        }
    }

    public function simpanHasil(Request $request, $orderLaboratorium)
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
            PenunjangHelper::simpanHasil(self::JENIS, (int) $orderLaboratorium, $hasilItems, $this->pegawaiId());

            return redirect()->route('daftar_pesanan_laboratorium.detail', $orderLaboratorium)
                ->with('success', 'Hasil laboratorium berhasil disimpan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan hasil & mengirim: '.$e->getMessage());
        }
    }

    public function selesai($orderLaboratorium)
    {
        try {
            PenunjangHelper::finalisasi(self::JENIS, (int) $orderLaboratorium, $this->pegawaiId());
            $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderLaboratorium);

            return redirect()->route('daftar_pesanan_laboratorium.detail', $orderLaboratorium)
                ->with('success', 'Order '.$order->no_order.' diselesaikan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyelesaikan order: '.$e->getMessage());
        }
    }

    public function batal($orderLaboratorium)
    {
        try {
            PenunjangHelper::batalkan(self::JENIS, (int) $orderLaboratorium);
            $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderLaboratorium);

            return redirect()->route('daftar_pesanan_laboratorium.index')
                ->with('success', 'Order '.$order->no_order.' dibatalkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order: '.$e->getMessage());
        }
    }

    public function cetak($orderLaboratorium)
    {
        $order = PenunjangHelper::findOrder(self::JENIS, (int) $orderLaboratorium);

        return view('moduls.PenunjangMedis.Laboratorium.DaftarPesananLaboratorium.cetak', compact('order'));
    }

    private function pegawaiId(): ?int
    {
        return Auth::user()->pegawai_id ?? null;
    }
}
