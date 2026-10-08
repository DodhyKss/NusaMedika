<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class ApiBarangController extends Controller
{
    /**
     * Pencarian barang untuk Select2 AJAX.
     *
     * Query param:
     *  - `q`              kata kunci (nama / kode barang)
     *  - `jenis_barang_id` filter jenis barang, dipakai cascade Jenis -> Barang
     *                      pada form EMR Tindakan Medis
     */
    public function searchBarang(Request $request)
    {
        $term = $request->input('q');
        $jenisId = $request->input('jenis_barang_id');

        $query = Barang::aktif()->with('satuan');

        if ($jenisId !== null && $jenisId !== '') {
            $query->where('jenis_barang_id', (int) $jenisId);
        }

        if ($term) {
            // Pakai `like`, bukan `ilike`: `ilike` hanya ada di PostgreSQL,
            // sedangkan app ini berjalan di MySQL (collation utf8mb4 sudah
            // case-insensitive).
            $query->where(function ($q) use ($term) {
                $q->where('nama_barang', 'like', "%{$term}%")
                    ->orWhere('kode_barang', 'like', "%{$term}%");
            });
        }

        $limit = (int) $request->input('limit', 50);
        $results = $query->limit(max(1, min($limit, 200)))
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($barang) {
                $satuan = $barang->textSatuan();

                return [
                    'id' => $barang->barang_id,
                    'text' => trim(($barang->kode_barang ? $barang->kode_barang.' - ' : '').$barang->nama_barang.($satuan !== '' ? ' ('.$satuan.')' : '')),
                    'kode' => $barang->kode_barang,
                    'satuan' => $satuan,
                ];
            });

        return response()->json([
            'results' => $results,
        ]);
    }
}
