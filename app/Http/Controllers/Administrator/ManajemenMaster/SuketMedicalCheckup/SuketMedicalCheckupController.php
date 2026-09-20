<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\SuketMedicalCheckup;

use App\Http\Controllers\Controller;
use App\Models\SuketMcu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SuketMedicalCheckupController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = SuketMcu::aktif();

        if ($search !== '') {
            $query->where('nama_suket', 'like', "%{$search}%");
        }

        $suketList = $query->orderBy('nama_suket')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.SuketMedicalCheckup.suket_medical_checkup', compact('suketList', 'search'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.SuketMedicalCheckup.suket_medical_checkup_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $suket = new SuketMcu;
            $suket->nama_suket = $data['nama_suket'];
            $suket->harga = $data['harga'];
            $suket->input_time = now();
            $suket->input_user_id = Auth::id();
            $suket->status_batal = 0;
            $suket->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.suket_medical_checkup.index')->with('success', 'Suket Medical Checkup berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan suket: '.$e->getMessage())->withInput();
        }
    }

    public function edit($suket)
    {
        $suket = SuketMcu::findOrFail($suket);

        return view('moduls.Administrator.ManajemenMaster.SuketMedicalCheckup.suket_medical_checkup_edit', compact('suket'));
    }

    public function update(Request $request, $suket)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $suket = SuketMcu::findOrFail($suket);
            $suket->nama_suket = $data['nama_suket'];
            $suket->harga = $data['harga'];
            $suket->mod_time = now();
            $suket->mod_user_id = Auth::id();
            $suket->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.suket_medical_checkup.index')->with('success', 'Suket Medical Checkup berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui suket: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($suket)
    {
        DB::beginTransaction();
        try {
            $suket = SuketMcu::findOrFail($suket);
            $suket->status_batal = 1;
            $suket->mod_time = now();
            $suket->mod_user_id = Auth::id();
            $suket->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.suket_medical_checkup.index')->with('success', 'Suket Medical Checkup berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus suket: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'nama_suket' => 'required|string|max:150',
            'harga' => 'required|numeric|min:0',
        ]);

        return array_merge([
            'harga' => 0,
        ], $data);
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
