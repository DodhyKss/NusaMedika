<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use Illuminate\Http\Request;

class ApiWilayahController extends Controller
{
    public function provinsi()
    {
        $rows = Provinsi::aktif()->orderBy('nama_provinsi')->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => ['id' => $row->provinsi_id, 'text' => $row->nama_provinsi]),
        ]);
    }

    public function kabupaten(Request $request)
    {
        $query = Kabupaten::aktif();

        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->input('provinsi_id'));
        }

        $rows = $query->orderBy('nama_kabupaten')->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => ['id' => $row->kabupaten_id, 'text' => $row->nama_kabupaten]),
        ]);
    }

    public function kecamatan(Request $request)
    {
        $query = Kecamatan::aktif();

        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->input('kabupaten_id'));
        }

        $rows = $query->orderBy('nama_kecamatan')->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => ['id' => $row->kecamatan_id, 'text' => $row->nama_kecamatan]),
        ]);
    }

    public function kelurahan(Request $request)
    {
        $query = Kelurahan::aktif();

        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->input('kecamatan_id'));
        }

        $rows = $query->orderBy('nama_kelurahan')->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => ['id' => $row->kelurahan_id, 'text' => $row->nama_kelurahan]),
        ]);
    }
}
