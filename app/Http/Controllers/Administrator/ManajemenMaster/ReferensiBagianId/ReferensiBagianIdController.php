<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\ReferensiBagianId;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\ReferensiBagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReferensiBagianIdController extends Controller
{
    public function index()
    {
        $referensiBagians = ReferensiBagian::withCount(['bagians' => function ($q) {
            $q->where(function ($sq) {
                $sq->whereNull('status_batal')->orWhere('status_batal', 0);
            });
        }])->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('referensi_bagian_id_id')->get();

        return view('moduls.Administrator.ManajemenMaster.ReferensiBagianId.index', compact('referensiBagians'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.ReferensiBagianId.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_referensi_bagian_id' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $referensi = new ReferensiBagian;
            $referensi->nama_referensi_bagian_id = $request->nama_referensi_bagian_id;
            $referensi->input_time = now();
            $referensi->input_user_id = Auth::id();
            $referensi->status_batal = 0;
            $referensi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.referensi_bagian_id.index')->with('success', 'Referensi bagian berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan referensi bagian: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $referensi = ReferensiBagian::findOrFail($id);

        return view('moduls.Administrator.ManajemenMaster.ReferensiBagianId.edit', compact('referensi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_referensi_bagian_id' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $referensi = ReferensiBagian::findOrFail($id);
            $referensi->nama_referensi_bagian_id = $request->nama_referensi_bagian_id;
            $referensi->mod_time = now();
            $referensi->mod_user_id = Auth::id();
            $referensi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.referensi_bagian_id.index')->with('success', 'Referensi bagian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui referensi bagian: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $referensi = ReferensiBagian::findOrFail($id);

            $used = Bagian::where('referensi_bagian_id', $id)->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })->count();

            if ($used > 0) {
                throw new \Exception('Referensi bagian masih dipakai oleh '.$used.' bagian aktif.');
            }

            $referensi->status_batal = 1;
            $referensi->mod_time = now();
            $referensi->mod_user_id = Auth::id();
            $referensi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.referensi_bagian_id.index')->with('success', 'Referensi bagian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus referensi bagian: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
