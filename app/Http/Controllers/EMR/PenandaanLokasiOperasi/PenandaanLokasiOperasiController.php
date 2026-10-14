<?php

namespace App\Http\Controllers\EMR\PenandaanLokasiOperasi;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Form Penandaan Lokasi Operasi (form 95, slug `penandaan_lokasi_operasi`).
 *
 * Site marking WHO: penandaan lokasi operasi oleh dokter bedah sebelum
 * masuk ruang operasi (KONSEP §13). Safety gate: konfirmasi pasien
 * "Tidak" wajib menyertakan alasan.
 */
class PenandaanLokasiOperasiController extends Controller
{
    private const SLUG = 'penandaan_lokasi_operasi';

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

        return view('moduls.EMR.PenandaanLokasiOperasi.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ));
    }

    public function store(Request $request, $registrasi_detail_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'create'), 403);

        $user = Auth::user();
        if (! $user || ! $user->pegawai_id || ! $user->user_id) {
            return redirect()->back()->with('error', 'Sesi Anda Telah Habis Silahkan Login Kembali!');
        }

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::insert((int) $form_id, $data, (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Penandaan Lokasi Operasi berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Penandaan Lokasi Operasi: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request, (int) $form_id);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $data);

            return redirect()->back()->with('success', 'Penandaan Lokasi Operasi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Penandaan Lokasi Operasi: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Penandaan Lokasi Operasi berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Penandaan Lokasi Operasi: '.$e->getMessage());
        }
    }

    private function validated(Request $request, int $form_id): array
    {
        $rules = [
            'tanggal_penandaan' => 'required|date',
            'jam_penandaan' => 'required|date_format:H:i',
            'nama_tindakan' => 'required|string|max:2000',
            'lokasi_anatomi' => 'required|string|max:2000',
            'sisi_operasi' => 'required|in:Kanan,Kiri,Bilateral,Tanpa Sisi',
            'jenis_penandaan' => 'required|in:Spidol Penanda Kulit,Plester Penanda,Sayatan Kulit',
            'petugas_penanda' => 'required|integer|exists:pegawai,pegawai_id',
            'konfirmasi_pasien' => 'required|in:Ya,Tidak',
            'lampiran_penandaan' => 'nullable|string|max:60000',
            'alasan_penandaan' => 'required_if:konfirmasi_pasien,Tidak|string|max:2000',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $data = array_merge(
            array_fill_keys(EmrHelper::objekVariabels($form_id), null),
            $validator->validated()
        );

        // Lampiran foto hanya disimpan bila berupa data URL gambar
        // (input file tanpa name → JS FileReader → hidden input).
        $lampiran = $data['lampiran_penandaan'] ?? null;
        if (is_string($lampiran) && ! preg_match('#^data:image/#', $lampiran)) {
            $data['lampiran_penandaan'] = null;
        }

        return $data;
    }

    private function kosong(): array
    {
        return [
            'tanggal_penandaan' => '', 'jam_penandaan' => '',
            'nama_tindakan' => '', 'lokasi_anatomi' => '', 'sisi_operasi' => '',
            'jenis_penandaan' => '', 'petugas_penanda' => '', 'konfirmasi_pasien' => '',
            'lampiran_penandaan' => null, 'alasan_penandaan' => '',
        ];
    }
}
