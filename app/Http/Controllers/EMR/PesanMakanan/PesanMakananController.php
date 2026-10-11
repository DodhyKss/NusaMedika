<?php

namespace App\Http\Controllers\EMR\PesanMakanan;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Form Pesan Makanan & Monitoring Asupan (form 42, slug `pesan_makanan`).
 *
 * Diisi dapur / petugas Wound: mencatat jadwal distribusi makan pagi, siang,
 * sore beserta petugas yang mendistribusikannya, plus jenis pesanan yang diminta.
 *
 * Validasi keunikan (docs/KONSEP_ASESMEN_GIZI.md §6.6): satu tanggal + satu
 * jenis pesanan hanya boleh satu baris per `registrasi_detail_id`. Dicek di
 * controller — `Rule::unique` tidak bisa dipakai karena `emr` bukan tabel
 * tunggal (nilai `jenis_pesanan` ada di `emr_detail`, tanggal di `emr`).
 */
class PesanMakananController extends Controller
{
    private const SLUG = 'pesan_makanan';

    /** Opsi jenis pesanan (dropdown SelectOption). */
    public const OPSI_JENIS = [
        'Jam Antar Dari Dapur Besar',
        'Jam Awal Distribusi Makan Ke Pasien',
        'Jam Selesai Makan Distribusi Ke Pasien',
    ];

    public function index($registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'read'), 403);

        $aksesCrud = AksesEhr::flags((int) $form_id);
        $riwayat = EmrHelper::emrList((int) $form_id, (int) $registrasi_detail->registrasi_id);
        $historyGrouped = EmrHelper::historyKunjunganGrouped($registrasi_detail);

        if (empty($emr_id) && ! ($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
            return redirect()->route('emr.dynamic.index', [
                'form_name' => self::SLUG,
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $riwayat->first()->emr_id,
                'action' => 'view',
            ]);
        }

        if (empty($emr_id)) {
            $emr_data = EmrHelper::wrapData($this->kosong());
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

        return view('moduls.EMR.PesanMakanan.index', compact(
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

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        $duplikat = $this->cekDuplikat($data, (int) $registrasi_detail_id);
        if ($duplikat) {
            return back()->with('error', 'Pesan makanan dengan tanggal & jenis yang sama sudah tercatat.')->withInput();
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Pesan Makanan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Pesan Makanan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        $duplikat = $this->cekDuplikat($data, (int) $registrasi_detail_id, (int) $emr_id);
        if ($duplikat) {
            return back()->with('error', 'Pesan makanan dengan tanggal & jenis yang sama sudah tercatat.')->withInput();
        }

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Pesan Makanan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Pesan Makanan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Pesan Makanan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Pesan Makanan: '.$e->getMessage());
        }
    }

    /**
     * Cek apakah tanggal + jenis pesanan sudah dipakai pada kunjungan yang sama.
     *
     * `tanggal_pesan` disimpan sebagai `emr.input_time` (bukan emr_detail),
     * jadi tanggal yang dibandingkan diambil dari `DATE(input_time)`.
     * `$ignoreEmrId` dipakai saat edit agar baris yang sedang diedit tidak
     * dianggap duplikat terhadap dirinya sendiri.
     */
    private function cekDuplikat(array $data, int $registrasiDetailId, ?int $ignoreEmrId = null): bool
    {
        $tanggal = substr((string) ($data['tanggal_pesan'] ?? ''), 0, 10);
        $jenis = (string) ($data['jenis_pesanan'] ?? '');

        if ($tanggal === '' || $jenis === '') {
            return false;
        }

        $formId = EmrHelper::formIdBySlug(self::SLUG);

        $ada = DB::table('emr')
            ->join('emr_detail', 'emr_detail.emr_id', '=', 'emr.emr_id')
            ->where('emr.form_id', $formId)
            ->where('emr.registrasi_id', $registrasiDetailId)
            ->where('emr_detail.variabel', 'jenis_pesanan')
            ->where('emr_detail.value', $jenis)
            ->where(function ($q) {
                $q->whereNull('emr.status_batal')->orWhere('emr.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('emr_detail.status_batal')->orWhere('emr_detail.status_batal', 0);
            })
            ->whereRaw('DATE(emr.input_time) = ?', [$tanggal]);

        if ($ignoreEmrId) {
            $ada->where('emr.emr_id', '!=', $ignoreEmrId);
        }

        return $ada->exists();
    }

    private function kosong(): array
    {
        return [
            'tanggal_pesan' => '', 'jenis_pesanan' => '',
            'waktu_pagi' => '', 'waktu_siang' => '', 'waktu_sore' => '',
            'petugas_pagi' => '', 'petugas_siang' => '', 'petugas_sore' => '',
            'prioritas' => '', 'keterangan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_pesan' => 'required|date',
            // Ditulis literal (bukan implode) supaya optsinya jelas dibaca
            // dan terbaca harness E2E yang men-parsing source controller.
            'jenis_pesanan' => 'required|in:Jam Antar Dari Dapur Besar,Jam Awal Distribusi Makan Ke Pasien,Jam Selesai Makan Distribusi Ke Pasien',
            'waktu_pagi' => 'required|date_format:H:i',
            'waktu_siang' => 'required|date_format:H:i',
            'waktu_sore' => 'required|date_format:H:i',
            'petugas_pagi' => 'required|string|max:255',
            'petugas_siang' => 'required|string|max:255',
            'petugas_sore' => 'required|string|max:255',
            'prioritas' => 'nullable|in:BIASA,CITO',
            'keterangan' => 'nullable|string|max:1000',
        ];

        return array_merge(
            ['prioritas' => null, 'keterangan' => null],
            $request->validate($rules)
        );
    }

    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
