<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SequenceHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Modul;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with('modul')->orderBy('modul_id')->orderBy('urutan_menu')->get();

        return view('moduls.administrator.menu.index', compact('menus'));
    }

    public function create()
    {
        $moduls = Modul::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('urutan_modul')->get();

        return view('moduls.administrator.menu.create', compact('moduls'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'modul_id' => 'required|integer|exists:modul,modul_id',
            'nama_menu' => 'required|string|max:100',
            'urutan_menu' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $menu = new Menu;
            $menu->menu_id = SequenceHelper::getNextId('menu');
            $menu->modul_id = $request->modul_id;
            $menu->nama_menu = $request->nama_menu;
            $menu->urutan_menu = $request->urutan_menu;
            $menu->input_time = now();
            $menu->input_user_id = Auth::id();
            $menu->status_batal = 0;
            $menu->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan menu: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $moduls = Modul::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('urutan_modul')->get();

        return view('moduls.administrator.menu.edit', compact('menu', 'moduls'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'modul_id' => 'required|integer|exists:modul,modul_id',
            'nama_menu' => 'required|string|max:100',
            'urutan_menu' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);
            $menu->modul_id = $request->modul_id;
            $menu->nama_menu = $request->nama_menu;
            $menu->urutan_menu = $request->urutan_menu;
            $menu->mod_time = now();
            $menu->mod_user_id = Auth::id();
            $menu->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui menu: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);
            $menu->status_batal = 1;
            $menu->mod_time = now();
            $menu->mod_user_id = Auth::id();
            $menu->save();

            SubMenu::where('menu_id', $id)->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus menu: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
