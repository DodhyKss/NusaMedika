<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Bagian;

use App\Helpers\SequenceHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\ReferensiBagian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BagianController extends Controller
{
    public function index()
    {
        $bagians = Bagian::with('referensi')->where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_bagian')->get();

        return view('moduls.administrator.manajemen_master.bagian.index', compact('bagians'));
    }

    public function create()
    {
        return view('moduls.administrator.manajemen_master.bagian.create', $this->dropdownData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bagian' => 'required|string|max:100',
            'referensi_bagian' => 'required|integer|exists:referensi_bagian,referensi_bagian_id',
            'group_bagian' => 'nullable|string|max:10',
            'seri_bagian' => 'nullable|string|max:20',
            'id_satu_sehat' => 'nullable|string|max:50',
            'flag_eksekutif' => 'nullable|boolean',
            'id_location' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $bagian = new Bagian;
            $bagian->bagian_id = SequenceHelper::getNextId('bagian');
            $bagian->nama_bagian = $request->nama_bagian;
            $bagian->referensi_bagian = $request->referensi_bagian;
            $bagian->group_bagian = $request->group_bagian;
            $bagian->seri_bagian = $request->seri_bagian;
            $bagian->id_satu_sehat = $request->id_satu_sehat;
            $bagian->flag_eksekutif = $request->boolean('flag_eksekutif');
            $bagian->id_location = $request->id_location;
            $bagian->input_time = now();
            $bagian->input_user_id = Auth::id();
            $bagian->status_batal = 0;
            $bagian->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan bagian: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $bagian = Bagian::findOrFail($id);

        return view('moduls.administrator.manajemen_master.bagian.edit', array_merge(compact('bagian'), $this->dropdownData()));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_bagian' => 'required|string|max:100',
            'referensi_bagian' => 'nullable|integer|exists:referensi_bagian,referensi_bagian_id',
            'group_bagian' => 'nullable|string|max:10',
            'seri_bagian' => 'nullable|string|max:20',
            'id_satu_sehat' => 'nullable|string|max:50',
            'flag_eksekutif' => 'nullable|boolean',
            'id_location' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $bagian = Bagian::findOrFail($id);
            $bagian->nama_bagian = $request->nama_bagian;
            $bagian->referensi_bagian = $request->referensi_bagian;
            $bagian->group_bagian = $request->group_bagian;
            $bagian->seri_bagian = $request->seri_bagian;
            $bagian->id_satu_sehat = $request->id_satu_sehat;
            $bagian->flag_eksekutif = $request->boolean('flag_eksekutif');
            $bagian->id_location = $request->id_location;
            $bagian->mod_time = now();
            $bagian->mod_user_id = Auth::id();
            $bagian->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui bagian: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bagian = Bagian::findOrFail($id);
            $bagian->status_batal = 1;
            $bagian->mod_time = now();
            $bagian->mod_user_id = Auth::id();
            $bagian->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.bagian.index')->with('success', 'Bagian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus bagian: '.$e->getMessage());
        }
    }

    private function dropdownData()
    {
        $referensiBagians = ReferensiBagian::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('referensi_bagian_id')->get();

        return compact('referensiBagians');
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
