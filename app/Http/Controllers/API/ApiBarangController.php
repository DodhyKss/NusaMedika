<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class ApiBarangController extends Controller
{
    public function searchBarang(Request $request)
    {
        $term = $request->input('q');

        $query = Barang::aktif()->with('satuan');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('nama_barang', 'ILIKE', "%{$term}%")
                    ->orWhere('kode_barang', 'ILIKE', "%{$term}%");
            });
        }

        $limit = $request->input('limit', 1000);
        $results = $query->limit($limit)->orderBy('nama_barang')->get()->map(function ($barang) {
            return [
                'id' => $barang->barang_id,
                'text' => trim(($barang->kode_barang ? $barang->kode_barang.' - ' : '').$barang->nama_barang.' ('.$barang->textSatuan().')'),
                'kode' => $barang->kode_barang,
                'satuan' => $barang->textSatuan(),
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }
}
