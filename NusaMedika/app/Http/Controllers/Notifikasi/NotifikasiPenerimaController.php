<?php

namespace App\Http\Controllers\Notifikasi;

use App\Helpers\NotifikasiHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotifikasiPenerimaController extends Controller
{
    /**
     * Tutup satu notifikasi milik user yang sedang login.
     * Notifikasi milik user lain tidak bisa ditutup (helper mengembalikan false).
     */
    public function tutup(Request $request, $notifikasi)
    {
        $ditutup = NotifikasiHelper::tutup((int) $notifikasi, (int) $request->user()->user_id);

        if (! $ditutup) {
            return back()->with('error', 'Notifikasi tidak ditemukan atau sudah ditutup.');
        }

        return back();
    }

    /**
     * Tutup semua notifikasi milik user yang sedang login.
     */
    public function tutupSemua(Request $request)
    {
        NotifikasiHelper::tutupSemua((int) $request->user()->user_id);

        return back();
    }
}
