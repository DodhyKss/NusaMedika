<?php

namespace App\Http\Controllers\EMR\ImplementasiKeperawatan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\Implementasi;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Implementasi Keperawatan (slug `implementasi_keperawatan`).
 *
 * Satu baris per pengisian form: Implementasi (dari Master Implementasi),
 * tanggal, jam, keterangan, dan respon pasien (free text).
 * `nama_implementasi` diisi ulang dari master saat menyimpan sebagai snapshot,
 * agar riwayat tetap terbaca bila master berubah.
 */
class ImplementasiKeperawatanController extends Controller
{
    private const SLUG = 'implementasi_keperawatan';

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);

        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        // Master Implementasi untuk dropdown.
        $implementasiList = Implementasi::aktif()->orderBy('kode_implementasi')->get();

        // Riwayat KUNJUNGAN untuk dropdown "History" di panel kiri.
        // Riwayat emr sendiri sudah diambil komponen x-emr-history-table.
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData([
                'implementasi_id' => '',
                'nama_implementasi' => '',
                'tanggal_implementasi' => '',
                'waktu_implementasi' => '',
                'keterangan_implementasi' => '',
                'respon_implementasi' => '',
            ]);

            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);

            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        return view('moduls.EMR.ImplementasiKeperawatan.index', compact(
            'registrasi_detail',
            'riwayat',
            'implementasiList',
            'emr_data',
            'historyGrouped',
            'formAction',
            'isEdit',
            'deleteAction',
            'isView',
            'emr_id',
            'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);

        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Implementasi Keperawatan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan implementasi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Implementasi Keperawatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui implementasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Implementasi Keperawatan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus implementasi: '.$e->getMessage());
        }
    }

    /**
     * Hanya field yang terpetakan yang disimpan. `nama_implementasi` diisi dari
     * master berdasarkan `implementasi_id` (bukan dari input browser), supaya
     * snapshotnya konsisten dengan master.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $implementasi = Implementasi::aktif()
            ->where('implementasi_id', (int) ($data['implementasi_id'] ?? 0))
            ->first();

        $data['nama_implementasi'] = $implementasi ? $implementasi->nama_implementasi : '';

        return $data;
    }

    /**
     * Validasi input form. Tanggal dan jam WAJIB diisi manual oleh perawat
     * (tidak ada nilai default) sesuai keputusan.
     */
    private function validated(Request $request): array
    {
        return array_merge([
            'keterangan_implementasi' => null,
            'respon_implementasi' => null,
        ], $request->validate([
            'implementasi_id' => 'required|integer|exists:implementasi,implementasi_id',
            'tanggal_implementasi' => 'required|date',
            'waktu_implementasi' => 'required|date_format:H:i',
            'keterangan_implementasi' => 'nullable|string|max:1000',
            'respon_implementasi' => 'nullable|string|max:1000',
        ]));
    }
}
