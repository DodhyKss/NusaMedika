<?php

namespace App\Http\Controllers\Inventory\Penerimaan\PenerimaanBarang;

use App\Helpers\StockHelper;
use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use App\Models\Penerimaan;
use App\Models\PenerimaanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Penerimaan::aktif()->with(['pemesanan', 'bagian', 'details']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_faktur', 'like', "%{$search}%")
                    ->orWhereHas('details.supplier', fn ($s) => $s->where('nama_supplier', 'like', "%{$search}%"))
                    ->orWhereHas('pemesanan', fn ($p) => $p->where('no_pemesanan', 'like', "%{$search}%"));
            });
        }

        $penerimaanList = $query->orderByDesc('penerimaan_id')->paginate(10)->withQueryString();

        return view('moduls.Inventory.Penerimaan.PenerimaanBarang.penerimaan_barang', compact('penerimaanList', 'search'));
    }

    public function create(Request $request)
    {
        $pemesananId = $request->input('pemesanan_id');

        if ($pemesananId) {
            $pemesanan = $this->poTerverima($pemesananId);

            if (! $pemesanan) {
                return redirect()->route('penerimaan_barang.create')->with('error', 'Pemesanan tidak valid atau sudah diterima.');
            }

            return view('moduls.Inventory.Penerimaan.PenerimaanBarang.penerimaan_barang_create', compact('pemesanan'));
        }

        $pemesananList = $this->listPoSiapTerima();

        return view('moduls.Inventory.Penerimaan.PenerimaanBarang.penerimaan_barang_pilih', compact('pemesananList'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $pemesanan = $this->poTerverima($data['pemesanan_id']);

        if (! $pemesanan) {
            return back()->with('error', 'Pemesanan tidak valid atau sudah diterima.');
        }

        try {
            $items = $this->validatedItems($request, $pemesanan);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if (empty($items)) {
            return back()->with('error', 'Minimal satu item penerimaan wajib diisi.')->withInput();
        }

        DB::beginTransaction();
        try {
            $penerimaan = new Penerimaan;
            $penerimaan->pemesanan_id = $pemesanan->pemesanan_id;
            $penerimaan->bagian_id = $pemesanan->bagian_id;
            $penerimaan->no_faktur = $data['no_faktur'];
            $penerimaan->tanggal_terima = $data['tanggal_terima'];
            $penerimaan->keterangan = $data['keterangan'];
            $penerimaan->input_time = now();
            $penerimaan->input_user_id = Auth::id();
            $penerimaan->status_batal = 0;
            $penerimaan->save();

            $poDtl = $pemesanan->details->keyBy('barang_id');

            foreach ($items as $item) {
                $poDetail = $poDtl->get($item['barang_id']);

                $detail = new PenerimaanDetail;
                $detail->penerimaan_id = $penerimaan->penerimaan_id;
                $detail->barang_id = $item['barang_id'];
                $detail->supplier_id = $poDetail?->supplier_id;
                $detail->distributor_id = $poDetail?->distributor_id;
                $detail->no_batch = $item['no_batch'];
                $detail->jumlah_terima = $item['jumlah_terima'];
                $detail->harga_beli = $item['harga_beli'];
                $detail->harga_jual = $item['harga_jual'];
                $detail->subtotal = round((float) $item['jumlah_terima'] * (float) $item['harga_beli'], 2);
                $detail->input_time = now();
                $detail->input_user_id = Auth::id();
                $detail->status_batal = 0;
                $detail->save();

                StockHelper::tambahMasuk(
                    $penerimaan->bagian_id,
                    $item['barang_id'],
                    $item['no_batch'],
                    (float) $item['jumlah_terima'],
                    [
                        'jenis_mutasi' => 1,
                        'ref_penerimaan_detail_id' => $detail->penerimaan_detail_id,
                        'tanggal' => $penerimaan->tanggal_terima,
                        'keterangan' => $penerimaan->no_faktur ?: 'Penerimaan barang',
                        'harga_beli' => $item['harga_beli'],
                        'harga_jual' => $item['harga_jual'],
                        'tgl_expired' => $item['tgl_expired'],
                    ]
                );
            }

            $pemesanan->status_pemesanan = 2;
            $pemesanan->mod_time = now();
            $pemesanan->mod_user_id = Auth::id();
            $pemesanan->save();

            DB::commit();

            return redirect()->route('penerimaan_barang.index')->with('success', 'Penerimaan berhasil dicatat dan stock bertambah.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan penerimaan: '.$e->getMessage())->withInput();
        }
    }

    private function listPoSiapTerima()
    {
        $alreadyReceived = Penerimaan::aktif()->pluck('pemesanan_id');

        return Pemesanan::aktif()
            ->with(['bagian', 'details' => fn ($q) => $q->aktif()->with('barang.satuan', 'supplier', 'distributor')])
            ->where('status_pemesanan', 1)
            ->whereNotIn('pemesanan_id', $alreadyReceived)
            ->orderBy('pemesanan_id')
            ->get();
    }

    private function poTerverima($pemesananId)
    {
        $alreadyReceived = Penerimaan::aktif()->pluck('pemesanan_id');
        $pemesanan = Pemesanan::aktif()
            ->with(['bagian', 'details' => fn ($q) => $q->aktif()->with('barang.satuan', 'supplier', 'distributor')])
            ->where('status_pemesanan', 1)
            ->where('pemesanan_id', (int) $pemesananId)
            ->whereNotIn('pemesanan_id', $alreadyReceived)
            ->first();

        return $pemesanan;
    }

    private function validated(Request $request)
    {
        return array_merge([
            'tanggal_terima' => now()->toDateTimeString(),
            'no_faktur' => null,
            'keterangan' => null,
        ], $request->validate([
            'pemesanan_id' => 'required|integer|exists:pemesanan,pemesanan_id',
            'no_faktur' => 'nullable|string|max:50',
            'tanggal_terima' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ]));
    }

    private function validatedItems(Request $request, Pemesanan $pemesanan): array
    {
        $barangIds = (array) $request->input('barang_id', []);
        $jumlahs = (array) $request->input('jumlah_terima', []);
        $batches = (array) $request->input('no_batch', []);
        $expireds = (array) $request->input('tgl_expired', []);

        $poDtl = $pemesanan->details->keyBy('barang_id');

        $items = [];
        foreach ($barangIds as $i => $barangId) {
            if ($barangId === null || $barangId === '') {
                continue;
            }

            $poDetail = $poDtl->get((int) $barangId);
            if (! $poDetail) {
                continue;
            }

            $jumlah = (float) ($jumlahs[$i] ?? 0);
            if ($jumlah <= 0) {
                continue;
            }

            $noBatch = trim((string) ($batches[$i] ?? ''));
            if ($noBatch === '') {
                throw new \RuntimeException('No. Batch wajib diisi untuk '.($poDetail->barang->nama_barang ?? '#'.$barangId).'.');
            }

            $tglExpired = trim((string) ($expireds[$i] ?? ''));
            if ($tglExpired === '') {
                throw new \RuntimeException('Tanggal expired wajib diisi untuk '.($poDetail->barang->nama_barang ?? '#'.$barangId).'.');
            }

            $sisaPesan = max(0, (float) $poDetail->jumlah_pesan);

            if ($jumlah > $sisaPesan) {
                throw new \RuntimeException('Jumlah terima melebihi jumlah pesan untuk barang '.($poDetail->barang->nama_barang ?? '#'.$barangId).'.');
            }

            $items[] = [
                'barang_id' => (int) $barangId,
                'no_batch' => $noBatch,
                'jumlah_terima' => $jumlah,
                'harga_beli' => (float) ($poDetail->harga_beli ?? 0),
                'harga_jual' => ($poDetail->harga_jual ?? null) !== null ? (float) $poDetail->harga_jual : null,
                'tgl_expired' => $tglExpired,
            ];
        }

        return $items;
    }
}
