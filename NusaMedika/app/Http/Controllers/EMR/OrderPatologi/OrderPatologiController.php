<?php

namespace App\Http\Controllers\EMR\OrderPatologi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\PenunjangHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\GroupTindakan;
use App\Models\RegistrasiDetail;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Order Patologi Anatomi (form 49, slug `order_patologi`).
 *
 * PATERN SAMA dengan `LaboratoriumController` (form 6): data order disimpan di
 * tabel `order_patologi` + `order_patologi_detail` (BUKAN `emr_detail`).
 * `emr_id` pada URL `/emr/form/order_patologi/{registrasi_detail_id}/{emr_id}`
 * bermakna `order_patologi_id`. Seluruh operasi melewati
 * `App\Helpers\PenunjangHelper` yang generik terhadap jenis order.
 *
 * `jenis_pesanan` = `group_tindakan_id` (unit peminta), yang juga menentukan
 * item tindakan yang boleh dipilih lewat `PenunjangHelper::tindakanBagianMap()`.
 */
class OrderPatologiController extends Controller
{
    private const SLUG = 'order_patologi';

    /** Prefix penomoran order (PAT). */
    private const JENIS = 'patologi';

    /** Opsi spesimen per baris. */
    public const OPSI_SPESIMEN = ['Biopsi', 'Sitologi', 'Kspecimen', 'Blocks'];

    public function index($registrasi_detail_id, $emr_id = null, $form_name = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        // Unit Patologi Anatomi = bagian penunjang (referensi_bagian_id 6).
        $bagianList = Bagian::aktif()->where('referensi_bagian_id', 6)->orderBy('nama_bagian')->get();

        // Group tindakan = "jenis pesanan" (unit peminta).
        $groups = GroupTindakan::aktif()->orderBy('nama_group_tindakan')->get();

        $tindakans = Tindakan::aktif()->orderBy('nama_tindakan')->get();
        $tindakanBagianMap = PenunjangHelper::tindakanBagianMap();

        // Peta group_tindakan_id => [tindakan_id, ...] untuk cascade "jenis
        // pesanan" -> item di view, plus tarif per tindakan untuk kolom harga.
        $grupTindakan = [];
        foreach ($groups as $g) {
            $grupTindakan[$g->group_tindakan_id] = [
                'nama' => $g->nama_group_tindakan,
                'tindakan_ids' => DB::table('group_tindakan_tindakan')
                    ->where('group_tindakan_id', $g->group_tindakan_id)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->pluck('tindakan_id')
                    ->map(fn ($x) => (int) $x)
                    ->all(),
            ];
        }
        $hakKelasId = PenunjangHelper::kelasRuangId(
            $registrasi_detail->hak_kelas_id ? (int) $registrasi_detail->hak_kelas_id : null
        );
        $tarifTindakan = [];
        foreach ($tindakans as $tk) {
            $tarifTindakan[$tk->tindakan_id] = (float) (PenunjangHelper::tarif((int) $tk->tindakan_id, $hakKelasId)['tarif'] ?? 0);
        }

        $orders = PenunjangHelper::riwayatOrderPasien(self::JENIS, (int) $registrasi_detail->registrasi_id);

        $edit = null;
        $editDetails = collect();
        if ($emr_id) {
            $edit = PenunjangHelper::findOrder(self::JENIS, (int) $emr_id);
            $editDetails = $edit->details;

            if ((int) $edit->status_order !== 0 && request('action') !== 'view') {
                return redirect()->route('emr.dynamic.index', [
                    'form_name' => self::SLUG,
                    'registrasi_detail_id' => $registrasi_detail_id,
                    'emr_id' => $edit->order_patologi_id,
                    'action' => 'view',
                ]);
            }
        }

        $isView = request('action') === 'view' || ($edit && (int) $edit->status_order !== 0);

        return view('moduls.EMR.OrderPatologi.index', compact(
            'registrasi_detail', 'aksesCrud', 'bagianList', 'groups', 'tindakans',
            'tindakanBagianMap', 'orders', 'edit', 'editDetails', 'isView', 'emr_id',
            'registrasi_detail_id', 'grupTindakan', 'tarifTindakan'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi')->findOrFail($registrasi_detail_id);
        $user = Auth::user();

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, (int) $data['jenis_pesanan']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($items === []) {
            return back()->with('error', 'Minimal satu pemeriksaan wajib dipilih.')->withInput();
        }

        try {
            PenunjangHelper::buatOrder(self::JENIS, [
                'registrasi_detail_id' => (int) $registrasi_detail_id,
                'bagian_asal_id' => (int) $registrasi_detail->bagian_id,
                'bagian_tujuan_id' => (int) $data['jenis_pesanan'],
                'dokter_id' => $user->pegawai_id ?? null,
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            // Kolom khusus form ini disimpan terpisah dari header generik.
            $this->simpanKolomKhusus('store', $registrasi_detail_id, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Patologi berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan order patologi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, (int) $data['jenis_pesanan']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($items === []) {
            return back()->with('error', 'Minimal satu pemeriksaan wajib dipilih.')->withInput();
        }

        try {
            PenunjangHelper::ubahOrder(self::JENIS, (int) $emr_id, [
                'registrasi_detail_id' => (int) $registrasi_detail_id,
                'bagian_asal_id' => (int) RegistrasiDetail::findOrFail($registrasi_detail_id)->bagian_id,
                'bagian_tujuan_id' => (int) $data['jenis_pesanan'],
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            $this->simpanKolomKhusus('update', (int) $emr_id, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Patologi berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui order patologi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            PenunjangHelper::batalkan(self::JENIS, (int) $emr_id);

            return redirect()->back()->with('success', 'Order Patologi berhasil dibatalkan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order patologi: '.$e->getMessage());
        }
    }

    /**
     * Simpan kolom khas order patologi (`jenis_pesanan`) yang tidak ada di
     * payload generik `PenunjangHelper::buatOrder()`.
     *
     * `bagian_tujuan_id` sudah diisi `jenis_pesanan` saat buatOrder, sehingga
     * di sini cukup memastikan keduanya sinkron.
     */
    private function simpanKolomKhusus(string $mode, int $id, array $data): void
    {
        $orderId = $mode === 'store'
            ? DB::table('order_patologi')
                ->where('registrasi_detail_id', $id)
                ->where('bagian_tujuan_id', (int) $data['jenis_pesanan'])
                ->orderByDesc('order_patologi_id')->value('order_patologi_id')
            : $id;

        if ($orderId) {
            DB::table('order_patologi')
                ->where('order_patologi_id', $orderId)
                ->update([
                    'jenis_pesanan' => (int) $data['jenis_pesanan'],
                    'mod_time' => now(),
                ]);
        }
    }

    private function validatedHeader(Request $request): array
    {
        $data = $request->validate([
            'tanggal_pemeriksaan' => 'required|date',
            'diagnosa_patologi' => 'required|string|max:2000',
            'jenis_pesanan' => 'required|integer|exists:group_tindakan,group_tindakan_id',
            'prioritas' => 'required|in:BIASA,CITO',
            'catatan' => 'nullable|string|max:1000',
        ]);

        return array_merge(['catatan' => null], $data);
    }

    private function validatedItems(Request $request, int $groupTindakanId): array
    {
        $tindakanIds = (array) $request->input('tindakan_id', []);
        $spesimenInputs = (array) $request->input('spesimen', []);

        $bagianMap = PenunjangHelper::tindakanBagianMap();

        // Tindakan yang diizinkan = anggota group terpilih.
        $anggota = DB::table('group_tindakan_tindakan')
            ->where('group_tindakan_id', $groupTindakanId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('tindakan_id')
            ->map(fn ($x) => (int) $x)
            ->all();

        $items = [];
        foreach ($tindakanIds as $i => $tindakanId) {
            if ($tindakanId === null || $tindakanId === '') {
                continue;
            }

            $tindakan = Tindakan::aktif()->whereKey((int) $tindakanId)->first();
            if (! $tindakan) {
                throw new \RuntimeException('Tindakan tidak valid pada baris #'.($i + 1).'.');
            }

            if (! in_array((int) $tindakanId, $anggota, true)) {
                throw new \RuntimeException(
                    '"'.$tindakan->nama_tindakan.'" tidak termasuk dalam Group Tindakan jenis pesanan terpilih.'
                );
            }

            $baris = (string) $i;
            $spesimen = trim((string) ($spesimenInputs[$baris] ?? ''));
            if ($spesimen !== '' && ! in_array($spesimen, self::OPSI_SPESIMEN, true)) {
                throw new \RuntimeException('Spesimen pada baris #'.($i + 1).' tidak valid.');
            }

            $items[] = [
                'tindakan_id' => (int) $tindakanId,
                'spesimen' => $spesimen !== '' ? $spesimen : null,
            ];
        }

        return $items;
    }
}
