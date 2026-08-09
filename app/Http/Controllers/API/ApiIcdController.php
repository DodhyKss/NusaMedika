<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ICD;
use Illuminate\Http\Request;

class ApiIcdController extends Controller
{
    public function searchIcd(Request $request)
    {
        $term = $request->input('q');

        $query = ICD::aktif();

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('kode_diagnosa', 'ILIKE', "%{$term}%")
                  ->orWhere('nama_diagnosa', 'ILIKE', "%{$term}%");
            });
        }

        $limit = $request->input('limit', 1000);
        $results = $query->limit($limit)->get()->map(function ($icd) {
            return [
                'id' => $icd->icd_id,
                'text' => $icd->kode_diagnosa . ' - ' . $icd->nama_diagnosa,
            ];
        });

        return response()->json([
            'results' => $results
        ]);
    }
}
