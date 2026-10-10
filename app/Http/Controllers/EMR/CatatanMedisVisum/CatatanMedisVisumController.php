<?php

namespace App\Http\Controllers\EMR\CatatanMedisVisum;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Form Catatan Medis Visum (form 57, slug `catatan_medis_visum`).
 *
 * Visum et repertum adalah dokumen medico-legal bertanda tangan dokter atas
 * permintaan penegak hukum. Karena itu tiga field berikut diset di server:
 * tanggal dan waktu pemeriksaan (tanggal pemeriksaan, bukan tanggal pengetikan),
 * serta nomor visum yang terbit sekali dan tidak dapat diubah.
 *
 * Dokumen ini KHUSUS IGD (form igd = 1) dan hanya boleh dihapus dalam 7 hari
 * pertama sejak dibuat.
 */
class CatatanMedisVisumController extends Controller
{
    private const SLUG = 'catatan_medis_visum';

    /** Batas usia visum sebelum tidak boleh dihapus lagi (hari). */
    private const BATAS_HARI_HAPUS = 7;

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        // Tidak boleh membuat data baru -> tampilkan riwayat terakhir.
        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong($registrasi_detail));
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

        return view('moduls.EMR.CatatanMedisVisum.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id, $registrasi_detail, null), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Catatan Medis Visum berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Catatan Medis Visum: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);
        $registrasi_detail = RegistrasiDetail::findOrFail($registrasi_detail_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id, $registrasi_detail, (int) $emr_id));

            return redirect()->back()->with('success', 'Catatan Medis Visum berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Catatan Medis Visum: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        // Visum adalah dokumen bermeterai: nomor sudah tersebar ke aparat
        // penegak hukum, jadi penghapusan hanya sah pada 7 hari pertama.
        $emr = EmrHelper::emrById((int) $emr_id);
        abort_unless($emr, 404);
        abort_if(Carbon::parse($emr->input_time)->diffInDays(now()) > self::BATAS_HARI_HAPUS, 403, 'Visum yang sudah lebih dari 7 hari tidak dapat dihapus.');

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Catatan Medis Visum berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Catatan Medis Visum: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. Nomor visum ditampilkan sebagai pratinjau
     * bahwa nomor forthcoming, tetapi angka yang benar tetap bangkit di server.
     */
    private function kosong(RegistrasiDetail $registrasi_detail): array
    {
        return [
            'tanggal_visum' => now()->toDateString(),
            'waktu_visum' => now()->format('H:i'),
            'jenis_visum' => '',
            'nomor_visum' => $this->nomorBerikutnya($registrasi_detail),
            'dokter_visum' => Auth::user()?->pegawai_id ?? '',
            'permintaan_visum' => '',
            'benda_bukti_identifisir' => '',
            'hasil_visum' => '',
            'kesimpulan_visum' => '',
            'kelainan_sebab' => '',
            'kelainan_akibat' => '',
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'jenis_visum' => ['required', Rule::in(array_column(SelectOption::get('jenis_visum'), 'value'))],
            'dokter_visum' => ['required', 'integer', Rule::exists('pegawai', 'pegawai_id')->where('profesi_id', 1)],
            'permintaan_visum' => 'required|string|max:1000',
            'benda_bukti_identifisir' => 'required|string|max:3000',
            'hasil_visum' => 'required|string|max:5000',
            'kesimpulan_visum' => 'required|string|max:5000',
            'kelainan_sebab' => 'required|string|max:2000',
            'kelainan_akibat' => 'required|string|max:2000',
        ]);
    }

    /**
     * Tanggal/waktu pemeriksaan dan nomor visum SELALU berasal dari server —
     * nilai dari browser dibuang. Nomor yang sudah terbit dipertahankan saat
     * edit.
     */
    private function filteredData(array $data, int $formId, RegistrasiDetail $registrasi_detail, ?int $emrId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $data['tanggal_visum'] = now()->toDateString();
        $data['waktu_visum'] = now()->format('H:i');

        $data['nomor_visum'] = $emrId
            ? $this->nomorTetap((int) $emrId)
            : $this->nomorBerikutnya($registrasi_detail);

        return $data;
    }

    /**
     * Nomor visum yang sudah terbit pada sebuah EMR. Tidak pernah berubah.
     */
    private function nomorTetap(int $emrId): string
    {
        $lama = trim((string) (EmrHelper::emrDetailByVariabel($emrId)['nomor_visum'] ?? ''));

        return $lama !== '' ? $lama : $this->nomorBerikutnya(RegistrasiDetail::findOrFail(EmrHelper::emrById($emrId)->registrasi_detail_id));
    }

    /**
     * Bangkitkan nomor visum berikutnya untuk sebuah kunjungan.
     * Format: V/{registrasi_id}/{registrasi_detail_id}/{urut}/{tahun}.
     */
    private function nomorBerikutnya(RegistrasiDetail $registrasi_detail): string
    {
        $form_id = (int) EmrHelper::formIdBySlug(self::SLUG);

        $urut = DB::table('emr')
            ->where('form_id', $form_id)
            ->where('registrasi_detail_id', (int) $registrasi_detail->registrasi_detail_id)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->count() + 1;

        return 'V/'.$registrasi_detail->registrasi_id
            .'/'.$registrasi_detail->registrasi_detail_id
            .'/'.str_pad((string) $urut, 3, '0', STR_PAD_LEFT)
            .'/'.now()->format('Y');
    }
}
