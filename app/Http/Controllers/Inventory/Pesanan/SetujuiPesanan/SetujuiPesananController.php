<?php

namespace App\Http\Controllers\Inventory\Pesanan\SetujuiPesanan;

use App\Http\Controllers\Controller;
use App\Models\SetujuiPesanan;
use Illuminate\Http\Request;

class SetujuiPesananController extends Controller
{
    public function index()
    {
        // Logika untuk menampilkan daftar (INDEX) di sini.
        return view('moduls.Inventory.Pesanan.SetujuiPesanan.setujui_pesanan');
    }

    public function create()
    {
        // Logika untuk menampilkan form tambah (CREATE) di sini.
        // return view('moduls.Inventory.Pesanan.SetujuiPesanan.setujui_pesanan_create');
    }

    public function store(Request $request)
    {
        // Logika untuk menyimpan data baru di sini.
    }

    public function edit($id)
    {
        // Logika untuk menampilkan form ubah (EDIT) di sini.
        // return view('moduls.Inventory.Pesanan.SetujuiPesanan.setujui_pesanan_edit', compact('...'));
    }

    public function update(Request $request, $id)
    {
        // Logika untuk memperbarui data di sini.
    }

    public function destroy($id)
    {
        // Logika untuk menghapus data di sini.
    }
}