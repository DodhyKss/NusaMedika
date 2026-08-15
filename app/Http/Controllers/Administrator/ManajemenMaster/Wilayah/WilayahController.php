<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Wilayah;

use App\Helpers\GenerateHelper;
use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WilayahController extends Controller
{
    private const TABS = ['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'];

    public function index(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        $provinsis = Provinsi::aktif()->orderBy('nama_provinsi')->get();

        $data = match ($tab) {
            'kabupaten' => Kabupaten::aktif()
                ->with(['provinsi' => fn ($q) => $q->aktif()])
                ->when($request->filled('provinsi_id'), fn ($q) => $q->where('provinsi_id', $request->input('provinsi_id')))
                ->withCount(['kecamatan' => fn ($q) => $q->aktif()])
                ->orderBy('nama_kabupaten')
                ->get(),
            'kecamatan' => Kecamatan::aktif()
                ->with(['kabupaten' => fn ($q) => $q->aktif()])
                ->when($request->filled('kabupaten_id'), fn ($q) => $q->where('kabupaten_id', $request->input('kabupaten_id')))
                ->withCount(['kelurahan' => fn ($q) => $q->aktif()])
                ->orderBy('nama_kecamatan')
                ->get(),
            'kelurahan' => Kelurahan::aktif()
                ->with(['kecamatan' => fn ($q) => $q->aktif()])
                ->when($request->filled('kecamatan_id'), fn ($q) => $q->where('kecamatan_id', $request->input('kecamatan_id')))
                ->orderBy('nama_kelurahan')
                ->get(),
            default => Provinsi::aktif()->withCount(['kabupaten' => fn ($q) => $q->aktif()])->orderBy('nama_provinsi')->get(),
        };

        $kabupatens = Kabupaten::aktif()->orderBy('nama_kabupaten')->get();
        $kecamatans = Kecamatan::aktif()->orderBy('nama_kecamatan')->get();

        return view('moduls.administrator.manajemen_master.wilayah.index', compact('tab', 'data', 'provinsis', 'kabupatens', 'kecamatans'));
    }

    public function create(Request $request)
    {
        $tab = $this->resolveTab($request->input('tab'));

        return view('moduls.administrator.manajemen_master.wilayah.form', $this->formData($tab));
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

            return redirect()->route('admin.wilayah.index', ['tab' => $tab])->with('success', 'Data '.ucfirst($tab).' berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan data: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $tab = $this->resolveTab($request->input('tab'));
        $wilayah = $this->findRecord($tab, $id);

        return view('moduls.administrator.manajemen_master.wilayah.form', array_merge(['wilayah' => $wilayah], $this->formData($tab)));
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

            return redirect()->route('admin.wilayah.index', ['tab' => $tab])->with('success', 'Data '.ucfirst($tab).' berhasil diperbarui.');
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

            return redirect()->route('admin.wilayah.index', ['tab' => $tab])->with('success', 'Data '.ucfirst($tab).' beserta turunannya berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus data: '.$e->getMessage());
        }
    }

    private function resolveTab($tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'provinsi';
    }

    private function formData(string $tab): array
    {
        return [
            'tab' => $tab,
            'provinsis' => Provinsi::aktif()->orderBy('nama_provinsi')->get(),
            'kabupatens' => Kabupaten::aktif()->orderBy('nama_kabupaten')->get(),
            'kecamatans' => Kecamatan::aktif()->orderBy('nama_kecamatan')->get(),
        ];
    }

    private function validatedData(Request $request, string $tab): array
    {
        return match ($tab) {
            'kabupaten' => $request->validate([
                'nama_kabupaten' => 'required|string|max:255',
                'provinsi_id' => 'required|integer|exists:provinsi,provinsi_id',
                'kode_wilayah_kabupaten' => 'nullable|integer',
            ]),
            'kecamatan' => $request->validate([
                'nama_kecamatan' => 'required|string|max:255',
                'kabupaten_id' => 'required|integer|exists:kabupaten,kabupaten_id',
                'kode_wilayah_kecamatan' => 'nullable|integer',
            ]),
            'kelurahan' => $request->validate([
                'nama_kelurahan' => 'required|string|max:255',
                'kecamatan_id' => 'required|integer|exists:kecamatan,kecamatan_id',
                'kode_wilayah_kelurahan' => 'nullable|integer',
            ]),
            default => $request->validate([
                'nama_provinsi' => 'required|string|max:255',
                'kode_wilayah_provinsi' => 'nullable|integer',
            ]),
        };
    }

    private function createRecord(string $tab, array $data): void
    {
        match ($tab) {
            'kabupaten' => $this->saveRecord(new Kabupaten, [
                'kabupaten_id' => GenerateHelper::getNextId('kabupaten'),
                'provinsi_id' => $data['provinsi_id'],
                'nama_kabupaten' => $data['nama_kabupaten'],
                'kode_wilayah_kabupaten' => $data['kode_wilayah_kabupaten'] ?? null,
                'status_batal' => 0,
            ]),
            'kecamatan' => $this->saveRecord(new Kecamatan, [
                'kecamatan_id' => GenerateHelper::getNextId('kecamatan'),
                'kabupaten_id' => $data['kabupaten_id'],
                'nama_kecamatan' => $data['nama_kecamatan'],
                'kode_wilayah_kecamatan' => $data['kode_wilayah_kecamatan'] ?? null,
                'status_batal' => 0,
            ]),
            'kelurahan' => $this->saveRecord(new Kelurahan, [
                'kelurahan_id' => GenerateHelper::getNextId('kelurahan'),
                'kecamatan_id' => $data['kecamatan_id'],
                'nama_kelurahan' => $data['nama_kelurahan'],
                'kode_wilayah_kelurahan' => $data['kode_wilayah_kelurahan'] ?? null,
                'status_batal' => 0,
            ]),
            default => $this->saveRecord(new Provinsi, [
                'provinsi_id' => GenerateHelper::getNextId('provinsi'),
                'nama_provinsi' => $data['nama_provinsi'],
                'kode_wilayah_provinsi' => $data['kode_wilayah_provinsi'] ?? null,
                'status_batal' => 0,
            ]),
        };
    }

    private function saveRecord($model, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $model->{$key} = $value;
        }
        $model->save();
    }

    private function findRecord(string $tab, $id)
    {
        return match ($tab) {
            'kabupaten' => Kabupaten::findOrFail($id),
            'kecamatan' => Kecamatan::findOrFail($id),
            'kelurahan' => Kelurahan::findOrFail($id),
            default => Provinsi::findOrFail($id),
        };
    }

    private function updateRecord(string $tab, $id, array $data): void
    {
        match ($tab) {
            'kabupaten' => Kabupaten::findOrFail($id)->update([
                'provinsi_id' => $data['provinsi_id'],
                'nama_kabupaten' => $data['nama_kabupaten'],
                'kode_wilayah_kabupaten' => $data['kode_wilayah_kabupaten'] ?? null,
            ]),
            'kecamatan' => Kecamatan::findOrFail($id)->update([
                'kabupaten_id' => $data['kabupaten_id'],
                'nama_kecamatan' => $data['nama_kecamatan'],
                'kode_wilayah_kecamatan' => $data['kode_wilayah_kecamatan'] ?? null,
            ]),
            'kelurahan' => Kelurahan::findOrFail($id)->update([
                'kecamatan_id' => $data['kecamatan_id'],
                'nama_kelurahan' => $data['nama_kelurahan'],
                'kode_wilayah_kelurahan' => $data['kode_wilayah_kelurahan'] ?? null,
            ]),
            default => Provinsi::findOrFail($id)->update([
                'nama_provinsi' => $data['nama_provinsi'],
                'kode_wilayah_provinsi' => $data['kode_wilayah_provinsi'] ?? null,
            ]),
        };
    }

    private function softDeleteRecord(string $tab, $id): void
    {
        match ($tab) {
            'kabupaten' => [
                $kabupaten = Kabupaten::findOrFail($id),
                $kecamatanIds = $kabupaten->kecamatan()->pluck('kecamatan_id')->all(),
                Kecamatan::where('kabupaten_id', $kabupaten->kabupaten_id)->update(['status_batal' => 1]),
                Kelurahan::whereIn('kecamatan_id', $kecamatanIds)->update(['status_batal' => 1]),
                $kabupaten->update(['status_batal' => 1]),
            ],
            'kecamatan' => [
                $kecamatan = Kecamatan::findOrFail($id),
                Kelurahan::where('kecamatan_id', $kecamatan->kecamatan_id)->update(['status_batal' => 1]),
                $kecamatan->update(['status_batal' => 1]),
            ],
            'kelurahan' => [
                Kelurahan::findOrFail($id)->update(['status_batal' => 1]),
            ],
            default => [
                $provinsi = Provinsi::findOrFail($id),
                $kabupatenIds = $provinsi->kabupaten()->pluck('kabupaten_id')->all(),
                $kecamatanIds = Kecamatan::whereIn('kabupaten_id', $kabupatenIds)->pluck('kecamatan_id')->all(),
                Kelurahan::whereIn('kecamatan_id', $kecamatanIds)->update(['status_batal' => 1]),
                Kecamatan::whereIn('kabupaten_id', $kabupatenIds)->update(['status_batal' => 1]),
                Kabupaten::whereIn('provinsi_id', [$provinsi->provinsi_id])->update(['status_batal' => 1]),
                $provinsi->update(['status_batal' => 1]),
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
