<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\MasterGroupTindakan;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\GroupTindakan;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterGroupTindakanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $bagianId = (int) $request->input('bagian_id');

        $query = GroupTindakan::aktif()->with('bagian')->withCount('tindakan');

        if ($search !== '') {
            $query->where('nama_group_tindakan', 'like', "%{$search}%");
        }

        if ($bagianId) {
            $query->where('bagian_id', $bagianId);
        }

        $groupList = $query->orderBy('nama_group_tindakan')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.MasterGroupTindakan.master_group_tindakan', array_merge(
            compact('groupList', 'search', 'bagianId'),
            $this->formData()
        ));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.MasterGroupTindakan.master_group_tindakan_create', array_merge(
            $this->formData(),
            ['selectedTindakanIds' => []]
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $group = new GroupTindakan;
            $group->nama_group_tindakan = $data['nama_group_tindakan'];
            $group->bagian_id = (int) $data['bagian_id'];
            $group->input_time = now();
            $group->input_user_id = Auth::id();
            $group->status_batal = 0;
            $group->save();

            $this->syncTindakanMappings($group, $data['tindakan_ids']);

            DB::commit();

            return redirect()->route('admin.master_group_tindakan.index')
                ->with('success', 'Group Tindakan berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan group tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function edit($masterGroupTindakan)
    {
        $group = GroupTindakan::findOrFail($masterGroupTindakan);

        $selectedTindakanIds = $group->tindakan()
            ->pluck('tindakan.tindakan_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return view('moduls.Administrator.ManajemenMaster.MasterGroupTindakan.master_group_tindakan_edit', array_merge(
            $this->formData(),
            compact('group', 'selectedTindakanIds')
        ));
    }

    public function update(Request $request, $masterGroupTindakan)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $group = GroupTindakan::findOrFail($masterGroupTindakan);
            $group->nama_group_tindakan = $data['nama_group_tindakan'];
            $group->bagian_id = (int) $data['bagian_id'];
            $group->mod_time = now();
            $group->mod_user_id = Auth::id();
            $group->save();

            $this->syncTindakanMappings($group, $data['tindakan_ids']);

            DB::commit();

            return redirect()->route('admin.master_group_tindakan.index')
                ->with('success', 'Group Tindakan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui group tindakan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($masterGroupTindakan)
    {
        DB::beginTransaction();
        try {
            $group = GroupTindakan::findOrFail($masterGroupTindakan);
            $group->status_batal = 1;
            $group->mod_time = now();
            $group->mod_user_id = Auth::id();
            $group->save();

            $this->softDeleteTindakanMappings((int) $group->group_tindakan_id);

            DB::commit();

            return redirect()->route('admin.master_group_tindakan.index')
                ->with('success', 'Group Tindakan berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus group tindakan: '.$e->getMessage());
        }
    }

    /**
     * Sync pivot group_tindakan_tindakan: soft-delete mapping aktif lama, insert ulang.
     */
    private function syncTindakanMappings(GroupTindakan $group, array $tindakanIds): void
    {
        $this->softDeleteTindakanMappings((int) $group->group_tindakan_id);

        foreach (array_unique($tindakanIds) as $tindakanId) {
            DB::table('group_tindakan_tindakan')->insert([
                'group_tindakan_id' => $group->group_tindakan_id,
                'tindakan_id' => (int) $tindakanId,
                'input_time' => now(),
                'input_user_id' => Auth::id(),
                'status_batal' => 0,
            ]);
        }
    }

    private function softDeleteTindakanMappings(int $groupTindakanId): void
    {
        DB::table('group_tindakan_tindakan')
            ->where('group_tindakan_id', $groupTindakanId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->update([
                'status_batal' => 1,
                'mod_time' => now(),
                'mod_user_id' => Auth::id(),
            ]);
    }

    private function validated(Request $request)
    {
        return array_merge([
            'tindakan_ids' => [],
        ], $request->validate([
            'nama_group_tindakan' => 'required|string|max:100',
            'bagian_id' => 'required|integer|exists:bagian,bagian_id',
            'tindakan_ids' => 'required|array|min:1',
            'tindakan_ids.*' => 'integer|exists:tindakan,tindakan_id',
        ]));
    }

    private function formData(): array
    {
        return [
            'bagianList' => Bagian::aktif()
                ->where('referensi_bagian_id', 6)
                ->orderBy('nama_bagian')
                ->get(),
            'tindakanList' => Tindakan::aktif()
                ->with('kategori')
                ->orderBy('nama_tindakan')
                ->get(),
        ];
    }
}
