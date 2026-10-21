<?php

namespace App\Http\Controllers\EMR\Handover;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Form Handover / Serah Terima (form 29, slug `handover`).
 *
 * Berita serah terima antar shift atau antar petugas. `isi_serah_terima`
 * di-prefill dari SBAR terakhir (form 12) pada kunjungan yang sama - HANYA teks
 * prefill, tetap bisa diedit petugas sebelum disimpan.
 *
 * Nama petugas (`nama_petugas_serah` / `nama_petugas_terima`) SELALU ditulis
 * ulang dari master `pegawai` di server; nilai browser dibuang.
 *
 * `ruang_tujuan` hanya berlaku bila jenis serah terima = B (Transfer Ruangan);
 * bila tidak, field itu dibuang di `filteredData()`.
 */
class HandoverController extends Controller
{
    private const SLUG = 'handover';

    /** Slug form 12 - sumber prefill isi serah terima. */
    private const SLUG_SBAR = 'sbar';

    /** `referensi_bagian` - ruang perawatan tujuan transfer. */
    private const REFERENSI_BAGIAN_RUANG = 2;

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

        $pegawais = DB::table('pegawai')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_pegawai')
            ->get(['pegawai_id', 'nama_pegawai']);

        $ruangTujuan = DB::table('bagian')
            ->where('referensi_bagian_id', self::REFERENSI_BAGIAN_RUANG)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('nama_bagian')
            ->get(['bagian_id', 'nama_bagian']);

        if (empty($emr_id)) {
            $kosong = $this->kosong();
            $kosong['isi_serah_terima'] = $this->prefillSbar((int) $registrasi_detail_id);

            $emr_data = EmrHelper::wrapData($kosong);
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

        return view('moduls.EMR.Handover.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud',
            'pegawais', 'ruangTujuan'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $data = $this->validated($request);
        $this->validasiPetugas($request, $data);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Handover berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Handover: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);
        $this->validasiPetugas($request, $data);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data, (int) $form_id));

            return redirect()->back()->with('success', 'Handover berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Handover: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Handover berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Handover: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru. Tanggal & jam sengaja TIDAK diberi default:
     * berita serah terima harus dicatat waktu BNIH sebenarnya.
     */
    private function kosong(): array
    {
        return [
            'tanggal_serah_terima' => '', 'waktu_serah_terima' => '',
            'shift' => '', 'jenis_serah_terima' => '',
            'petugas_serah_id' => '', 'nama_petugas_serah' => '',
            'petugas_terima_id' => '', 'nama_petugas_terima' => '',
            'ruang_tujuan' => '', 'waktu_konfirmasi_terima' => '',
            'jumlah_pasien_serah_terima' => '', 'status_kelengkapan_serah_terima' => '',
            'isi_serah_terima' => '', 'catatan_serah_terima' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'nama_petugas_serah' => null,
            'nama_petugas_terima' => null,
            'ruang_tujuan' => null,
            'catatan_serah_terima' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_serah_terima' => 'required|date',
            'waktu_serah_terima' => 'required|date_format:H:i',
            'shift' => ['required', Rule::in(array_column(SelectOption::get('shift'), 'value'))],
            'jenis_serah_terima' => ['required', Rule::in(array_column(SelectOption::get('jenis_serah_terima'), 'value'))],

            'petugas_serah_id' => ['required', 'integer', 'exists:pegawai,pegawai_id'],
            'petugas_terima_id' => ['required', 'integer', 'exists:pegawai,pegawai_id'],

            // Hanya wajib bila jenis serah terima = B (Transfer Ruangan).
            'ruang_tujuan' => 'nullable|required_if:jenis_serah_terima,B|integer|exists:bagian,bagian_id',

            'waktu_konfirmasi_terima' => 'required|date_format:H:i',
            'jumlah_pasien_serah_terima' => 'required|integer|min:1|max:200',
            'status_kelengkapan_serah_terima' => [
                'required',
                Rule::in(array_column(SelectOption::get('status_kelengkapan_serah_terima'), 'value')),
            ],
            'isi_serah_terima' => 'required|string|max:4000',
            'catatan_serah_terima' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Petugas serah dan petugas terima tidak boleh orang yang sama, kecuali
     * mode self-handover yang diizinkan lewat flag `?self_handover=1`.
     */
    private function validasiPetugas(Request $request, array $data): void
    {
        if ($request->boolean('self_handover')) {
            return;
        }

        $serah = (int) ($data['petugas_serah_id'] ?? 0);
        $terima = (int) ($data['petugas_terima_id'] ?? 0);

        if ($serah > 0 && $serah === $terima) {
            throw ValidationException::withMessages([
                'petugas_terima_id' => 'Petugas serah dan petugas terima tidak boleh sama.',
            ]);
        }
    }

    /**
     * Hanya field terpetakan yang disimpan. Nama petugas ditulis ulang dari
     * master, dan `ruang_tujuan` dibuang bila jenisnya bukan transfer ruangan.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $data['nama_petugas_serah'] = $this->namaPegawai((int) ($data['petugas_serah_id'] ?? 0));
        $data['nama_petugas_terima'] = $this->namaPegawai((int) ($data['petugas_terima_id'] ?? 0));

        if (($data['jenis_serah_terima'] ?? '') !== 'B') {
            $data['ruang_tujuan'] = null;
        }

        return $data;
    }

    /**
     * Nama pegawai dari master; null bila id tidak dikenal.
     */
    private function namaPegawai(int $pegawaiId): ?string
    {
        if ($pegawaiId <= 0) {
            return null;
        }

        return DB::table('pegawai')
            ->where('pegawai_id', $pegawaiId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->value('nama_pegawai');
    }

    /**
     * Prefill `isi_serah_terima` dari SBAR terakhir pada kunjungan yang sama.
     * Hanya teks - petugas tetap bebas mengubahnya.
     */
    private function prefillSbar(int $registrasiDetailId): string
    {
        $formId = EmrHelper::formIdBySlug(self::SLUG_SBAR);

        if (! $formId) {
            return '';
        }

        $sbar = EmrHelper::latestValuesByVariabel((int) $formId, $registrasiDetailId, [
            's_situation', 'b_background', 'a_assessment', 'r_recommendation',
        ]);

        if (! $sbar) {
            return '';
        }

        $bagian = [
            'S' => 'SITUASI',
            'B' => 'BACKGROUND',
            'A' => 'ASSESSMENT',
            'R' => 'RECOMMENDATION',
        ];
        $keys = [
            'S' => 's_situation',
            'B' => 'b_background',
            'A' => 'a_assessment',
            'R' => 'r_recommendation',
        ];

        $baris = [];
        foreach ($keys as $kode => $variabel) {
            $teks = trim((string) ($sbar[$variabel] ?? ''));
            if ($teks !== '') {
                $baris[] = $kode.' - '.$bagian[$kode].': '.$teks;
            }
        }

        return implode("\n", $baris);
    }
}
