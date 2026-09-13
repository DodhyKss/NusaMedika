<?php

namespace App\Http\Controllers\Inventory\Distribusi\MutasiBarang;

use App\Helpers\StockHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Barang;
use App\Models\MutasiBarang;
use App\Models\MutasiBarangDetail;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MutasiBarangController extends Controller
{
    public function index(Request $request)
    {
        $query = MutasiBarang::aktif()->with(['bagianAsal', 'bagianTujuan', 'details' => fn ($q) => $q->aktif()->with('barang')]);

        $mutasiList = $query->orderByDesc('mutasi_barang_id')->paginate(10)->withQueryString();

        return view('moduls.Inventory.Distribusi.MutasiBarang.mutasi_barang', compact('mutasiList'));
    }

    public function create()
    {
        $bagianList = Bagian::aktif()->orderBy('nama_bagian')->get();
        $barangs = Barang::aktif()->with('satuan')->orderBy('nama_barang')->get();
        $batchStock = Stock::aktif()
            ->with('barang')
            ->where('jumlah', '>', 0)
            ->get()
            ->map(fn ($s) => [
                'barang_id' => $s->barang_id,
                'barang' => $s->barang?->nama_barang ?? '-',
                'no_batch' => $s->no_batch,
                'bagian_id' => $s->bagian_id,
                'jumlah' => (float) $s->jumlah,
            ])
            ->values();

        return view('moduls.Inventory.Distribusi.MutasiBarang.mutasi_barang_create', compact('bagianList', 'barangs', 'batchStock'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $items = $this->validatedItems($request);

        if (empty($items)) {
            return back()->with('error', 'Minimal satu item barang wajib diisi.')->withInput();
        }

        if ($data['bagian_tujuan_id'] !== null && (int) $data['bagian_tujuan_id'] === (int) $data['bagian_asal_id']) {
            return back()->with('error', 'Bagian asal dan tujuan tidak boleh sama.')->withInput();
        }

        foreach ($items as $item) {
            $stock = Stock::aktif()
                ->where('barang_id', $item['barang_id'])
                ->where('bagian_id', $data['bagian_asal_id'])
                ->where('no_batch', $item['no_batch'])
                ->first();

            if (! $stock || (float) $stock->jumlah < $item['jumlah']) {
                $barang = Barang::find($item['barang_id']);

                return back()->with('error', 'Stock batch '.($item['no_batch'] ?: '-').' tidak mencukupi untuk '.($barang->nama_barang ?? '#'.$item['barang_id']).'.')->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $mutasi = new MutasiBarang;
            $mutasi->bagian_asal_id = $data['bagian_asal_id'];
            $mutasi->bagian_tujuan_id = $data['bagian_tujuan_id'];
            $mutasi->tanggal_mutasi = $data['tanggal_mutasi'];
            $mutasi->keterangan = $data['keterangan'];
            $mutasi->input_time = now();
            $mutasi->input_user_id = Auth::id();
            $mutasi->status_batal = 0;
            $mutasi->save();

            $mutasi->no_mutasi = 'MT-'.now()->format('Ymd').'-'.str_pad($mutasi->mutasi_barang_id, 4, '0', STR_PAD_LEFT);
            $mutasi->save();

            $adaTujuan = $data['bagian_tujuan_id'] !== null;

            foreach ($items as $item) {
                $detail = new MutasiBarangDetail;
                $detail->mutasi_barang_id = $mutasi->mutasi_barang_id;
                $detail->barang_id = $item['barang_id'];
                $detail->no_batch = $item['no_batch'];
                $detail->jumlah = $item['jumlah'];
                $detail->input_time = now();
                $detail->input_user_id = Auth::id();
                $detail->status_batal = 0;
                $detail->save();

                StockHelper::tambahKeluar(
                    $data['bagian_asal_id'],
                    $item['barang_id'],
                    $item['no_batch'],
                    (float) $item['jumlah'],
                    [
                        'jenis_mutasi' => $adaTujuan ? 3 : 4,
                        'ref_mutasi_barang_detail_id' => $detail->mutasi_barang_detail_id,
                        'tanggal' => $mutasi->tanggal_mutasi,
                        'keterangan' => $adaTujuan ? 'Mutasi ke '.($mutasi->bagianTujuan->nama_bagian ?? '#'.$data['bagian_tujuan_id']) : 'Pemakaian / Pengeluaran',
                    ]
                );

                if ($adaTujuan) {
                    StockHelper::tambahMasuk(
                        $data['bagian_tujuan_id'],
                        $item['barang_id'],
                        $item['no_batch'],
                        (float) $item['jumlah'],
                        [
                            'jenis_mutasi' => 2,
                            'ref_mutasi_barang_detail_id' => $detail->mutasi_barang_detail_id,
                            'tanggal' => $mutasi->tanggal_mutasi,
                            'keterangan' => 'Mutasi dari '.($mutasi->bagianAsal->nama_bagian ?? '#'.$data['bagian_asal_id']),
                        ]
                    );
                }
            }

            DB::commit();

            return redirect()->route('mutasi_barang.index')->with('success', 'Mutasi barang berhasil dicatat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan mutasi: '.$e->getMessage())->withInput();
        }
    }

    private function validated(Request $request)
    {
        return array_merge([
            'tanggal_mutasi' => now()->toDateTimeString(),
            'bagian_tujuan_id' => null,
            'keterangan' => null,
        ], $request->validate([
            'bagian_asal_id' => 'required|integer|exists:bagian,bagian_id',
            'bagian_tujuan_id' => 'nullable|integer|exists:bagian,bagian_id',
            'tanggal_mutasi' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ]));
    }

    private function validatedItems(Request $request): array
    {
        $barangIds = (array) $request->input('barang_id', []);
        $jumlahs = (array) $request->input('jumlah', []);
        $batches = (array) $request->input('no_batch', []);

        $items = [];
        foreach ($barangIds as $i => $barangId) {
            if ($barangId === null || $barangId === '') {
                continue;
            }

            $jumlah = (float) ($jumlahs[$i] ?? 0);
            if ($jumlah <= 0) {
                continue;
            }

            $noBatch = trim((string) ($batches[$i] ?? ''));

            $items[] = [
                'barang_id' => (int) $barangId,
                'no_batch' => $noBatch,
                'jumlah' => $jumlah,
            ];
        }

        return $items;
    }
}
