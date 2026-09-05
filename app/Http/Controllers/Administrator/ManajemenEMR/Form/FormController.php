<?php

namespace App\Http\Controllers\Administrator\ManajemenEMR\Form;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    private const TABS = ['form', 'objek', 'mapping'];

    private const PK = [
        'form' => 'form_id',
        'objek' => 'objek_id',
        'mapping' => 'objek_form_control_id',
    ];

    private const TABLE = [
        'form' => 'form',
        'objek' => 'objek',
        'mapping' => 'objek_form_control',
    ];

    public function index(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        $forms = $this->aktif('form')->orderBy('form_id')->get();
        $objeks = $this->aktif('objek')->orderBy('objek_id')->get();

        $data = match ($tab) {
            'objek' => $this->aktif('objek as o')
                ->addSelect('o.*')
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM objek_form_control ofc JOIN form f ON f.form_id = ofc.form_id AND (f.status_batal != 1 OR f.status_batal IS NULL) WHERE ofc.objek_id = o.objek_id AND (ofc.status_batal != 1 OR ofc.status_batal IS NULL)) AS form_count'))
                ->orderBy('o.objek_id')
                ->get(),
            'mapping' => DB::table('objek_form_control as ofc')
                ->join('form as f', 'f.form_id', '=', 'ofc.form_id')
                ->leftJoin('objek as o', function ($join) {
                    $join->on('o.objek_id', '=', 'ofc.objek_id')
                        ->where(function ($q) {
                            $q->whereNull('o.status_batal')->orWhere('o.status_batal', 0);
                        });
                })
                ->where(function ($q) {
                    $q->whereNull('ofc.status_batal')->orWhere('ofc.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('f.status_batal')->orWhere('f.status_batal', 0);
                })
                ->when($request->filled('form_id'), fn ($q) => $q->where('ofc.form_id', $request->integer('form_id')))
                ->addSelect('ofc.*', 'f.nama_form', 'f.slug', 'o.nama_objek')
                ->orderBy('ofc.form_id')
                ->orderBy('ofc.objek_form_control_id')
                ->get(),
            default => $this->aktif('form as f')
                ->addSelect('f.*')
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM objek_form_control ofc WHERE ofc.form_id = f.form_id AND (ofc.status_batal != 1 OR ofc.status_batal IS NULL)) AS objek_count'))
                ->orderBy('f.form_id')
                ->get(),
        };

        return view('moduls.Administrator.ManajemenEMR.Form.form', compact('tab', 'data', 'forms', 'objeks'));
    }

    public function create(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        return view('moduls.Administrator.ManajemenEMR.Form.form_form', $this->formData($tab));
    }

    public function store(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $data = $this->validatedData($request, $tab);

        DB::beginTransaction();
        try {
            DB::table(self::TABLE[$tab])->insert(array_merge($this->values($tab, $data), [
                'input_time' => now(),
                'input_user_id' => Auth::id(),
                'status_batal' => 0,
            ]));
            DB::commit();

            return redirect()->route('admin.form.index', ['tab' => $tab])->with('success', 'Data '.self::LABEL[$tab].' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $row = $this->findRecord($tab, $id);

        return view('moduls.Administrator.ManajemenEMR.Form.form_form', array_merge(['row' => $row], $this->formData($tab)));
    }

    public function update(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $data = $this->validatedData($request, $tab, $id);

        DB::beginTransaction();
        try {
            $this->updateRecord($tab, $id, $data);
            DB::commit();

            return redirect()->route('admin.form.index', ['tab' => $tab])->with('success', 'Data '.self::LABEL[$tab].' berhasil diperbarui.');
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

            return redirect()->route('admin.form.index', ['tab' => $tab])->with('success', 'Data '.self::LABEL[$tab].' beserta relasinya berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    private const LABEL = [
        'form' => 'form',
        'objek' => 'objek',
        'mapping' => 'mapping',
    ];

    private function resolveTab($tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'form';
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
            'forms' => $this->aktif('form')->orderBy('form_id')->get(),
            'objeks' => $this->aktif('objek')->orderBy('objek_id')->get(),
            'menus' => $this->aktif('dashboard_menu')->orderBy('dashboard_menu_id')->get(),
            'subs' => $this->aktif('dashboard_menu_sub')->orderBy('dashboard_menu_sub_id')->get(),
            'extras' => $this->aktif('dashboard_menu_sub_extra')->orderBy('dashboard_menu_sub_extra_id')->get(),
        ];
    }

    private function validatedData(Request $request, string $tab, $ignoreId = null): array
    {
        return match ($tab) {
            'objek' => $request->validate([
                'nama_objek' => 'required|string|max:100',
            ]),
            'mapping' => $request->validate([
                'form_id' => 'required|integer|exists:form,form_id',
                'objek_id' => 'required|integer|exists:objek,objek_id',
                'variabel' => 'required|string|max:250',
            ]),
            default => $request->validate([
                'nama_form' => 'required|string|max:100',
                'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('form', 'slug')->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereNull('status_batal')->orWhere('status_batal', 0);
                    });
                })->ignore($ignoreId, 'form_id')],
                'id_dash_menu' => 'nullable|string|max:10',
                'ri' => 'nullable|boolean',
                'rj' => 'nullable|boolean',
                'igd' => 'nullable|boolean',
                'mcu' => 'nullable|boolean',
            ]),
        };
    }

    private function values(string $tab, array $data): array
    {
        return match ($tab) {
            'objek' => [
                'nama_objek' => $data['nama_objek'],
            ],
            'mapping' => [
                'form_id' => $data['form_id'],
                'objek_id' => $data['objek_id'],
                'variabel' => $data['variabel'],
            ],
            default => [
                'nama_form' => $data['nama_form'],
                'slug' => $data['slug'],
                'id_dash_menu' => $data['id_dash_menu'] ?? null,
                'ri' => isset($data['ri']) ? 1 : 0,
                'rj' => isset($data['rj']) ? 1 : 0,
                'igd' => isset($data['igd']) ? 1 : 0,
                'mcu' => isset($data['mcu']) ? 1 : 0,
            ],
        };
    }

    private function updateRecord(string $tab, $id, array $data): void
    {
        $this->findRecord($tab, $id);

        DB::table(self::TABLE[$tab])->where(self::PK[$tab], $id)->update(array_merge($this->values($tab, $data), [
            'mod_time' => now(),
            'mod_user_id' => Auth::id(),
        ]));
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
            'objek' => [
                DB::table('objek_form_control')->where('objek_id', $id)->update($soft),
                DB::table('objek')->where('objek_id', $id)->update($soft),
            ],
            'mapping' => [
                DB::table('objek_form_control')->where('objek_form_control_id', $id)->update($soft),
            ],
            default => [
                DB::table('objek_form_control')->where('form_id', $id)->update($soft),
                DB::table('akses_ehr')->where('form_id', $id)->update($soft),
                DB::table('form')->where('form_id', $id)->update($soft),
            ],
        };
    }
}
