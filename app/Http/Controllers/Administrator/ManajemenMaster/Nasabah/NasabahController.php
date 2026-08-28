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
        $search = trim((string) $request->input('search'));

        $query = Nasabah::where(function ($q) {
            $q->where('status_batal', '!=', 1)->orWhereNull('status_batal');
        });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_nasabah', 'ilike', "%{$search}%")
                    ->orWhere('email_nasabah', 'ilike', "%{$search}%")
                    ->orWhere('telp_nasabah', 'ilike', "%{$search}%");
            });
        }

        $nasabahs = $query->orderBy('nama_nasabah')->paginate(10)->withQueryString();

        return view('moduls.Administrator.ManajemenMaster.Nasabah.index', compact('nasabahs', 'search'));
    }

    public function create()
    {
        return view('moduls.Administrator.ManajemenMaster.Nasabah.create');
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
            $nasabah->telp_nasabah = $data['telp_nasabah'];
            $nasabah->telp_nasabah_2 = $data['telp_nasabah_2'];
            $nasabah->tipe_biaya = $data['tipe_biaya'];
            $nasabah->biaya_administrasi = $data['biaya_administrasi'];
            $nasabah->batas_atas = $data['batas_atas'];
            $nasabah->instalasi = $data['instalasi'];
            $nasabah->cp_nama = $data['cp_nama'];
            $nasabah->cp_telp = $data['cp_telp'];
            $nasabah->cp_nama_2 = $data['cp_nama_2'];
            $nasabah->cp_telp_2 = $data['cp_telp_2'];
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

        return view('moduls.Administrator.ManajemenMaster.Nasabah.edit', compact('nasabah'));
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
            $nasabah->telp_nasabah = $data['telp_nasabah'];
            $nasabah->telp_nasabah_2 = $data['telp_nasabah_2'];
            $nasabah->tipe_biaya = $data['tipe_biaya'];
            $nasabah->biaya_administrasi = $data['biaya_administrasi'];
            $nasabah->batas_atas = $data['batas_atas'];
            $nasabah->instalasi = $data['instalasi'];
            $nasabah->cp_nama = $data['cp_nama'];
            $nasabah->cp_telp = $data['cp_telp'];
            $nasabah->cp_nama_2 = $data['cp_nama_2'];
            $nasabah->cp_telp_2 = $data['cp_telp_2'];
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
            'telp_nasabah' => 'nullable|string|max:20',
            'telp_nasabah_2' => 'nullable|string|max:20',
            'tipe_biaya' => 'nullable|integer',
            'biaya_administrasi' => 'nullable|numeric',
            'batas_atas' => 'nullable|numeric',
            'instalasi' => 'nullable|array',
            'instalasi.*' => 'nullable|string|max:20',
            'cp_nama' => 'nullable|string|max:255',
            'cp_telp' => 'nullable|string|max:20',
            'cp_nama_2' => 'nullable|string|max:255',
            'cp_telp_2' => 'nullable|string|max:20',
        ]);

        return array_merge([
            'email_nasabah' => null,
            'alamat_nasabah' => null,
            'telp_nasabah' => null,
            'telp_nasabah_2' => null,
            'tipe_biaya' => null,
            'biaya_administrasi' => null,
            'batas_atas' => null,
            'instalasi' => null,
            'cp_nama' => null,
            'cp_telp' => null,
            'cp_nama_2' => null,
            'cp_telp_2' => null,
        ], $data);
    }
}
