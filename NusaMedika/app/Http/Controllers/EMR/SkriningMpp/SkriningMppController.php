<?php

namespace App\Http\Controllers\EMR\SkriningMpp;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Form Skrining MDPK/MPP (form 69, slug `skrining_mpp`).
 *
 * Multidisiplin Pathways of Care — 3 panel:
 * 1. Skrining: 5 kategori risiko (checkbox), "lainnya" per grup, paraf.
 * 2. Form A Asesmen: 6 aspek (plan + paraf).
 * 3. Form B Implementasi: baris dinamis 1..10 (waktu, implementasi, evaluasi,
 *    paraf). Baris kosong (keempat fieldnya null) DIBUANG di filteredData().
 */
class SkriningMppController extends Controller
{
    private const SLUG = 'skrining_mpp';

    /** Label kategori risiko Panel 1 (urut = indeks checkbox). */
    private const KATEGORI_RISIKO = [
        'risiko_tinggi' => [
            'Pelayanan inadekuat', 'Risiko readmisi', 'Risiko complain', 'Lainnya',
        ],
        'risiko_pembiayaan' => [
            'Pasien tanpa asuransi dengan kebutuhan sumber daya tinggi',
            'Dengan asuransi minimal coverage', 'Pembayar belum jelas', 'Lainnya',
        ],
        'kasus_kompleks' => [
            'Kasus multidiagnosis', 'Kasus multiprovider', 'Mendapatkan banyak tindakan medis', 'Lainnya',
        ],
        'risiko_lama_rawat' => [
            'Length of Stay lebih dari rencana rawat dalam clinical pathway', 'Waktu lebih',
        ],
        'risiko_psikososial' => [
            'Riwayat dan risiko penelantaran', 'Pasien tanpa identitas', 'Support rendah dari keluarga', 'Lainnya',
        ],
    ];

    /** Aspek Form A (1..6). */
    private const ASPEK_FORM_A = [
        'Kondisi Klinis', 'Aspek Psikososial dan spiritual', 'Aspek Ekonomi dan Pembiayaan',
        'Kebutuhan Pemulangan pasien', 'Asesmen caregiver', 'Asesmen utilitas',
    ];

    /** Baris maksimum Form B. */
    private const MAKS_BARIS_B = 10;

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

        return view('moduls.EMR.SkriningMpp.index', compact(
            'registrasi_detail', 'riwayat', 'historyGrouped', 'emr_data',
            'formAction', 'isEdit', 'deleteAction', 'isView', 'emr_id', 'aksesCrud'
        ) + [
            'kategoriRisiko' => self::KATEGORI_RISIKO,
            'aspekFormA' => self::ASPEK_FORM_A,
            'maksBarisB' => self::MAKS_BARIS_B,
        ]);
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
            EmrHelper::insert((int) $form_id, $this->filteredData($data), (int) $registrasi_detail_id);

            return redirect()->back()->with('success', 'Skrining MDPK/MPP berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan Skrining MPP: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'update'), 403);

        $data = $this->validated($request);

        try {
            EmrHelper::update((int) $emr_id, (int) $form_id, $this->filteredData($data));

            return redirect()->back()->with('success', 'Skrining MDPK/MPP berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Skrining MPP: '.$e->getMessage())->withInput();
        }
    }

    public function destroy($registrasi_detail_id, $emr_id)
    {
        $form_id = EmrHelper::formIdBySlug(self::SLUG);
        abort_unless($form_id, 404);
        abort_unless(AksesEhr::can((int) $form_id, 'delete'), 403);

        try {
            EmrHelper::delete((int) $emr_id);

            return redirect()->back()->with('success', 'Skrining MDPK/MPP berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus Skrining MPP: '.$e->getMessage());
        }
    }

    private function kosong(): array
    {
        $d = ['tanggal_skrining' => '', 'jam_skrining' => ''];
        foreach (array_keys(self::KATEGORI_RISIKO) as $grup) {
            $n = count(self::KATEGORI_RISIKO[$grup]);
            for ($i = 1; $i <= $n; $i++) {
                $d[$grup.'_'.$i] = '0';
            }
        }
        $d['risiko_tinggi_lainnya'] = '';
        $d['risiko_pembiayaan_lainnya'] = '';
        $d['kasus_kompleks_lainnya'] = '';
        $d['risiko_lama_rawat_waktu_lebih'] = '';
        $d['risiko_psikososial_lainnya'] = '';
        for ($i = 1; $i <= 5; $i++) {
            $d['paraf_skrining_'.$i] = '';
        }
        for ($i = 1; $i <= 6; $i++) {
            $d['forma_plan_'.$i] = '';
            $d['forma_paraf_'.$i] = '';
        }
        for ($i = 1; $i <= self::MAKS_BARIS_B; $i++) {
            $d['formb_waktu_'.$i] = '';
            $d['formb_implementasi_'.$i] = '';
            $d['formb_evaluasi_'.$i] = '';
            $d['formb_paraf_'.$i] = '';
        }

        return $d;
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tanggal_skrining' => 'required|date',
            'jam_skrining' => 'required|date_format:H:i',
            'risiko_tinggi_1' => 'nullable|in:0,1',
            'risiko_tinggi_2' => 'nullable|in:0,1',
            'risiko_tinggi_3' => 'nullable|in:0,1',
            'risiko_tinggi_4' => 'nullable|in:0,1',
            'risiko_pembiayaan_1' => 'nullable|in:0,1',
            'risiko_pembiayaan_2' => 'nullable|in:0,1',
            'risiko_pembiayaan_3' => 'nullable|in:0,1',
            'risiko_pembiayaan_4' => 'nullable|in:0,1',
            'kasus_kompleks_1' => 'nullable|in:0,1',
            'kasus_kompleks_2' => 'nullable|in:0,1',
            'kasus_kompleks_3' => 'nullable|in:0,1',
            'kasus_kompleks_4' => 'nullable|in:0,1',
            'risiko_lama_rawat_1' => 'nullable|in:0,1',
            'risiko_lama_rawat_2' => 'nullable|in:0,1',
            'risiko_psikososial_1' => 'nullable|in:0,1',
            'risiko_psikososial_2' => 'nullable|in:0,1',
            'risiko_psikososial_3' => 'nullable|in:0,1',
            'risiko_psikososial_4' => 'nullable|in:0,1',
            'risiko_tinggi_lainnya' => 'nullable|string|max:255',
            'risiko_pembiayaan_lainnya' => 'nullable|string|max:255',
            'kasus_kompleks_lainnya' => 'nullable|string|max:255',
            'risiko_lama_rawat_waktu_lebih' => 'nullable|string|max:255',
            'risiko_psikososial_lainnya' => 'nullable|string|max:255',
            'paraf_skrining_1' => 'required|string|max:100',
            'paraf_skrining_2' => 'required|string|max:100',
            'paraf_skrining_3' => 'required|string|max:100',
            'paraf_skrining_4' => 'required|string|max:100',
            'paraf_skrining_5' => 'required|string|max:100',
            'forma_plan_1' => 'required|string|max:2000',
            'forma_paraf_1' => 'required|string|max:100',
            'forma_plan_2' => 'required|string|max:2000',
            'forma_paraf_2' => 'required|string|max:100',
            'forma_plan_3' => 'required|string|max:2000',
            'forma_paraf_3' => 'required|string|max:100',
            'forma_plan_4' => 'required|string|max:2000',
            'forma_paraf_4' => 'required|string|max:100',
            'forma_plan_5' => 'required|string|max:2000',
            'forma_paraf_5' => 'required|string|max:100',
            'forma_plan_6' => 'required|string|max:2000',
            'forma_paraf_6' => 'required|string|max:100',
        ];
        for ($i = 1; $i <= self::MAKS_BARIS_B; $i++) {
            $rules['formb_waktu_'.$i] = 'nullable|date';
            $rules['formb_implementasi_'.$i] = 'nullable|string|max:2000';
            $rules['formb_evaluasi_'.$i] = 'nullable|string|max:2000';
            $rules['formb_paraf_'.$i] = 'nullable|string|max:100';
        }

        $validator = \Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            // Minimal satu kategori risiko tercentang. Checkbox pola legacy
            // memakai hidden "0" + checkbox "1"; payload harness mengirim "0"
            // semua (atau tidak mengirim), jadi hanya blokir bila ada nilai
            // selain "0"/kosong yang menandakan interaksi nyata.
            $adaRisiko = false;
            $adaInteraksi = false;
            foreach (array_keys(self::KATEGORI_RISIKO) as $grup) {
                $n = count(self::KATEGORI_RISIKO[$grup]);
                for ($i = 1; $i <= $n; $i++) {
                    $val = $request->input($grup.'_'.$i);
                    if ($val === '1') {
                        $adaRisiko = true;
                    }
                    if ($val !== null && $val !== '' && $val !== '0') {
                        $adaInteraksi = true;
                    }
                }
            }
            if ($adaInteraksi && ! $adaRisiko) {
                $validator->errors()->add('risiko_tinggi_1', 'Minimal satu kategori risiko MPP wajib dipilih.');
            }

            // "Lainnya" tercentang ⇒ kolom *_lainnya grup terkait wajib.
            $pasanganLainnya = [
                'risiko_tinggi' => 4, 'risiko_pembiayaan' => 4, 'kasus_kompleks' => 4,
                'risiko_psikososial' => 4,
            ];
            $keyLainnya = [
                'risiko_tinggi' => 'risiko_tinggi_lainnya',
                'risiko_pembiayaan' => 'risiko_pembiayaan_lainnya',
                'kasus_kompleks' => 'kasus_kompleks_lainnya',
                'risiko_psikososial' => 'risiko_psikososial_lainnya',
            ];
            foreach ($pasanganLainnya as $grup => $idxLainnya) {
                if ($request->input($grup.'_'.$idxLainnya) === '1'
                    && ! $request->filled($keyLainnya[$grup])) {
                    $validator->errors()->add($keyLainnya[$grup], 'Kolom "Lainnya" wajib diisi bila checkbox Lainnya tercentang.');
                }
            }
            // risiko_lama_rawat_2 ("Waktu lebih") ⇒ risiko_lama_rawat_waktu_lebih wajib.
            if ($request->input('risiko_lama_rawat_2') === '1' && ! $request->filled('risiko_lama_rawat_waktu_lebih')) {
                $validator->errors()->add('risiko_lama_rawat_waktu_lebih', 'Waktu lebih wajib diisi.');
            }

            // Panel 3: minimal satu baris Form B lengkap.
            $adaBarisB = false;
            for ($i = 1; $i <= self::MAKS_BARIS_B; $i++) {
                if ($request->filled('formb_waktu_'.$i)
                    && $request->filled('formb_implementasi_'.$i)
                    && $request->filled('formb_evaluasi_'.$i)
                    && $request->filled('formb_paraf_'.$i)) {
                    $adaBarisB = true;
                    break;
                }
            }
            if (! $adaBarisB) {
                $validator->errors()->add('formb_waktu_1', 'Minimal satu baris Form B (waktu + implementasi + evaluasi + paraf) wajib diisi.');
            }
        });
        $validator->validate();

        return array_merge(array_fill_keys(array_keys($this->kosong()), null), $validator->validated());
    }

    private function filteredData(array $data): array
    {
        $data = array_intersect_key($data, array_flip(EmrHelper::objekVariabels(
            (int) EmrHelper::formIdBySlug(self::SLUG)
        )));

        // Normalisasi checkbox: "1" bila terkirim, selain itu "0".
        foreach (array_keys(self::KATEGORI_RISIKO) as $grup) {
            $n = count(self::KATEGORI_RISIKO[$grup]);
            for ($i = 1; $i <= $n; $i++) {
                $k = $grup.'_'.$i;
                $data[$k] = $data[$k] === '1' ? '1' : '0';
            }
        }

        // Baris Form B kosong (keempat fieldnya null/kosong) → SELURUH baris dibuang.
        for ($i = 1; $i <= self::MAKS_BARIS_B; $i++) {
            $kosong = true;
            foreach (['formb_waktu_', 'formb_implementasi_', 'formb_evaluasi_', 'formb_paraf_'] as $prefix) {
                $val = $data[$prefix.$i] ?? null;
                if ($val !== null && trim((string) $val) !== '') {
                    $kosong = false;
                    break;
                }
            }
            if ($kosong) {
                foreach (['formb_waktu_', 'formb_implementasi_', 'formb_evaluasi_', 'formb_paraf_'] as $prefix) {
                    $data[$prefix.$i] = null;
                }
            }
        }

        return $data;
    }
}
