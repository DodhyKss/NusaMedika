<?php

namespace App\Http\Controllers\Administrator\ManajemenMaster\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    protected const JENIS = 'SUPPLIER';

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $query = Supplier::aktif()->where('jenis_supplier', static::JENIS)->with('barangs');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        $supplierList = $query->orderBy('nama_supplier')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Supplier.supplier', compact('supplierList', 'search'));
    }

    public function create()
    {
        $barangList = $this->barangOptions();

        return view('moduls.Administrator.ManajemenMaster.Supplier.supplier_create', compact('barangList'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $supplier = new Supplier;
            $supplier->nama_supplier = $data['nama_supplier'];
            $supplier->jenis_supplier = static::JENIS;
            $supplier->alamat = $data['alamat'];
            $supplier->telepon = $data['telepon'];
            $supplier->email = $data['email'];
            $supplier->npwp = $data['npwp'];
            $supplier->input_time = now();
            $supplier->input_user_id = Auth::id();
            $supplier->status_batal = 0;
            $supplier->save();

            $this->syncBarangMappings($supplier, $data['barang_ids'] ?? []);

            DB::commit();

            return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan supplier: '.$e->getMessage())->withInput();
        }
    }

    public function edit($supplier)
    {
        $supplier = Supplier::findOrFail($supplier);

        $barangList = $this->barangOptions();
        $selectedBarangIds = $supplier->barangs()->pluck('barang.barang_id')->map(fn ($id) => (string) $id)->all();

        return view('moduls.Administrator.ManajemenMaster.Supplier.supplier_edit', compact('supplier', 'barangList', 'selectedBarangIds'));
    }

    public function update(Request $request, $supplier)
    {
        $data = $this->validated($request);

        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($supplier);
            $supplier->nama_supplier = $data['nama_supplier'];
            $supplier->alamat = $data['alamat'];
            $supplier->telepon = $data['telepon'];
            $supplier->email = $data['email'];
            $supplier->npwp = $data['npwp'];
            $supplier->mod_time = now();
            $supplier->mod_user_id = Auth::id();
            $supplier->save();

            $this->syncBarangMappings($supplier, $data['barang_ids'] ?? []);

            DB::commit();

            return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui supplier: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($supplier)
    {
        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($supplier);
            $supplier->status_batal = 1;
            $supplier->mod_time = now();
            $supplier->mod_user_id = Auth::id();
            $supplier->save();

            $this->softDeleteBarangMappings($supplier->supplier_id);

            DB::commit();

            return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menghapus supplier: '.$e->getMessage());
        }
    }

    private function barangOptions()
    {
        return Barang::aktif()->orderBy('nama_barang')->get();
    }

    private function syncBarangMappings(Supplier $supplier, array $barangIds): void
    {
        $this->softDeleteBarangMappings($supplier->supplier_id);

        foreach (array_unique($barangIds) as $barangId) {
            DB::table('barang_supplier')->insert([
                'barang_id' => $barangId,
                'supplier_id' => $supplier->supplier_id,
                'input_time' => now(),
                'input_user_id' => Auth::id(),
                'status_batal' => 0,
            ]);
        }
    }

    private function softDeleteBarangMappings(int $supplierId): void
    {
        DB::table('barang_supplier')
            ->where('supplier_id', $supplierId)
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
            'alamat' => null,
            'telepon' => null,
            'email' => null,
            'npwp' => null,
            'barang_ids' => [],
        ], $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'npwp' => 'nullable|string|max:30',
            'barang_ids' => 'nullable|array',
            'barang_ids.*' => 'integer|exists:barang,barang_id',
        ]));
    }
}
