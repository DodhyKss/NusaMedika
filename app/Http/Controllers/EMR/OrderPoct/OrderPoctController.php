<?php

namespace App\Http\Controllers\EMR\OrderPoct;

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
 * Form Order POCT (form 51, slug `order_poct`).
 *
 * POLA SAMA dengan `LaboratoriumController` (form 6): data order disimpan di
 * `order_poct` + `order_poct_detail` (BUKAN `emr_detail`). `emr_id` pada URL
 * bermakna `order_poct_id`.
 *
 * Kolom khas POCT (disimpan di header tabel):
 * `indikasi` (alasan pemeriksaan) dan ringkasan hasil terakhir
 * (`tanggal_hasil_terakhir`, `waktu_hasil_terakhir`, `hasil_terakhir`).
 *
 * Catatan hasil lengkap per parameter tetap ditulis di `order_poct_detail`;
 * tiga kolom di atas hanya RINGKASAN hasil terakhir untuk dashboard.
 */
class OrderPoctController extends Controller
{
    private const SLUG = 'order_poct';

    private const JENIS = 'poct';

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
                    'emr_id' => $edit->order_poct_id,
                    'action' => 'view',
                ]);
            }
        }

        $isView = request('action') === 'view' || ($edit && (int) $edit->status_order !== 0);

        return view('moduls.EMR.OrderPoct.index', compact(
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
            $items = $this->validatedItems($request, (int) $data['bagian_tujuan_id']);
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
                'bagian_tujuan_id' => (int) $data['bagian_tujuan_id'],
                'dokter_id' => $user->pegawai_id ?? null,
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            $this->simpanKolomKhusus($orderId, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order POCT berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan order POCT: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, (int) $data['bagian_tujuan_id']);
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
                'bagian_tujuan_id' => (int) $data['bagian_tujuan_id'],
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['catatan'],
            ], $items);

            $this->simpanKolomKhusus((int) $emr_id, $data);

            return redirect()->route('emr.dynamic.index', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order POCT berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui order POCT: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            PenunjangHelper::batalkan(self::JENIS, (int) $emr_id);

            return redirect()->back()->with('success', 'Order POCT berhasil dibatalkan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order POCT: '.$e->getMessage());
        }
    }

    private function simpanKolomKhusus(int $orderId, array $data): void
    {
        DB::table('order_poct')->where('order_poct_id', $orderId)->update([
            'indikasi' => $data['indikasi'] ?? null,
            'mod_time' => now(),
        ]);
    }

    private function validatedHeader(Request $request): array
    {
        return array_merge(['catatan' => null, 'indikasi' => null], $request->validate([
            'tanggal_order' => 'required|date',
            'diagnosa_poct' => 'required|string|max:2000',
            'indikasi' => 'nullable|string|max:255',
            'bagian_tujuan_id' => 'required|integer|exists:bagian,bagian_id',
            'prioritas' => 'required|in:BIASA,CITO',
            'catatan' => 'nullable|string|max:1000',
        ]));
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
                    '"'.$tindakan->nama_tindakan.'" tidak termasuk dalam Group Tindakan unit tujuan terpilih.'
                );
            }

            $items[] = ['tindakan_id' => (int) $tindakanId];
        }

        return $items;
    }
}
