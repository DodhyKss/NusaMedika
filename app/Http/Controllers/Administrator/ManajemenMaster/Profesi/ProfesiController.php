<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Profesi;

use App\Http\Controllers\Controller;
use App\Models\Profesi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfesiController extends Controller
{
    public function index()
    {
        $profesis = Profesi::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_profesi')->get();

        return view('moduls.Administrator.ManajemenMaster.Profesi.profesi', compact('profesis'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Profesi.profesi_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_profesi' => 'required|string|max:100',
            'ehr' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $profesi = new Profesi;
            $profesi->nama_profesi = $request->nama_profesi;
            $profesi->ehr = $request->boolean('ehr');
            $profesi->input_time = now();
            $profesi->input_user_id = Auth::id();
            $profesi->status_batal = 0;
            $profesi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.profesi.index')->with('success', 'Profesi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan profesi: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $profesi = Profesi::findOrFail($id);

        return view('moduls.Administrator.ManajemenMaster.Profesi.profesi_edit', compact('profesi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_profesi' => 'required|string|max:100',
            'ehr' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $profesi = Profesi::findOrFail($id);
            $profesi->nama_profesi = $request->nama_profesi;
            $profesi->ehr = $request->boolean('ehr');
            $profesi->mod_time = now();
            $profesi->mod_user_id = Auth::id();
            $profesi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.profesi.index')->with('success', 'Profesi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui profesi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $profesi = Profesi::findOrFail($id);
            $profesi->status_batal = 1;
            $profesi->mod_time = now();
            $profesi->mod_user_id = Auth::id();
            $profesi->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.profesi.index')->with('success', 'Profesi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus profesi: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
