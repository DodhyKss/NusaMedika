<?php

namespace App\Http\Controllers\Administrator\ManajemenEMR\DashboardMenu;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardMenuController extends Controller
{
    private const TABS = ['menu', 'sub', 'extra'];

    private const PK = [
        'menu' => 'dashboard_menu_id',
        'sub' => 'dashboard_menu_sub_id',
        'extra' => 'dashboard_menu_sub_extra_id',
    ];

    private const TABLE = [
        'menu' => 'dashboard_menu',
        'sub' => 'dashboard_menu_sub',
        'extra' => 'dashboard_menu_sub_extra',
    ];

    private const LABELS = [
        'menu' => 'menu',
        'sub' => 'sub menu',
        'extra' => 'sub menu extra',
    ];

    public function index(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        $menus = $this->aktif('dashboard_menu')->orderBy('dashboard_menu_id')->get();
        $subs = $this->aktif('dashboard_menu_sub')->orderBy('dashboard_menu_sub_id')->get();
        $extraCount = $this->aktif('dashboard_menu_sub_extra')->count();

        $data = match ($tab) {
            'sub' => DB::table('dashboard_menu_sub as s')
                ->join('dashboard_menu as m', 'm.dashboard_menu_id', '=', 's.dashboard_menu_id')
                ->where(function ($q) {
                    $q->whereNull('s.status_batal')->orWhere('s.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('m.status_batal')->orWhere('m.status_batal', 0);
                })
                ->when($request->filled('dashboard_menu_id'), fn ($q) => $q->where('s.dashboard_menu_id', $request->input('dashboard_menu_id')))
                ->addSelect('s.*', 'm.nama_menu')
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM dashboard_menu_sub_extra e WHERE e.dashboard_menu_sub_id = s.dashboard_menu_sub_id AND (e.status_batal != 1 OR e.status_batal IS NULL)) AS extra_count'))
                ->orderBy('m.dashboard_menu_id')
                ->orderBy('s.dashboard_menu_sub_id')
                ->get(),
            'extra' => DB::table('dashboard_menu_sub_extra as e')
                ->join('dashboard_menu_sub as s', 's.dashboard_menu_sub_id', '=', 'e.dashboard_menu_sub_id')
                ->join('dashboard_menu as m', 'm.dashboard_menu_id', '=', 's.dashboard_menu_id')
                ->where(function ($q) {
                    $q->whereNull('e.status_batal')->orWhere('e.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('s.status_batal')->orWhere('s.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('m.status_batal')->orWhere('m.status_batal', 0);
                })
                ->when($request->filled('dashboard_menu_sub_id'), fn ($q) => $q->where('e.dashboard_menu_sub_id', $request->input('dashboard_menu_sub_id')))
                ->addSelect('e.*', 's.nama_sub_menu', 'm.nama_menu')
                ->orderBy('m.dashboard_menu_id')
                ->orderBy('s.dashboard_menu_sub_id')
                ->orderBy('e.dashboard_menu_sub_extra_id')
                ->get(),
            default => DB::table('dashboard_menu as m')
                ->where(function ($q) {
                    $q->whereNull('m.status_batal')->orWhere('m.status_batal', 0);
                })
                ->addSelect('m.*')
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM dashboard_menu_sub s WHERE s.dashboard_menu_id = m.dashboard_menu_id AND (s.status_batal != 1 OR s.status_batal IS NULL)) AS sub_count'))
                ->orderBy('m.dashboard_menu_id')
                ->get(),
        };

        return view('moduls.Administrator.ManajemenEMR.DashboardMenu.dashboard_menu', compact('tab', 'data', 'menus', 'subs', 'extraCount'));
    }

    public function create(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        return view('moduls.Administrator.ManajemenEMR.DashboardMenu.dashboard_menu_form', $this->formData($tab));
    }

    public function store(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $data = $this->validatedData($request, $tab);

        DB::beginTransaction();
        try {
            $this->createRecord($tab, $data);
            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.dashboard_menu.index', ['tab' => $tab])->with('success', 'Data '.self::LABELS[$tab].' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $row = $this->findRecord($tab, $id);

        return view('moduls.Administrator.ManajemenEMR.DashboardMenu.dashboard_menu_form', array_merge(['row' => $row], $this->formData($tab)));
    }

    public function update(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $data = $this->validatedData($request, $tab);

        DB::beginTransaction();
        try {
            $this->updateRecord($tab, $id, $data);
            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.dashboard_menu.index', ['tab' => $tab])->with('success', 'Data '.self::LABELS[$tab].' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui data: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));

        DB::beginTransaction();
        try {
            $this->softDeleteRecord($tab, $id);
            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.dashboard_menu.index', ['tab' => $tab])->with('success', 'Data '.self::LABELS[$tab].' beserta turunannya berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    private function resolveTab($tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'menu';
    }

    private function aktif(string $table)
    {
        return DB::table($table)->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    private function formData(string $tab): array
    {
        return [
            'tab' => $tab,
            'menus' => $this->aktif('dashboard_menu')->orderBy('dashboard_menu_id')->get(),
            'subs' => $this->aktif('dashboard_menu_sub')->orderBy('dashboard_menu_sub_id')->get(),
        ];
    }

    private function validatedData(Request $request, string $tab): array
    {
        return match ($tab) {
            'sub' => $request->validate([
                'dashboard_menu_id' => 'required|integer|exists:dashboard_menu,dashboard_menu_id',
                'nama_sub_menu' => 'required|string|max:100',
            ]),
            'extra' => $request->validate([
                'dashboard_menu_sub_id' => 'required|integer|exists:dashboard_menu_sub,dashboard_menu_sub_id',
                'nama_sub_menu_extra' => 'required|string|max:100',
            ]),
            default => $request->validate([
                'nama_menu' => 'required|string|max:100',
            ]),
        };
    }

    private function createRecord(string $tab, array $data): void
    {
        DB::table(self::TABLE[$tab])->insert(array_merge($this->values($tab, $data), [
            'input_time' => now(),
            'input_user_id' => Auth::id(),
            'status_batal' => 0,
        ]));
    }

    private function updateRecord(string $tab, $id, array $data): void
    {
        $this->findRecord($tab, $id);

        DB::table(self::TABLE[$tab])->where(self::PK[$tab], $id)->update(array_merge($this->values($tab, $data), [
            'mod_time' => now(),
            'mod_user_id' => Auth::id(),
        ]));
    }

    private function values(string $tab, array $data): array
    {
        return match ($tab) {
            'sub' => [
                'dashboard_menu_id' => $data['dashboard_menu_id'],
                'nama_sub_menu' => $data['nama_sub_menu'],
            ],
            'extra' => [
                'dashboard_menu_sub_id' => $data['dashboard_menu_sub_id'],
                'nama_sub_menu_extra' => $data['nama_sub_menu_extra'],
            ],
            default => [
                'nama_menu' => $data['nama_menu'],
            ],
        };
    }

    private function findRecord(string $tab, $id)
    {
        $row = DB::table(self::TABLE[$tab])->where(self::PK[$tab], $id)->first();

        if (! $row) {
            abort(404);
        }

        return $row;
    }

    private function softDeleteRecord(string $tab, $id): void
    {
        $this->findRecord($tab, $id);
        $soft = ['status_batal' => 1, 'mod_time' => now(), 'mod_user_id' => Auth::id()];

        match ($tab) {
            'sub' => [
                DB::table('dashboard_menu_sub_extra')->where('dashboard_menu_sub_id', $id)->update($soft),
                DB::table('dashboard_menu_sub')->where('dashboard_menu_sub_id', $id)->update($soft),
            ],
            'extra' => [
                DB::table('dashboard_menu_sub_extra')->where('dashboard_menu_sub_extra_id', $id)->update($soft),
            ],
            default => [
                $subIds = DB::table('dashboard_menu_sub')->where('dashboard_menu_id', $id)->pluck('dashboard_menu_sub_id')->all(),
                DB::table('dashboard_menu_sub_extra')->whereIn('dashboard_menu_sub_id', $subIds)->update($soft),
                DB::table('dashboard_menu_sub')->where('dashboard_menu_id', $id)->update($soft),
                DB::table('dashboard_menu')->where('dashboard_menu_id', $id)->update($soft),
            ],
        };
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
