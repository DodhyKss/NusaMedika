<?php

namespace App\Http\Controllers\Inventory\Pesanan\SetujuiPesanan;

use App\Http\Controllers\Controller;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetujuiPesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::aktif()
            ->with(['bagian', 'details' => fn ($q) => $q->aktif()->with('barang.satuan', 'supplier', 'distributor')])
            ->where('status_pemesanan', 0);

        $pemesananList = $query->orderBy('pemesanan_id')->paginate(10)->withQueryString();

        return view('moduls.Inventory.Pesanan.SetujuiPesanan.setujui_pesanan', compact('pemesananList'));
    }

    public function setujui(Request $request, $pemesanan)
    {
        DB::beginTransaction();
        try {
            $pemesanan = Pemesanan::aktif()->findOrFail($pemesanan);

            if ((int) $pemesanan->status_pemesanan !== 0) {
                throw new \RuntimeException('Pemesanan sudah diproses sebelumnya.');
            }

            $pemesanan->status_pemesanan = 1;
            $pemesanan->mod_time = now();
            $pemesanan->mod_user_id = Auth::id();
            $pemesanan->save();

            DB::commit();

            return redirect()->route('setujui_pesanan.index')->with('success', 'Pemesanan '.$pemesanan->no_pemesanan.' berhasil disetujui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyetujui pemesanan: '.$e->getMessage());
        }
    }

    public function tolak(Request $request, $pemesanan)
    {
        DB::beginTransaction();
        try {
            $pemesanan = Pemesanan::aktif()->findOrFail($pemesanan);

            if ((int) $pemesanan->status_pemesanan !== 0) {
                throw new \RuntimeException('Pemesanan sudah diproses sebelumnya.');
            }

            $pemesanan->status_pemesanan = 3;
            $pemesanan->mod_time = now();
            $pemesanan->mod_user_id = Auth::id();
            $pemesanan->save();

            DB::commit();

            return redirect()->route('setujui_pesanan.index')->with('success', 'Pemesanan '.$pemesanan->no_pemesanan.' ditolak.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menolak pemesanan: '.$e->getMessage());
        }
    }
}
