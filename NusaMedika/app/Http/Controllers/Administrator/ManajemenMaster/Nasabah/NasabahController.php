<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NasabahController extends Controller
{
    public function index(Request $request)
    {
        $nasabahId = (int) $request->input('nasabah_id');

        $query = Nasabah::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });

        if ($nasabahId) {
            $query->where('nasabah_id', $nasabahId);
        }

        $nasabahs = $query->orderBy('nama_nasabah')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Nasabah.nasabah', compact('nasabahs'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Nasabah.nasabah_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $nasabah = new Nasabah;
            $nasabah->nama_nasabah = $data['nama_nasabah'];
            $nasabah->email_nasabah = $data['email_nasabah'];
            $nasabah->alamat_nasabah = $data['alamat_nasabah'];
            $nasabah->input_time = now();
            $nasabah->input_user_id = Auth::id();
            $nasabah->status_batal = 0;
            $nasabah->save();

            DB::commit();

            return redirect()->route('admin.nasabah.index')->with('success', 'Nasabah berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan nasabah: '.$e->getMessage())->withInput();
        }
    }

    public function edit($nasabah)
    {
        $nasabah = Nasabah::findOrFail($nasabah);

        return view('moduls.Administrator.ManajemenMaster.Nasabah.nasabah_edit', compact('nasabah'));
    }

    public function update(Request $request, $nasabah)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $nasabah = Nasabah::findOrFail($nasabah);
            $nasabah->nama_nasabah = $data['nama_nasabah'];
            $nasabah->email_nasabah = $data['email_nasabah'];
            $nasabah->alamat_nasabah = $data['alamat_nasabah'];
            $nasabah->mod_time = now();
            $nasabah->mod_user_id = Auth::id();
            $nasabah->save();

            DB::commit();

            return redirect()->route('admin.nasabah.index')->with('success', 'Nasabah berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui nasabah: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($nasabah)
    {
        DB::beginTransaction();
        try {
            $nasabah = Nasabah::findOrFail($nasabah);
            $nasabah->status_batal = 1;
            $nasabah->mod_time = now();
            $nasabah->mod_user_id = Auth::id();
            $nasabah->save();

            DB::commit();

            return redirect()->route('admin.nasabah.index')->with('success', 'Nasabah berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus nasabah: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'nama_nasabah' => 'required|string|max:255',
            'email_nasabah' => 'nullable|email|max:100',
            'alamat_nasabah' => 'nullable|string|max:250',
        ]);

        return array_merge([
            'email_nasabah' => null,
            'alamat_nasabah' => null,
        ], $data);
    }
}
