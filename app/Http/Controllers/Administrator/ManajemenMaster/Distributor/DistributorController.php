<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{
    protected const JENIS = 'DISTRIBUTOR';

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Supplier::aktif()->where('jenis_supplier', static::JENIS);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'ilike', "%{$search}%")
                    ->orWhere('telepon', 'ilike', "%{$search}%");
            });
        }

        $distributorList = $query->orderBy('nama_supplier')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Distributor.distributor', compact('distributorList', 'search'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Distributor.distributor_create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $distributor = new Supplier;
            $distributor->nama_supplier = $data['nama_supplier'];
            $distributor->jenis_supplier = static::JENIS;
            $distributor->alamat = $data['alamat'];
            $distributor->telepon = $data['telepon'];
            $distributor->email = $data['email'];
            $distributor->npwp = $data['npwp'];
            $distributor->input_time = now();
            $distributor->input_user_id = Auth::id();
            $distributor->status_batal = 0;
            $distributor->save();

            DB::commit();

            return redirect()->route('admin.distributor.index')->with('success', 'Distributor berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan distributor: '.$e->getMessage())->withInput();
        }
    }

    public function edit($distributor)
    {
        $distributor = Supplier::findOrFail($distributor);

        return view('moduls.Administrator.ManajemenMaster.Distributor.distributor_edit', compact('distributor'));
    }

    public function update(Request $request, $distributor)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $distributor = Supplier::findOrFail($distributor);
            $distributor->nama_supplier = $data['nama_supplier'];
            $distributor->alamat = $data['alamat'];
            $distributor->telepon = $data['telepon'];
            $distributor->email = $data['email'];
            $distributor->npwp = $data['npwp'];
            $distributor->mod_time = now();
            $distributor->mod_user_id = Auth::id();
            $distributor->save();

            DB::commit();

            return redirect()->route('admin.distributor.index')->with('success', 'Distributor berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui distributor: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($distributor)
    {
        DB::beginTransaction();
        try {
            $distributor = Supplier::findOrFail($distributor);
            $distributor->status_batal = 1;
            $distributor->mod_time = now();
            $distributor->mod_user_id = Auth::id();
            $distributor->save();

            DB::commit();

            return redirect()->route('admin.distributor.index')->with('success', 'Distributor berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus distributor: '.$e->getMessage());
        }
    }

    private function validated(Request $request)
    {
        return array_merge([
            'alamat' => null,
            'telepon' => null,
            'email' => null,
            'npwp' => null,
        ], $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'npwp' => 'nullable|string|max:30',
        ]));
    }
}
