<?php

namespace App\Http\Controllers\Inventory\Stock\StockBarang;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\HargaBarang;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockBarangController extends Controller
{
    public function index(Request $request)
    {
        $bagianId = $request->input('bagian_id');
        $search = trim((string) $request->input('search'));

        $query = Stock::aktif()->with(['barang' => fn ($b) => $b->with('satuan', 'jenis'), 'bagian']);

        if ($bagianId !== null && $bagianId !== '') {
            $query->where('bagian_id', (int) $bagianId);
        }

        if ($search !== '') {
            $query->whereHas('barang', function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%")
                    ->orWhere('kode_barang', 'ilike', "%{$search}%");
            });
        }

        $stockList = $query->orderBy('bagian_id')->orderBy('barang_id')->orderBy('no_batch')->paginate(10)->withQueryString();

        $bagianList = Bagian::aktif()->orderBy('nama_bagian')->get();
        $hargaMap = HargaBarang::aktif()->get()->keyBy(fn ($h) => $h->barang_id.'|'.($h->no_batch ?? '-'));

        return view('moduls.Inventory.Stock.StockBarang.stock_barang', compact('stockList', 'bagianId', 'search', 'bagianList', 'hargaMap'));
    }
}
