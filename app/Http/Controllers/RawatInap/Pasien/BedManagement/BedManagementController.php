<?php

namespace App\Http\Controllers\RawatInap\Pasien\BedManagement;

use App\Http\Controllers\Controller;
use App\Models\Bagian;
use App\Models\Bed;
use App\Models\KelasRuang;
use App\Models\RegistrasiDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BedManagementController extends Controller
{
    public function index(Request $request)
    {
        $ruangs = $this->ruangPerawatan();

        $ruangId = (int) $request->input('ruang_id');
        if (! $ruangs->contains('bagian_id', $ruangId)) {
            $ruangId = $ruangs->first()?->bagian_id ?? 0;
        }

        $beds = Bed::aktif()
            ->with('pasien')
            ->where('bagian_id', $ruangId)
            ->orderBy('no_kamar')
            ->orderBy('nama_bed')
            ->get();

        $kelasMap = KelasRuang::aktif()->pluck('nama_kelas_ruang', 'kelas_ruang_id');

        $terisiPasienIds = $beds->pluck('pasien_id_1')->filter()->unique();

        $registrasiMap = [];
        if ($terisiPasienIds->isNotEmpty()) {
            $registrasiMap = DB::table('registrasi as r')
                ->join('registrasi_detail as rd', 'rd.registrasi_id', '=', 'r.registrasi_id')
                ->leftJoin('penanggung_rawat as pr', 'pr.registrasi_id', '=', 'r.registrasi_id')
                ->leftJoin('users as u', 'u.user_id', '=', 'pr.rawat_user_id')
                ->leftJoin('pegawai as pg', 'pg.pegawai_id', '=', 'u.pegawai_id')
                ->whereIn('r.pasien_id', $terisiPasienIds->all())
                ->where('r.jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
                ->whereNull('r.tgl_keluar')
                ->where(function ($q) {
                    $q->whereNull('r.status_batal')->orWhere('r.status_batal', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('rd.status_batal')->orWhere('rd.status_batal', 0);
                })
                ->select('r.pasien_id', 'rd.registrasi_detail_id', 'pg.nama_pegawai as dpjp')
                ->get()
                ->keyBy('pasien_id')
                ->all();
        }

        $waitlist = DB::table('registrasi as r')
            ->join('registrasi_detail as rd', 'rd.registrasi_id', '=', 'r.registrasi_id')
            ->join('pasien as p', 'p.pasien_id', '=', 'r.pasien_id')
            ->leftJoin('pasien_nasabah as pn', 'pn.pasien_nasabah_id', '=', 'r.pasien_nasabah_id')
            ->leftJoin('nasabah as n', 'n.nasabah_id', '=', 'pn.nasabah_id')
            ->leftJoin('bed as b', function ($q) use ($ruangId) {
                $q->on('b.pasien_id_1', '=', 'r.pasien_id')
                    ->where('b.bagian_id', '=', $ruangId)
                    ->where(function ($sb) {
                        $sb->whereNull('b.status_batal')->orWhere('b.status_batal', 0);
                    });
            })
            ->where('r.jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
            ->whereNull('r.tgl_keluar')
            ->where(function ($q) {
                $q->whereNull('r.status_batal')->orWhere('r.status_batal', 0);
            })
            ->where('rd.bagian_id', $ruangId)
            ->where(function ($q) {
                $q->whereNull('rd.status_batal')->orWhere('rd.status_batal', 0);
            })
            ->whereNull('b.bed_id')
            ->select(
                'r.registrasi_id',
                'rd.registrasi_detail_id',
                'r.tgl_masuk',
                'p.pasien_id',
                'p.no_mr',
                'p.nama_pasien',
                'n.nama_nasabah'
            )
            ->orderBy('r.tgl_masuk')
            ->get();

        $kosongBeds = $beds->whereNull('pasien_id_1')->pluck('nama_bed', 'bed_id');

        $kosongBedsPerRuang = Bed::aktif()
            ->whereNull('pasien_id_1')
            ->orderBy('bagian_id')
            ->orderBy('no_kamar')
            ->orderBy('nama_bed')
            ->get(['bed_id', 'bagian_id', 'no_kamar', 'nama_bed'])
            ->groupBy('bagian_id')
            ->mapWithKeys(fn ($group, $bagianId) => [$bagianId => $group->mapWithKeys(
                fn ($b) => [$b->bed_id => $b->nama_bed.' (Kamar '.$b->no_kamar.')']
            )->all()])
            ->all();

        return view('moduls.RawatInap.Pasien.BedManagement.bed_management', compact(
            'ruangs',
            'ruangId',
            'beds',
            'kelasMap',
            'registrasiMap',
            'waitlist',
            'kosongBeds',
            'kosongBedsPerRuang'
        ));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:bed,bed_id',
            'registrasi_detail_id' => 'required|integer|exists:registrasi_detail,registrasi_detail_id',
        ]);

        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->where('bed_id', $request->bed_id)->firstOrFail();

            if ($bed->pasien_id_1) {
                return back()->with('error', 'Bed sudah terisi pasien lain.');
            }

            $detail = RegistrasiDetail::with('registrasi')
                ->where('registrasi_detail_id', $request->registrasi_detail_id)
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->firstOrFail();

            $registrasi = $detail->registrasi;

            if (! $registrasi || $registrasi->jenis_rawat !== env('JENIS_RAWAT_RI', 'RI')) {
                return back()->with('error', 'Registrasi rawat inap tidak ditemukan.');
            }

            $sudahPunyaBed = Bed::aktif()
                ->where('pasien_id_1', $registrasi->pasien_id)
                ->where('bagian_id', $detail->bagian_id)
                ->exists();

            if ($sudahPunyaBed) {
                return back()->with('error', 'Pasien sudah memiliki bed aktif pada ruang ini.');
            }

            $kelas = $detail->hak_kelas_id
                ? KelasRuang::aktif()->where('kelas_ruang_id', $detail->hak_kelas_id)->first()
                : null;

            $bed->pasien_id_1 = $registrasi->pasien_id;
            $bed->tgl_masuk = now();
            $bed->status_bed = 1;
            $bed->kelas_id = $kelas?->kelas_ruang_id ?? $bed->kelas_id;
            $bed->kodekelas = $kelas?->kelas_bpjs ?? $bed->kodekelas;
            $bed->namakelas = $kelas?->nama_kelas_ruang ?? $bed->namakelas;
            $bed->flag_persiapan_pulang = 2;
            $bed->tgl_pulang = null;
            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            DB::commit();

            return back()->with('success', 'Pasien berhasil ditempatkan ke bed.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menempatkan pasien: '.$e->getMessage());
        }
    }

    public function ready(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:bed,bed_id',
            'tgl_pulang' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->where('bed_id', $request->bed_id)->whereNotNull('pasien_id_1')->firstOrFail();

            if (($bed->flag_persiapan_pulang ?? 0) === 1) {
                $bed->flag_persiapan_pulang = 2;
                $bed->tgl_pulang = null;
            } else {
                $bed->flag_persiapan_pulang = 1;
                $bed->tgl_pulang = $request->filled('tgl_pulang')
                    ? date('Y-m-d H:i:s', strtotime($request->tgl_pulang))
                    : now();
            }

            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            DB::commit();

            return back()->with('success', 'Status persiapan pulang bed diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memperbarui status bed: '.$e->getMessage());
        }
    }

    public function release(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:bed,bed_id',
        ]);

        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->where('bed_id', $request->bed_id)->whereNotNull('pasien_id_1')->firstOrFail();

            $bed->pasien_id_1 = null;
            $bed->pasien_id_2 = null;
            $bed->tgl_masuk = null;
            $bed->status_bed = 0;
            $bed->flag_persiapan_pulang = 2;
            $bed->tgl_pulang = null;
            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            DB::commit();

            return back()->with('success', 'Pasien dilepas dari bed.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal melepas pasien: '.$e->getMessage());
        }
    }

    public function pulang(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:bed,bed_id',
            'tgl_pulang' => 'required|date',
            'alasan_pulang' => 'nullable|string|max:255',
        ]);

        $tglKeluar = Carbon::parse($request->tgl_pulang);

        DB::beginTransaction();
        try {
            $bed = Bed::aktif()->where('bed_id', $request->bed_id)->whereNotNull('pasien_id_1')->firstOrFail();
            $pasienId = $bed->pasien_id_1;

            // Kosongkan bed
            $bed->pasien_id_1 = null;
            $bed->pasien_id_2 = null;
            $bed->tgl_masuk = null;
            $bed->status_bed = 0;
            $bed->flag_persiapan_pulang = 2;
            $bed->tgl_pulang = null;
            $bed->mod_time = now();
            $bed->mod_user_id = Auth::id();
            $bed->save();

            // Selesaikan registrasi rawat inap aktif pasien
            $registrasi = DB::table('registrasi')
                ->where('pasien_id', $pasienId)
                ->where('jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
                ->whereNull('tgl_keluar')
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->orderBy('registrasi_id', 'desc')
                ->first();

            if ($registrasi) {
                DB::table('registrasi')
                    ->where('registrasi_id', $registrasi->registrasi_id)
                    ->update([
                        'tgl_keluar' => $tglKeluar,
                        'alasan_pulang' => $request->input('alasan_pulang'),
                        'mod_time' => now(),
                        'mod_user_id' => Auth::id(),
                    ]);

                $detail = DB::table('registrasi_detail')
                    ->where('registrasi_id', $registrasi->registrasi_id)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->first();

                if ($detail) {
                    DB::table('registrasi_detail')
                        ->where('registrasi_detail_id', $detail->registrasi_detail_id)
                        ->update([
                            'check_out' => $tglKeluar,
                            'mod_time' => now(),
                            'mod_user_id' => Auth::id(),
                        ]);

                    DB::table('bill_temp')
                        ->where('registrasi_detail_id', $detail->registrasi_detail_id)
                        ->where(function ($q) {
                            $q->whereNull('status_batal')->orWhere('status_batal', 0);
                        })
                        ->update([
                            'status_selesai' => 1,
                            'mod_time' => now(),
                            'mod_user_id' => Auth::id(),
                        ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Pasien berhasil dipulangkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memulangkan pasien: '.$e->getMessage());
        }
    }

    public function move(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:bed,bed_id',
            'target_bed_id' => 'required|integer|exists:bed,bed_id',
            'ruang_id' => 'nullable|integer|exists:bagian,bagian_id',
        ]);

        DB::beginTransaction();
        try {
            $current = Bed::aktif()->where('bed_id', $request->bed_id)->whereNotNull('pasien_id_1')->firstOrFail();

            if (($current->flag_persiapan_pulang ?? 0) === 1) {
                return back()->with('error', 'Pasien sedang dalam persiapan pulang, tidak dapat dipindah.');
            }

            $target = Bed::aktif()->where('bed_id', $request->target_bed_id)->whereNull('pasien_id_1')->firstOrFail();

            if ($request->filled('ruang_id')) {
                if ((int) $request->ruang_id === (int) $current->bagian_id) {
                    return back()->with('error', 'Pilih ruangan yang berbeda dari ruangan saat ini.');
                }

                if ((int) $target->bagian_id !== (int) $request->ruang_id) {
                    return back()->with('error', 'Bed tujuan tidak berada pada ruangan terpilih.');
                }
            } elseif ((int) $target->bagian_id !== (int) $current->bagian_id) {
                return back()->with('error', 'Pindah bed hanya untuk bed kosong pada ruangan yang sama. Gunakan Pindah Ruangan untuk lintas ruangan.');
            }

            $pasienId = $current->pasien_id_1;

            // Isi bed tujuan dengan data pasien dari bed sebelumnya
            $target->pasien_id_1 = $pasienId;
            $target->tgl_masuk = $current->tgl_masuk;
            $target->status_bed = 1;
            $target->kelas_id = $current->kelas_id;
            $target->kodekelas = $current->kodekelas;
            $target->namakelas = $current->namakelas;
            $target->flag_persiapan_pulang = $current->flag_persiapan_pulang ?? 2;
            $target->tgl_pulang = $current->tgl_pulang;
            $target->mod_time = now();
            $target->mod_user_id = Auth::id();
            $target->save();

            // Kosongkan bed sebelumnya
            $current->pasien_id_1 = null;
            $current->pasien_id_2 = null;
            $current->tgl_masuk = null;
            $current->status_bed = 0;
            $current->flag_persiapan_pulang = 2;
            $current->tgl_pulang = null;
            $current->mod_time = now();
            $current->mod_user_id = Auth::id();
            $current->save();

            // Pindah ruangan: ikutkan ruang pasien di registrasi_detail & bill_temp
            if ($request->filled('ruang_id')) {
                $rd = RegistrasiDetail::where('registrasi_id', function ($q) use ($pasienId) {
                    $q->select('registrasi_id')
                        ->from('registrasi')
                        ->where('pasien_id', $pasienId)
                        ->where('jenis_rawat', env('JENIS_RAWAT_RI', 'RI'))
                        ->whereNull('tgl_keluar')
                        ->where(function ($sq) {
                            $sq->whereNull('status_batal')->orWhere('status_batal', 0);
                        })
                        ->limit(1);
                })
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    })
                    ->first();

                if ($rd) {
                    $rd->bagian_id = $request->ruang_id;
                    $rd->mod_time = now();
                    $rd->mod_user_id = Auth::id();
                    $rd->save();

                    DB::table('bill_temp')
                        ->where('registrasi_detail_id', $rd->registrasi_detail_id)
                        ->where(function ($q) {
                            $q->whereNull('status_batal')->orWhere('status_batal', 0);
                        })
                        ->update(['bagian_id' => $request->ruang_id]);
                }
            }

            DB::commit();

            return back()->with('success', 'Pasien berhasil dipindahkan ke bed tujuan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memindahkan pasien: '.$e->getMessage());
        }
    }

    private function ruangPerawatan()
    {
        return Bagian::where('referensi_bagian_id', env('REF_BAGIAN_RANAP', 2))
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('bagian_id')
            ->get(['bagian_id', 'nama_bagian']);
    }
}
