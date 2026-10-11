<?php

namespace App\Http\Controllers\EMR\OrderMikrobiologi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\PenunjangHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\RegistrasiDetail;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Order Mikrobiologi (form 50, slug `order_mikrobiologi`).
 *
 * POLA SAMA dengan `LaboratoriumController` (form 6): data order disimpan di
 * `order_mikrobiologi` + `order_mikrobiologi_detail` (BUKAN `emr_detail`).
 * `emr_id` pada URL bermakna `order_mikrobiologi_id`.
 *
 * Kolom khas mikrobiologi (disimpan di header tabel, bukan `emr_detail`):
 * `unit_pemeriksaan`, `jenis_pemeriksaan` (Dalam/Luar), `spesimen`, dan
 * `jumlah_spesimen`. When mikrobiologi dilakukan di luar, `catatan` WAJIB
 * memuat nama laboratorium tujuan rujukan.
 */
class OrderMikrobiologiController extends Controller
{
    private const SLUG = 'order_mikrobiologi';

    private const JENIS = 'mikrobiologi';

    /** Opsi sumber spesimen. */
    public const OPSI_SPESIMEN = [
        'Darah', 'Urine', 'Sputum', 'Swab Luka', 'Liqrida', 'Tenggorok',
        'Rektal', 'Vagina', 'Uretra', 'Lumbal Cair', 'Jari', 'Kulit', 'Jamur',
    ];

    public function index($registrasi_detail_id, $emr_id = null, $form_name = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $bagianList = Bagian::aktif()->where('referensi_bagian_id', 6)->orderBy('nama_bagian')->get();
        $tindakans = Tindakan::aktif()->orderBy('nama_tindakan')->get();
        $tindakanBagianMap = PenunjangHelper::tindakanBagianMap();

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
                    'emr_id' => $edit->order_mikrobiologi_id,
                    'action' => 'view',
                ]);
            }
        }

        $isView = request('action') === 'view' || ($edit && (int) $edit->status_order !== 0);

        return view('moduls.EMR.OrderMikrobiologi.index', compact(
            'registrasi_detail', 'aksesCrud', 'bagianList', 'tindakans',
            'tindakanBagianMap', 'orders', 'edit', 'editDetails', 'isView',
            'emr_id', 'registrasi_detail_id'
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
            $items = $this->validatedItems($request, (int) $data['unit_pemeriksaan']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($items === []) {
            return back()->with('error', 'Minimal satu pemeriksaan wajib dipilih.')->withInput();
        }

        try {
            $orderId = PenunjangHelper::buatOrder(self::JENIS, [
                'registrasi_detail_id' => (int) $registrasi_detail_id,
                'bagian_asal_id' => (int) $registrasi_detail->bagian_id,
                'bagian_tujuan_id' => (int) $data['unit_pemeriksaan'],
                'dokter_id' => $user->pegawai_id ?? null,
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            $this->simpanKolomKhusus($orderId, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Mikrobiologi berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan order mikrobiologi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, (int) $data['unit_pemeriksaan']);
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
                'bagian_tujuan_id' => (int) $data['unit_pemeriksaan'],
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            $this->simpanKolomKhusus((int) $emr_id, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Mikrobiologi berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui order mikrobiologi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            PenunjangHelper::batalkan(self::JENIS, (int) $emr_id);

            return redirect()->back()->with('success', 'Order Mikrobiologi berhasil dibatalkan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order mikrobiologi: '.$e->getMessage());
        }
    }

    private function simpanKolomKhusus(int $orderId, array $data): void
    {
        DB::table('order_mikrobiologi')->where('order_mikrobiologi_id', $orderId)->update([
            'unit_pemeriksaan' => (int) $data['unit_pemeriksaan'],
            'jenis_pemeriksaan' => $data['jenis_pemeriksaan'],
            'spesimen' => $data['spesimen'],
            'jumlah_spesimen' => (int) $data['jumlah_spesimen'],
            'mod_time' => now(),
        ]);
    }

    private function validatedHeader(Request $request): array
    {
        $data = $request->validate([
            'tanggal_pemeriksaan' => 'required|date',
            'diagnosa' => 'required|string|max:2000',
            'unit_pemeriksaan' => 'required|integer|exists:bagian,bagian_id',
            'jenis_pemeriksaan' => 'required|in:Dalam,Luar',
            'spesimen' => 'required|string|max:50',
            'jumlah_spesimen' => 'required|integer|between:1,10',
            'prioritas' => 'required|in:BIASA,CITO',
            'catatan' => 'nullable|string|max:1000',
        ]);

        // Pemeriksaan di luar wajib menyebut laboratorium tujuan rujukan,
        // minimal 5 karakter — tanpa itu klaim tidak bisa ditelusuri.
        if ($data['jenis_pemeriksaan'] === 'Luar' && mb_strlen(trim((string) ($data['catatan'] ?? ''))) < 5) {
            throw new \RuntimeException('Untuk pemeriksaan Luar, catatan harus memuat nama laboratorium tujuan rujukan.');
        }

        return array_merge(['catatan' => null], $data);
    }

    private function validatedItems(Request $request, int $bagianTujuanId): array
    {
        $tindakanIds = (array) $request->input('tindakan_id', []);
        $bagianMap = PenunjangHelper::tindakanBagianMap();

        $items = [];
        foreach ($tindakanIds as $i => $tindakanId) {
            if ($tindakanId === null || $tindakanId === '') {
                continue;
            }

            $tindakan = Tindakan::aktif()->whereKey((int) $tindakanId)->first();
            if (! $tindakan) {
                throw new \RuntimeException('Tindakan tidak valid pada baris #'.($i + 1).'.');
            }

            if (($bagianMap[(int) $tindakanId] ?? null) !== $bagianTujuanId) {
                throw new \RuntimeException(
                    '"'.$tindakan->nama_tindakan.'" tidak termasuk dalam Group Tindakan unit pemeriksa terpilih.'
                );
            }

            $items[] = ['tindakan_id' => (int) $tindakanId];
        }

        return $items;
    }
}
