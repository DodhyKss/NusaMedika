<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class ApiPegawaiController extends Controller
{
    public function searchPegawai(Request $request) 
    {
        $term = $request->input('q');

        $query = Pegawai::aktif()->where('profesi_id', env('PROFESI_ID_DOKTER'));

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('nama_pegawai', 'ILIKE', "%{$term}%")
                    ->orWhere('nip', 'ILIKE', "%{$term}%");
            });
        }

        $limit = $request->input('limit', 1000);
        $results = $query->limit($limit)->get()->map(function ($pegawai) {
            return [
                'id' => $pegawai->pegawai_id,
                'text' => $pegawai->nama_pegawai,
            ];
        });

        return response()->json([
            'results' => $results,
        ]);
    }
}
