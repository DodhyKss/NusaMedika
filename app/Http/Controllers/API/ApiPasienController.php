<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;

class ApiPasienController extends Controller
{
    public function searchPasien(Request $request)
    {
        $search = $request->input('q');
        $query = Pasien::select('pasien_id', 'nama_pasien', 'no_mr');
        
        if ($search) {
            $query->where('nama_pasien', 'ilike', "%{$search}%")
                  ->orWhere('no_mr', 'ilike', "%{$search}%");
        }

        $pasiens = $query->limit(20)->get();

        $formatted = $pasiens->map(function ($pasien) {
            return [
                'id' => $pasien->pasien_id,
                'text' => "{$pasien->no_mr} - {$pasien->nama_pasien}"
            ];
        });

        return response()->json([
            'results' => $formatted
        ]);
    }
}
