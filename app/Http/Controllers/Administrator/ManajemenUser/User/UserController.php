<?php

namespace App\Http\Controllers\Administrator\ManajemenUser\User;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\UserAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with(['pegawai' => function ($q) {
            $q->where(function ($sq) {
                $sq->whereNull('status_batal')->orWhere('status_batal', 0);
            });
        }])->orderBy('user_id')->get();

        return view('moduls.administrator.manajemen_user.user.index', compact('users'));
    }

    public function create()
    {
        $moduls = $this->modulsWithSubMenus();
        $pegawais = $this->activePegawais();

        return view('moduls.administrator.manajemen_user.user.create', compact('moduls', 'pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:50|unique:users,user_name',
            'user_password' => 'required|string|max:30',
            'pegawai_id' => 'required|integer|exists:pegawai,pegawai_id',
            'sub_menu_ids' => 'nullable|array',
            'sub_menu_ids.*' => 'integer|exists:sub_menu,sub_menu_id',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($request->pegawai_id);

            $user = new User;
            $user->user_id = GenerateHelper::getNextId('users', 'user_id');
            $user->user_name = $request->user_name;
            $user->user_password = $request->user_password;
            $user->nama_pegawai = $pegawai->nama_pegawai;
            $user->pegawai_id = $pegawai->pegawai_id;
            $user->input_time = now();
            $user->input_user_id = Auth::id();
            $user->last_update_pass = now();
            $user->status_batal = 0;
            $user->save();

            $this->syncUserAkses($user->user_id, $request->sub_menu_ids ?? []);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan user: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $moduls = $this->modulsWithSubMenus();
        $pegawais = $this->activePegawais();
        $userAkses = UserAkses::where('user_id', $id)
            ->where(function ($q) {
                $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
            })
            ->pluck('sub_menu_id')
            ->toArray();

        return view('moduls.administrator.manajemen_user.user.edit', compact('user', 'moduls', 'pegawais', 'userAkses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_name' => 'required|string|max:50|unique:users,user_name,'.$id.',user_id',
            'user_password' => 'nullable|string|max:30',
            'pegawai_id' => 'required|integer|exists:pegawai,pegawai_id',
            'sub_menu_ids' => 'nullable|array',
            'sub_menu_ids.*' => 'integer|exists:sub_menu,sub_menu_id',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($request->pegawai_id);

            $user = User::findOrFail($id);
            $user->user_name = $request->user_name;
            $user->nama_pegawai = $pegawai->nama_pegawai;
            $user->pegawai_id = $pegawai->pegawai_id;
            if ($request->filled('user_password')) {
                $user->user_password = $request->user_password;
                $user->last_update_pass = now();
            }
            $user->mod_time = now();
            $user->mod_user_id = Auth::id();
            $user->save();

            $this->syncUserAkses($id, $request->sub_menu_ids ?? []);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui user: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            if ((int) $id === (int) Auth::id()) {
                return back()->with('error', 'Tidak dapat menghapus user yang sedang login.');
            }

            $user = User::findOrFail($id);
            $user->status_batal = 1;
            $user->mod_time = now();
            $user->mod_user_id = Auth::id();
            $user->save();

            UserAkses::where('user_id', $id)->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus user: '.$e->getMessage());
        }
    }

    private function modulsWithSubMenus()
    {
        return Modul::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })
            ->with(['menus' => function ($query) {
                $query->where(function ($q) {
                    $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
                })->with(['subMenus' => function ($query) {
                    $query->where(function ($q) {
                        $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
                    });
                }]);
            }])
            ->orderBy('urutan_modul')
            ->get();
    }

    private function activePegawais()
    {
        return Pegawai::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_pegawai')->get();
    }

    private function syncUserAkses($userId, array $subMenuIds)
    {
        UserAkses::where('user_id', $userId)->update([
            'status_batal' => 1,
            'mod_time' => now(),
            'mod_user_id' => Auth::id(),
        ]);

        $nextId = GenerateHelper::getNextId('user_akses');
        foreach ($subMenuIds as $subMenuId) {
            $exists = UserAkses::where('user_id', $userId)
                ->where('sub_menu_id', $subMenuId)
                ->first();

            if ($exists) {
                $exists->status_batal = 0;
                $exists->mod_time = now();
                $exists->mod_user_id = Auth::id();
                $exists->save();
            } else {
                DB::table('user_akses')->insert([
                    'user_akses_id' => $nextId++,
                    'user_id' => $userId,
                    'sub_menu_id' => $subMenuId,
                    'input_time' => now(),
                    'input_user_id' => Auth::id(),
                    'status_batal' => 0,
                ]);
            }
        }
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
