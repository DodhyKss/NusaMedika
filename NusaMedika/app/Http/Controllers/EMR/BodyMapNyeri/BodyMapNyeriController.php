<?php

namespace App\Http\Controllers\EMR\BodyMapNyeri;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Helpers\NyeriHelper;
use App\Helpers\SelectOption;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Form Body Map Nyeri (form 37, slug `body_map_nyeri`).
 *
 * Pasien/petugas menandai lokasi nyeri di atas gambar tubuh memakai kanvas
 * murni. Hasilnya disimpan sebagai data URL PNG di `emr_detail.value` (kolom
 * TEXT 64 KB), bukan di folder ARSIP seperti legacy — supaya ikut tercetak dan
 * ikut ter-backup bersama `emr_detail`.
 *
 * CATATAN: objek 327 dipakai dua variabel (`keterangan_lokasi` dan
 * `sisi_lokasi`) — form ini TIDAK boleh memakai `EmrHelper::emrDetailByObjek()`.
 */
class BodyMapNyeriController extends Controller
{
    private const SLUG = 'body_map_nyeri';

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
            $emr_data = EmrHelper::wrapData($this->kosong());
            $formAction = route('emr.form.store', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id]);
            $isEdit = false;
            $deleteAction = '';
            $isView = false;
        } else {
            // WAJIB per variabel: objek 327 dipakai dua variabel, jadi
            // emrDetailByObjek() akan menimpa salah satunya.
            $emr_data = EmrHelper::emrDetailByVariabel((int) $emr_id);
            $formAction = route('emr.form.update', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isEdit = true;
            $deleteAction = route('emr.form.destroy', ['form_name' => self::SLUG, 'registrasi_detail_id' => $registrasi_detail_id, 'emr_id' => $emr_id]);
            $isView = request('action') === 'view';
        }

        return view('moduls.EMR.BodyMapNyeri.index', compact(
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

        try {
            EmrHelper::insert((int) $form_id, $this->filteredData($data, (int) $form_id), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Body Map Nyeri berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Body Map Nyeri: '.$e->getMessage())->withInput();
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

            return redirect()->back()->with('success', 'Body Map Nyeri berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Body Map Nyeri: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Body Map Nyeri berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Body Map Nyeri: '.$e->getMessage());
        }
    }

    /**
     * Nilai kosong untuk form baru.
     */
    private function kosong(): array
    {
        return [
            'tanggal_body_map' => now()->format('Y-m-d'),
            'waktu_body_map' => now()->format('H:i'),
            'gambar_lokasi_nyeri' => '',
            'keterangan_lokasi' => '',
            'sisi_lokasi' => '',
            'catatan' => '',
        ];
    }

    private function validated(Request $request): array
    {
        $opsional = [
            'gambar_lokasi_nyeri' => null,
            'catatan' => null,
        ];

        return array_merge($opsional, $request->validate([
            'tanggal_body_map' => 'required|date',
            'waktu_body_map' => 'required|date_format:H:i',

            // Batas 60 KB dipakai karena kolom emr_detail.value bertipe TEXT (64 KB).
            'gambar_lokasi_nyeri' => 'nullable|string|max:60000',
            'keterangan_lokasi' => 'required|string|max:500',
            'sisi_lokasi' => ['required', Rule::in(array_column(SelectOption::get('sisi_lokasi'), 'value'))],

            'catatan' => 'nullable|string|max:1000',
        ]));
    }

    /**
     * Hanya field terpetaman yang disimpan. Gambar body map wajib berupa PNG
     * data URL; nilai lain (termasuk teks kosong) dikosongkan supaya kolom TEXT
     * 64 KB tidak pernah berisi sampah.
     */
    private function filteredData(array $data, int $formId): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels($formId)));

        $data['gambar_lokasi_nyeri'] = NyeriHelper::normalisasiGambar($data['gambar_lokasi_nyeri'] ?? null);

        return $data;
    }
}
