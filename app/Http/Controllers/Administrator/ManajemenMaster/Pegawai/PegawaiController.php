<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Pegawai;

use App\Helpers\SequenceHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Profesi;
use App\Models\StatusKepegawaian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->with(['bagian', 'profesi', 'jabatan', 'statusKepegawaian'])->orderBy('nama_pegawai')->get();

        return view('moduls.administrator.manajemen_master.pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        return view('moduls.administrator.manajemen_master.pegawai.create', $this->dropdownData());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pegawai' => 'required|string|max:100',
            'nip' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:20',
            'bagian_id' => 'nullable|integer|exists:bagian,bagian_id',
            'profesi_id' => 'nullable|integer|exists:profesi,profesi_id',
            'jabatan_id' => 'nullable|integer|exists:jabatan,jabatan_id',
            'status_kepegawaian_id' => 'nullable|integer|exists:status_kepegawaian,status_kepegawaian_id',
            'sip' => 'nullable|string|max:50',
            'tgl_awal_sip' => 'nullable|date',
            'tgl_akhir_sip' => 'nullable|date',
            'str' => 'nullable|string|max:50',
            'tgl_awal_str' => 'nullable|date',
            'tgl_akhir_str' => 'nullable|date',
            'no_rfid' => 'nullable|string|max:50',
            'id_satu_sehat' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = new Pegawai;
            $pegawai->pegawai_id = SequenceHelper::getNextId('pegawai');
            $pegawai->nama_pegawai = $request->nama_pegawai;
            $pegawai->nip = $request->nip;
            $pegawai->nik = $request->nik;
            $pegawai->bagian_id = $request->bagian_id;
            $pegawai->profesi_id = $request->profesi_id;
            $pegawai->jabatan_id = $request->jabatan_id;
            $pegawai->status_kepegawaian_id = $request->status_kepegawaian_id;
            $pegawai->sip = $request->sip;
            $pegawai->tgl_awal_sip = $request->tgl_awal_sip;
            $pegawai->tgl_akhir_sip = $request->tgl_akhir_sip;
            $pegawai->str = $request->str;
            $pegawai->tgl_awal_str = $request->tgl_awal_str;
            $pegawai->tgl_akhir_str = $request->tgl_akhir_str;
            $pegawai->no_rfid = $request->no_rfid;
            $pegawai->id_satu_sehat = $request->id_satu_sehat;
            $pegawai->input_time = now();
            $pegawai->input_user_id = Auth::id();
            $pegawai->status_batal = 0;
            $pegawai->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan pegawai: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        return view('moduls.administrator.manajemen_master.pegawai.edit', array_merge(compact('pegawai'), $this->dropdownData()));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pegawai' => 'required|string|max:100',
            'nip' => 'nullable|string|max:30',
            'nik' => 'nullable|string|max:20',
            'bagian_id' => 'nullable|integer|exists:bagian,bagian_id',
            'profesi_id' => 'nullable|integer|exists:profesi,profesi_id',
            'jabatan_id' => 'nullable|integer|exists:jabatan,jabatan_id',
            'status_kepegawaian_id' => 'nullable|integer|exists:status_kepegawaian,status_kepegawaian_id',
            'sip' => 'nullable|string|max:50',
            'tgl_awal_sip' => 'nullable|date',
            'tgl_akhir_sip' => 'nullable|date',
            'str' => 'nullable|string|max:50',
            'tgl_awal_str' => 'nullable|date',
            'tgl_akhir_str' => 'nullable|date',
            'no_rfid' => 'nullable|string|max:50',
            'id_satu_sehat' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($id);
            $pegawai->nama_pegawai = $request->nama_pegawai;
            $pegawai->nip = $request->nip;
            $pegawai->nik = $request->nik;
            $pegawai->bagian_id = $request->bagian_id;
            $pegawai->profesi_id = $request->profesi_id;
            $pegawai->jabatan_id = $request->jabatan_id;
            $pegawai->status_kepegawaian_id = $request->status_kepegawaian_id;
            $pegawai->sip = $request->sip;
            $pegawai->tgl_awal_sip = $request->tgl_awal_sip;
            $pegawai->tgl_akhir_sip = $request->tgl_akhir_sip;
            $pegawai->str = $request->str;
            $pegawai->tgl_awal_str = $request->tgl_awal_str;
            $pegawai->tgl_akhir_str = $request->tgl_akhir_str;
            $pegawai->no_rfid = $request->no_rfid;
            $pegawai->id_satu_sehat = $request->id_satu_sehat;
            $pegawai->mod_time = now();
            $pegawai->mod_user_id = Auth::id();
            $pegawai->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui pegawai: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pegawai = Pegawai::findOrFail($id);
            $pegawai->status_batal = 1;
            $pegawai->mod_time = now();
            $pegawai->mod_user_id = Auth::id();
            $pegawai->save();

            DB::commit();
            $this->clearSidebarCache();

            return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus pegawai: '.$e->getMessage());
        }
    }

    private function dropdownData()
    {
        $bagians = Bagian::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_bagian')->get();

        $profesis = Profesi::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_profesi')->get();

        $jabatans = Jabatan::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_jabatan')->get();

        $statuses = StatusKepegawaian::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        })->orderBy('nama_status_kepegawaian')->get();

        return compact('bagians', 'profesis', 'jabatans', 'statuses');
    }

    private function clearSidebarCache()
    {
        User::pluck('user_id')->each(function ($userId) {
            Cache::forget('sidebar_moduls_user_'.$userId);
        });
    }
}
