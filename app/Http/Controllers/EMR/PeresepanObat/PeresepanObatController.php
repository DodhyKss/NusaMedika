<?php

namespace App\Http\Controllers\EMR\PeresepanObat;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\PeresepanObat;
use App\Models\PeresepanObatDetail;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeresepanObatController extends Controller
{
    /**
     * Form penulisan resep (EMR) untuk seorang pasien. Data tersimpan di
     * tabel `peresepan_obat` + `peresepan_obat_detail` (bukan emr/emr_detail).
     * $emr_id pada URL /emr/form/peresepan_obat/{registrasi_detail_id}/{emr_id}
     * di sini bermakna peresepan_obat_id.
     */
    public function index($registrasi_detail_id, $emr_id = null, $form_name = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug('peresepan_obat');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $barangs = Barang::aktif()->with('satuan')->orderBy('nama_barang')->get();

        $peresepans = PeresepanObat::aktif()
            ->with('details.barang', 'dokter')
            ->where('registrasi_detail_id', $registrasi_detail_id)
            ->orderByDesc('peresepan_obat_id')
            ->paginate(10)
            ->withQueryString();

        $edit = null;
        $editDetails = collect();
        if ($emr_id) {
            $edit = PeresepanObat::aktif()
                ->with('details.barang', 'dokter')
                ->find((int) $emr_id);
            if ($edit) {
                $editDetails = $edit->details;
            }
        }

        $isView = request('action') === 'view';

        return view('moduls.EMR.PeresepanObat.index', compact(
            'registrasi_detail',
            'barangs',
            'peresepans',
            'edit',
            'editDetails',
            'form_id',
            'aksesCrud',
            'isView'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug('peresepan_obat');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi')->findOrFail($registrasi_detail_id);

        $user = Auth::user();
        if (! $user || $user->pegawai_id == null || $user->user_id == null) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            $items = $this->validatedItems($request);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if (empty($items)) {
            return back()->with('error', 'Minimal satu item obat wajib diisi.')->withInput();
        }

        DB::beginTransaction();
        try {
            $peresepan = new PeresepanObat;
            $peresepan->no_resep = $this->generateNoResep();
            $peresepan->dokter_id = $user->pegawai_id;
            $peresepan->pasien_id = $registrasi_detail->registrasi->pasien_id;
            $peresepan->registrasi_detail_id = $registrasi_detail_id;
            $peresepan->status_resep = 0;
            $peresepan->tanggal_resep = now();
            $peresepan->keterangan = $request->input('keterangan');
            $peresepan->input_time = now();
            $peresepan->input_user_id = $user->user_id;
            $peresepan->status_batal = 0;
            $peresepan->save();

            $this->storeDetails($peresepan->peresepan_obat_id, $items);

            DB::commit();

            return redirect()->route('emr.dynamic.index', ['form_name' => 'peresepan_obat', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Resep berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan resep: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('peresepan_obat');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $peresepan = PeresepanObat::aktif()->find((int) $emr_id);
        if (! $peresepan) {
            abort(404);
        }

        // Resep yang sudah didispense atau selesai tidak boleh diubah lagi
        if ((int) $peresepan->status_resep !== 0) {
            return back()->with('error', 'Resep yang sudah diproses tidak dapat diubah.');
        }

        try {
            $items = $this->validatedItems($request);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if (empty($items)) {
            return back()->with('error', 'Minimal satu item obat wajib diisi.')->withInput();
        }

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $peresepan->keterangan = $request->input('keterangan');
            $peresepan->mod_time = now();
            $peresepan->mod_user_id = $user->user_id ?? null;
            $peresepan->save();

            // Soft-delete detail lama lalu simpan ulang
            PeresepanObatDetail::where('peresepan_obat_id', $peresepan->peresepan_obat_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            $this->storeDetails($peresepan->peresepan_obat_id, $items);

            DB::commit();

            return redirect()->route('emr.dynamic.index', ['form_name' => 'peresepan_obat', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Resep berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui resep: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('peresepan_obat');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        $peresepan = PeresepanObat::aktif()->find((int) $emr_id);
        if (! $peresepan) {
            abort(404);
        }

        if ((int) $peresepan->status_resep !== 0) {
            return back()->with('error', 'Resep yang sudah diproses tidak dapat dibatalkan.');
        }

        $user = Auth::user();

        DB::beginTransaction();
        try {
            PeresepanObat::where('peresepan_obat_id', $peresepan->peresepan_obat_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            PeresepanObatDetail::where('peresepan_obat_id', $peresepan->peresepan_obat_id)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => now(),
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            DB::commit();

            return redirect()->route('emr.dynamic.index', ['form_name' => 'peresepan_obat', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Resep berhasil dibatalkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membatalkan resep: '.$e->getMessage());
        }
    }

    private function generateNoResep(): string
    {
        $prefix = 'RES-'.now()->format('Ymd');
        $suffixes = DB::table('peresepan_obat')
            ->where('no_resep', 'like', $prefix.'-%')
            ->pluck('no_resep')
            ->map(fn ($no) => (int) last(explode('-', $no)))
            ->toArray();

        $last = empty($suffixes) ? 0 : max($suffixes);

        return $prefix.'-'.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
    }

    private function storeDetails(int $peresepanObatId, array $items): void
    {
        $user = Auth::user();
        $now = now();

        foreach ($items as $item) {
            DB::table('peresepan_obat_detail')->insert([
                'peresepan_obat_id' => $peresepanObatId,
                'barang_id' => $item['barang_id'],
                'jumlah' => $item['jumlah'],
                's_1' => $item['s_1'],
                's_2' => $item['s_2'],
                'aturan_pakai' => $item['aturan_pakai'],
                'rute_pemberian' => $item['rute_pemberian'],
                'flag_dispense' => 0,
                'jumlah_dispense' => 0,
                'input_time' => $now,
                'input_user_id' => $user->user_id ?? null,
                'status_batal' => 0,
            ]);
        }
    }

    private function validatedItems(Request $request): array
    {
        $barangIds = (array) $request->input('barang_id', []);
        $jumlahs = (array) $request->input('jumlah', []);
        $s1s = (array) $request->input('s_1', []);
        $s2s = (array) $request->input('s_2', []);
        $aturanPakais = (array) $request->input('aturan_pakai', []);
        $rutes = (array) $request->input('rute_pemberian', []);

        $items = [];
        foreach ($barangIds as $i => $barangId) {
            if ($barangId === null || $barangId === '') {
                continue;
            }

            $jumlah = (float) ($jumlahs[$i] ?? 0);
            if ($jumlah <= 0) {
                throw new \RuntimeException('Jumlah wajib lebih dari 0 untuk baris #'.($i + 1).'.');
            }

            if (! Barang::aktif()->whereKey((int) $barangId)->exists()) {
                throw new \RuntimeException('Barang tidak valid pada baris #'.($i + 1).'.');
            }

            $items[] = [
                'barang_id' => (int) $barangId,
                'jumlah' => $jumlah,
                's_1' => trim((string) ($s1s[$i] ?? '')) ?: null,
                's_2' => trim((string) ($s2s[$i] ?? '')) ?: null,
                'aturan_pakai' => trim((string) ($aturanPakais[$i] ?? '')) ?: null,
                'rute_pemberian' => trim((string) ($rutes[$i] ?? '')) ?: null,
            ];
        }

        return $items;
    }
}
