<?php

namespace App\Http\Controllers\Social\Status;

use App\Http\Controllers\Controller;
use App\Models\StatusSosial;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * Status milik user yang sedang login + form buat status baru.
     *
     * Halaman ini khusus mengelola status sendiri. Melihat status milik user
     * lain ada di sub_menu terpisah "Status User".
     */
    public function index(Request $request)
    {
        $statuses = StatusSosial::belumKedaluwarsa()
            ->where('user_id', $request->user()->user_id)
            ->orderByDesc('input_time')
            ->orderByDesc('status_sosial_id')
            ->paginate(15);

        return view('moduls.Social.Status.status', compact('statuses'));
    }

    /**
     * Simpan status milik user yang sedang login.
     */
    public function store(Request $request)
    {
        $data = array_merge([
            'masa_berlaku' => null,
        ], $request->validate([
            'isi' => 'required|string|max:500',
            'masa_berlaku' => 'nullable|integer|in:'.implode(',', array_keys(StatusSosial::MASA_BERLAKU)),
        ]));

        DB::beginTransaction();
        try {
            $status = new StatusSosial;
            $status->user_id = $request->user()->user_id;
            $status->isi = $data['isi'];
            // Masa berlaku NULL = status tidak pernah kedaluwarsa.
            $status->tanggal_kadaraluarsa = $data['masa_berlaku']
                ? Carbon::now()->addHours((int) $data['masa_berlaku'])
                : null;
            $status->input_time = now();
            $status->input_user_id = $request->user()->user_id;
            $status->status_batal = 0;
            $status->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan status: '.$e->getMessage())->withInput();
        }

        return back()->with('success', 'Status berhasil dipublikasikan.');
    }

    /**
     * Hapus status milik sendiri saja.
     */
    public function destroy($status)
    {
        $user = Auth::user();

        $status = StatusSosial::aktif()->findOrFail($status);

        // Status hanya boleh dihapus oleh pemiliknya.
        if ((int) $status->user_id !== (int) $user->user_id) {
            return back()->with('error', 'Status ini bukan milik Anda, tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            $status->status_batal = 1;
            $status->mod_time = now();
            $status->mod_user_id = $user->user_id;
            $status->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus status: '.$e->getMessage());
        }

        return back()->with('success', 'Status berhasil dihapus.');
    }
}
