<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Modul;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Modul;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModulController extends Controller
{
    public function index()
    {
        $moduls = Modul::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('urutan_modul')->get();

        return view('moduls.administrator.manajemen_master.modul.index', compact('moduls'));
    }

    public function create()
    {
        return view('moduls.administrator.manajemen_master.modul.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_modul' => 'required|string|max:100',
            'icon_modul' => 'nullable|string|max:100',
            'urutan_modul' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $modul = new Modul;
            $modul->modul_id = GenerateHelper::getNextId('modul');
            $modul->nama_modul = $request->nama_modul;
            $modul->icon_modul = $request->icon_modul;
            $modul->urutan_modul = $request->urutan_modul;
            $modul->input_time = now();
            $modul->input_user_id = Auth::id();
            $modul->status_batal = 0;
            $modul->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.modul.index')->with('success', 'Modul berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan modul: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $modul = Modul::findOrFail($id);

        return view('moduls.administrator.manajemen_master.modul.edit', compact('modul'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_modul' => 'required|string|max:100',
            'icon_modul' => 'nullable|string|max:100',
            'urutan_modul' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $modul = Modul::findOrFail($id);
            $modul->nama_modul = $request->nama_modul;
            $modul->icon_modul = $request->icon_modul;
            $modul->urutan_modul = $request->urutan_modul;
            $modul->mod_time = now();
            $modul->mod_user_id = Auth::id();
            $modul->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.modul.index')->with('success', 'Modul berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui modul: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $modul = Modul::findOrFail($id);
            $modul->status_batal = 1;
            $modul->mod_time = now();
            $modul->mod_user_id = Auth::id();
            $modul->save();

            $menuIds = Menu::where('modul_id', $id)->pluck('menu_id');
            Menu::whereIn('menu_id', $menuIds)->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);
            SubMenu::whereIn('menu_id', $menuIds)->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.modul.index')->with('success', 'Modul berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus modul: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
