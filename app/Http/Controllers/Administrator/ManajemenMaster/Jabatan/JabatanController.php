<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_jabatan')->get();

        return view('moduls.administrator.manajemen_master.jabatan.index', compact('jabatans'));
    }

    public function create()
    {
        return view('moduls.administrator.manajemen_master.jabatan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $jabatan = new Jabatan;
            $jabatan->nama_jabatan = $request->nama_jabatan;
            $jabatan->input_time = now();
            $jabatan->input_user_id = Auth::id();
            $jabatan->status_batal = 0;
            $jabatan->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan jabatan: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        return view('moduls.administrator.manajemen_master.jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $jabatan = Jabatan::findOrFail($id);
            $jabatan->nama_jabatan = $request->nama_jabatan;
            $jabatan->mod_time = now();
            $jabatan->mod_user_id = Auth::id();
            $jabatan->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui jabatan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $jabatan = Jabatan::findOrFail($id);
            $jabatan->status_batal = 1;
            $jabatan->mod_time = now();
            $jabatan->mod_user_id = Auth::id();
            $jabatan->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus jabatan: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
