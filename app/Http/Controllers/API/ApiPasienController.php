<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiPasienController extends Controller
{
    public function searchPasien(Request $request)
    {
        Log::info('DIAG.pasien.search', [
            'q' => $request->input('q'),
            'field' => $request->input('field'),
            'user' => $request->user()?->user_id,
            'url' => $request->fullUrl(),
        ]);
        $search = trim((string) $request->input('q'));
        $field = $request->input('field');
        $column = in_array($field, ['no_mr', 'ktp', 'nama'], true) ? ($field === 'nama' ? 'nama_pasien' : $field) : null;

        $query = Pasien::select(
            'pasien_id', 'nama_pasien', 'no_mr', 'ktp', 'jenis_kelamin',
            'tempat_lahir', 'tgl_lahir', 'alamat', 'no_hp', 'agama',
            'gol_darah', 'status_perkawinan', 'pekerjaan', 'nama_ibu_kandung'
        )->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });

        if ($search !== '') {
            if ($column !== null) {
                $query->where($column, 'ilike', "%{$search}%");
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pasien', 'ilike', "%{$search}%")
                        ->orWhere('no_mr', 'ilike', "%{$search}%")
                        ->orWhere('ktp', 'ilike', "%{$search}%");
                });
            }
        }

        $pasiens = $query->orderBy('nama_pasien')->limit(min((int) $request->input('limit', 20), 1000))->get();

        $formatted = $pasiens->map(function ($pasien) use ($field, $column) {
            $extra = [
                'ktp' => $pasien->ktp,
                'jenis_kelamin' => $pasien->jenis_kelamin === 'L' ? 'Laki-Laki' : ($pasien->jenis_kelamin === 'P' ? 'Perempuan' : $pasien->jenis_kelamin),
                'tempat_lahir' => $pasien->tempat_lahir,
                'tgl_lahir' => $pasien->tgl_lahir ? date('d-m-Y', strtotime($pasien->tgl_lahir)) : '-',
                'alamat' => $pasien->alamat,
                'no_hp' => $pasien->no_hp,
                'agama' => $pasien->agama,
                'gol_darah' => $pasien->gol_darah,
                'status_perkawinan' => $pasien->status_perkawinan,
                'pekerjaan' => $pasien->pekerjaan,
                'nama_ibu_kandung' => $pasien->nama_ibu_kandung,
            ];

            if ($column !== null) {
                return [
                    'id' => (string) $pasien->{$column},
                    'text' => $field === 'nama'
                        ? "{$pasien->nama_pasien} (No. RM: {$pasien->no_mr})"
                        : ($field === 'ktp'
                            ? "{$pasien->ktp} - {$pasien->nama_pasien}"
                            : "{$pasien->no_mr} - {$pasien->nama_pasien}"),
                    'extra' => $extra,
                ];
            }

            return [
                'id' => (string) $pasien->pasien_id,
                'text' => "{$pasien->no_mr} - {$pasien->nama_pasien}",
                'extra' => $extra,
            ];
        });

        Log::info('DIAG.pasien.search.count', [
            'q' => $request->input('q'),
            'count' => $formatted->count(),
        ]);

        return response()->json([
            'results' => $formatted->values()->toArray(),
        ]);
    }
}
