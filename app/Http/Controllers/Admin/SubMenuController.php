<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SequenceHelper;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\User;
use App\Models\UserAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubMenuController extends Controller
{
    public function index()
    {
        $subMenus = SubMenu::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with(['menu.modul'])->orderBy('menu_id')->orderBy('urutan_sub_menu')->get();

        return view('moduls.administrator.sub_menu.index', compact('subMenus'));
    }

    public function create()
    {
        $menus = Menu::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with('modul')->orderBy('modul_id')->orderBy('urutan_menu')->get();

        return view('moduls.administrator.sub_menu.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menu,menu_id',
            'nama_sub_menu' => 'required|string|max:100',
            'file_sub_menu' => 'required|string|max:100',
            'urutan_sub_menu' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $subMenu = new SubMenu;
            $subMenu->sub_menu_id = SequenceHelper::getNextId('sub_menu');
            $subMenu->menu_id = $request->menu_id;
            $subMenu->nama_sub_menu = $request->nama_sub_menu;
            $subMenu->file_sub_menu = $request->file_sub_menu;
            $subMenu->urutan_sub_menu = $request->urutan_sub_menu;
            $subMenu->input_time = now();
            $subMenu->input_user_id = Auth::id();
            $subMenu->status_batal = 0;
            $subMenu->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.sub_menu.index')->with('success', 'Sub Menu berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan sub menu: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $subMenu = SubMenu::findOrFail($id);
        $menus = Menu::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with('modul')->orderBy('modul_id')->orderBy('urutan_menu')->get();

        return view('moduls.administrator.sub_menu.edit', compact('subMenu', 'menus'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menu,menu_id',
            'nama_sub_menu' => 'required|string|max:100',
            'file_sub_menu' => 'required|string|max:100',
            'urutan_sub_menu' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $subMenu = SubMenu::findOrFail($id);
            $subMenu->menu_id = $request->menu_id;
            $subMenu->nama_sub_menu = $request->nama_sub_menu;
            $subMenu->file_sub_menu = $request->file_sub_menu;
            $subMenu->urutan_sub_menu = $request->urutan_sub_menu;
            $subMenu->mod_time = now();
            $subMenu->mod_user_id = Auth::id();
            $subMenu->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.sub_menu.index')->with('success', 'Sub Menu berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui sub menu: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $subMenu = SubMenu::findOrFail($id);
            $subMenu->status_batal = 1;
            $subMenu->mod_time = now();
            $subMenu->mod_user_id = Auth::id();
            $subMenu->save();

            UserAkses::where('sub_menu_id', $id)->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.sub_menu.index')->with('success', 'Sub Menu berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus sub menu: '.$e->getMessage());
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
