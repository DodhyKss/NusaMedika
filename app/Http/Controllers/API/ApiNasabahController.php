<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\Request;

class ApiNasabahController extends Controller
{
    public function searchNasabah(Request $request)
    {   
        $term = $request->input('q');

        $query = Nasabah::aktif();

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('nama_nasabah', 'ILIKE', "%{$term}%");
            });
        }

        $limit = $request->input('limit', 1000);
        $results = $query->limit($limit)->get()->map(function ($nasabah) {
            return [
                'id' => $nasabah->nasabah_id,
                'text' => $nasabah->nama_nasabah,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }
}
