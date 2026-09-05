<?php

namespace App\Http\Controllers\Administrator\ManajemenEMR\AksesEhr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AksesEhrController extends Controller
{
    public function index(Request $request)
    {
        $profesis = $this->profesis();
        $profesiId = $request->integer('profesi_id') ?: null;

        $tree = [];
        $aksesCount = 0;
        $totalLeaf = 0;

        if ($profesiId) {
            $tree = $this->buildTree($profesiId);
            $totalLeaf = $this->countLeaf($tree);
            $aksesCount = count(array_filter($this->leafFormIds($tree), function ($formId) use ($tree) {
                $flags = $this->flagsOf($tree, $formId);

                return $flags['create'] || $flags['read'] || $flags['update'] || $flags['delete'];
            }));
        }

        return view('moduls.Administrator.ManajemenEMR.AksesEhr.akses_ehr', compact(
            'profesis',
            'profesiId',
            'tree',
            'aksesCount',
            'totalLeaf'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'profesi_id' => 'required|integer|exists:profesi,profesi_id',
            'create_form_ids' => 'nullable|array',
            'create_form_ids.*' => 'integer|exists:form,form_id',
            'read_form_ids' => 'nullable|array',
            'read_form_ids.*' => 'integer|exists:form,form_id',
            'update_form_ids' => 'nullable|array',
            'update_form_ids.*' => 'integer|exists:form,form_id',
            'delete_form_ids' => 'nullable|array',
            'delete_form_ids.*' => 'integer|exists:form,form_id',
        ]);

        $profesiId = (int) $request->input('profesi_id');
        $sel = [
            'create' => $this->ints($request->input('create_form_ids')),
            'read' => $this->ints($request->input('read_form_ids')),
            'update' => $this->ints($request->input('update_form_ids')),
            'delete' => $this->ints($request->input('delete_form_ids')),
        ];

        $tree = $this->buildTree($profesiId);
        $leafIds = $this->leafFormIds($tree);
        $namaProfesi = DB::table('profesi')->where('profesi_id', $profesiId)->value('nama_profesi');

        DB::beginTransaction();
        try {
            foreach ($leafIds as $formId) {
                $flags = [
                    'create' => in_array($formId, $sel['create']),
                    'read' => in_array($formId, $sel['read']),
                    'update' => in_array($formId, $sel['update']),
                    'delete' => in_array($formId, $sel['delete']),
                ];

                $existing = DB::table('akses_ehr')
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->where('profesi_id', $profesiId)
                    ->where('form_id', $formId)
                    ->first();

                $adaAkses = $flags['create'] || $flags['read'] || $flags['update'] || $flags['delete'];

                if (! $adaAkses) {
                    if ($existing) {
                        DB::table('akses_ehr')
                            ->where('akses_ehr_id', $existing->akses_ehr_id)
                            ->update([
                                'status_batal' => 1,
                                'mod_time' => now(),
                                'mod_user_id' => Auth::id(),
                            ]);
                    }

                    continue;
                }

                $payload = [
                    'akses_create' => $flags['create'] ? 1 : 0,
                    'akses_read' => $flags['read'] ? 1 : 0,
                    'akses_update' => $flags['update'] ? 1 : 0,
                    'akses_delete' => $flags['delete'] ? 1 : 0,
                    'mod_time' => now(),
                    'mod_user_id' => Auth::id(),
                ];

                if ($existing) {
                    DB::table('akses_ehr')
                        ->where('akses_ehr_id', $existing->akses_ehr_id)
                        ->update($payload);
                } else {
                    DB::table('akses_ehr')->insert(array_merge([
                        'profesi_id' => $profesiId,
                        'form_id' => $formId,
                        'level_id' => null,
                        'bagian_id' => null,
                        'input_time' => now(),
                        'input_user_id' => Auth::id(),
                        'status_batal' => 0,
                    ], $payload));
                }
            }

            DB::commit();

            return redirect()->route('admin.akses_ehr.index', ['profesi_id' => $profesiId])
                ->with('success', 'Akses EHR untuk profesi '.($namaProfesi ?? '-').' berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan akses EHR: '.$e->getMessage())->withInput();
        }
    }

    private function buildTree(int $profesiId): array
    {
        $menus = DB::table('dashboard_menu')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('dashboard_menu_id')
            ->get();

        $subs = DB::table('dashboard_menu_sub')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('dashboard_menu_sub_id')
            ->get();

        $extras = DB::table('dashboard_menu_sub_extra')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('dashboard_menu_sub_extra_id')
            ->get();

        $forms = DB::table('form')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->whereNotNull('id_dash_menu')
            ->where('id_dash_menu', '<>', '')
            ->get();

        $flagMap = [];
        foreach (DB::table('akses_ehr')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->where('profesi_id', $profesiId)
            ->get() as $r) {
            $flagMap[$r->form_id] = [
                'create' => (int) $r->akses_create === 1,
                'read' => (int) $r->akses_read === 1,
                'update' => (int) $r->akses_update === 1,
                'delete' => (int) $r->akses_delete === 1,
            ];
        }

        $defaultFlags = ['create' => false, 'read' => false, 'update' => false, 'delete' => false];

        $tree = [];

        foreach ($menus as $menu) {
            $menuNode = ['id' => $menu->dashboard_menu_id, 'nama' => $menu->nama_menu, 'subs' => []];

            foreach ($subs->where('dashboard_menu_id', $menu->dashboard_menu_id) as $sub) {
                $subNode = [
                    'id' => $sub->dashboard_menu_sub_id,
                    'nama' => $sub->nama_sub_menu,
                    'leaf' => null,
                    'extras' => [],
                ];

                $subExtras = $extras->where('dashboard_menu_sub_id', $sub->dashboard_menu_sub_id);

                if ($subExtras->isEmpty()) {
                    $form = $forms->firstWhere('id_dash_menu', $menu->dashboard_menu_id.'.'.$sub->dashboard_menu_sub_id);
                    if ($form) {
                        $subNode['leaf'] = $this->leaf($form, $flagMap[$form->form_id] ?? $defaultFlags);
                    }
                } else {
                    foreach ($subExtras as $extra) {
                        $form = $forms->firstWhere('id_dash_menu', $menu->dashboard_menu_id.'.'.$sub->dashboard_menu_sub_id.'.'.$extra->dashboard_menu_sub_extra_id);
                        if ($form) {
                            $subNode['extras'][] = $this->leaf($form, $flagMap[$form->form_id] ?? $defaultFlags, $extra->nama_sub_menu_extra);
                        }
                    }
                }

                $menuNode['subs'][] = $subNode;
            }

            $tree[] = $menuNode;
        }

        return $tree;
    }

    private function leaf(object $form, array $flags, ?string $namaExtra = null): array
    {
        return [
            'form_id' => $form->form_id,
            'nama' => $form->nama_form,
            'id_dash_menu' => $form->id_dash_menu,
            'nama_extra' => $namaExtra,
            'flags' => $flags,
        ];
    }

    private function leafFormIds(array $tree): array
    {
        $ids = [];

        foreach ($tree as $menu) {
            foreach ($menu['subs'] as $sub) {
                if ($sub['leaf']) {
                    $ids[] = $sub['leaf']['form_id'];
                }

                foreach ($sub['extras'] as $extra) {
                    $ids[] = $extra['form_id'];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function flagsOf(array $tree, int $formId): array
    {
        foreach ($tree as $menu) {
            foreach ($menu['subs'] as $sub) {
                if ($sub['leaf'] && $sub['leaf']['form_id'] === $formId) {
                    return $sub['leaf']['flags'];
                }

                foreach ($sub['extras'] as $extra) {
                    if ($extra['form_id'] === $formId) {
                        return $extra['flags'];
                    }
                }
            }
        }

        return ['create' => false, 'read' => false, 'update' => false, 'delete' => false];
    }

    private function countLeaf(array $tree): int
    {
        return count($this->leafFormIds($tree));
    }

    private function ints(mixed $values): array
    {
        return array_values(array_unique(array_map('intval', (array) $values)));
    }

    private function profesis()
    {
        return DB::table('profesi')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('profesi_id')
            ->get();
    }
}
