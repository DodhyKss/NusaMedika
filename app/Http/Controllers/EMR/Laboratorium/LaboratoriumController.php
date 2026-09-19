<?php

namespace App\Http\Controllers\EMR\Laboratorium;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\PenunjangHelper;
use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\RegistrasiDetail;
use App\Models\Tindakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoriumController extends Controller
{
    /**
     * Form order Laboratorium di Dashboard Pasien. Data tersimpan di tabel
     * `order_laboratorium` + `order_laboratorium_detail` (bukan emr/emr_detail).
     * $emr_id pada URL /emr/form/laboratorium/{registrasi_detail_id}/{emr_id}
     * bermakna order_laboratorium_id.
     */
    public function index($registrasi_detail_id, $emr_id = null, $form_name = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug('laboratorium');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $bagianList = Bagian::aktif()
            ->where('referensi_bagian_id', 6)
            ->orderBy('nama_bagian')
            ->get();

        $tindakans = Tindakan::aktif()->with('bagian')->orderBy('nama_tindakan')->get();

        $orders = PenunjangHelper::riwayatOrderPasien('lab', (int) $registrasi_detail->registrasi_id);

        $edit = null;
        $editDetails = collect();
        if ($emr_id) {
            $edit = PenunjangHelper::findOrder('lab', (int) $emr_id);
            $editDetails = $edit->details;

            if ((int) $edit->status_order !== 0 && request('action') !== 'view') {
                return redirect()->route('emr.dynamic.index', [
                    'form_name' => 'laboratorium',
                    'registrasi_detail_id' => $registrasi_detail_id,
                    'emr_id' => $edit->order_laboratorium_id,
                    'action' => 'view',
                ]);
            }
        }

        $isView = request('action') === 'view' || ($edit && (int) $edit->status_order !== 0);

        return view('moduls.EMR.Laboratorium.index', compact(
            'registrasi_detail',
            'form_id',
            'aksesCrud',
            'bagianList',
            'tindakans',
            'orders',
            'edit',
            'editDetails',
            'isView'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug('laboratorium');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $registrasi_detail = RegistrasiDetail::with('registrasi')->findOrFail($registrasi_detail_id);
        $user = Auth::user();

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, $data['bagian_tujuan_id']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($items === []) {
            return back()->with('error', 'Minimal satu pemeriksaan wajib dipilih.')->withInput();
        }

        try {
            PenunjangHelper::buatOrder('lab', [
                'registrasi_detail_id' => (int) $registrasi_detail_id,
                'bagian_asal_id' => (int) $registrasi_detail->bagian_id,
                'bagian_tujuan_id' => $data['bagian_tujuan_id'],
                'dokter_id' => $user->pegawai_id ?? null,
                'prioritas' => $data['prioritas'],
                'keterangan' => $data['keterangan'],
            ], $items);

            return redirect()->route('emr.dynamic.index', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Laboratorium berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan order laboratorium: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('laboratorium');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        try {
            $data = $this->validatedHeader($request);
            $items = $this->validatedItems($request, $data['bagian_tujuan_id']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($items === []) {
            return back()->with('error', 'Minimal satu pemeriksaan wajib dipilih.')->withInput();
        }

        try {
            PenunjangHelper::ubahOrder('lab', (int) $emr_id, $data, $items);

            return redirect()->route('emr.dynamic.index', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Laboratorium berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui order laboratorium: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug('laboratorium');
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            PenunjangHelper::batalkan('lab', (int) $emr_id);

            return redirect()->route('emr.dynamic.index', ['form_name' => 'laboratorium', 'registrasi_detail_id' => $registrasi_detail_id])
                ->with('success', 'Order Laboratorium berhasil dibatalkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan order laboratorium: '.$e->getMessage());
        }
    }

    private function validatedHeader(Request $request): array
    {
        $data = $request->validate([
            'bagian_tujuan_id' => 'required|integer|exists:bagian,bagian_id',
            'prioritas' => 'nullable|in:BIASA,CITO,SEGERA',
            'keterangan' => 'nullable|string',
        ]);

        $bagian = Bagian::aktif()
            ->where('bagian_id', $data['bagian_tujuan_id'])
            ->where('referensi_bagian_id', 6)
            ->first();

        if (! $bagian) {
            throw new \RuntimeException('Bagian tujuan penunjang tidak valid.');
        }

        return array_merge([
            'prioritas' => null,
            'keterangan' => null,
        ], $data);
    }

    private function validatedItems(Request $request, int $bagianTujuanId): array
    {
        $tindakanIds = (array) $request->input('tindakan_id', []);

        $items = [];
        foreach ($tindakanIds as $i => $tindakanId) {
            if ($tindakanId === null || $tindakanId === '') {
                continue;
            }

            $tindakan = Tindakan::aktif()->whereKey((int) $tindakanId)->first();
            if (! $tindakan) {
                throw new \RuntimeException('Tindakan tidak valid pada baris #'.($i + 1).'.');
            }

            if ((int) $tindakan->bagian_id !== $bagianTujuanId) {
                throw new \RuntimeException('"'.$tindakan->nama_tindakan.'" tidak tersedia pada bagian tujuan terpilih.');
            }

            $items[] = [
                'tindakan_id' => (int) $tindakanId,
            ];
        }

        return $items;
    }
}
