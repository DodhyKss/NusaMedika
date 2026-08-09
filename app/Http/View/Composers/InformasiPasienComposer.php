<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InformasiPasienComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $registrasi_detail_id = request()->route('registrasi_detail_id');
        if ($registrasi_detail_id) {
            try {
                $informasi = DB::selectOne('
                select
                    r.jenis_rawat,
                    r.tgl_masuk as tgl_layanan,
                    b.nama_bagian,
                    p.nama_pasien, 
                    p.tempat_lahir, 
                    p.tgl_lahir, 
                    p.ktp, 
                    p.jenis_kelamin, 
                    p.no_hp, 
                    p.alamat,
                    pg.nama_pegawai,
                    n.nama_nasabah,
                    rs.sep
                from registrasi_detail rd
                    join registrasi r on r.registrasi_id = rd.registrasi_id
                    join pasien p on p.pasien_id = r.pasien_id
                    left join penanggung_rawat pr on pr.registrasi_id = r.registrasi_id and (pr.status_batal is null or pr.status_batal = 0)
                    left join users u on u.user_id = pr.rawat_user_id and (u.status_batal is null or u.status_batal = 0)
                    left join pegawai pg on pg.pegawai_id = u.pegawai_id and (pg.status_batal is null or pg.status_batal = 0)
                    join pasien_nasabah pn on pn.pasien_nasabah_id = r.pasien_nasabah_id
                    join nasabah n on n.nasabah_id = pn.nasabah_id
                    left join rujukan_sep rs on rs.registrasi_id = r.registrasi_id and (rs.status_batal is null or rs.status_batal = 0)
                    join bagian b on b.bagian_id = rd.bagian_id
                where 
                    rd.registrasi_detail_id = :registrasi_detail_id
                    and (rd.status_batal is null or rd.status_batal = 0)
                    and (r.status_batal is null or r.status_batal = 0)
                    and (p.status_batal is null or p.status_batal = 0)
                    and (pn.status_batal is null or pn.status_batal = 0)
                    and (n.status_batal is null or n.status_batal = 0)
                    and (b.status_batal is null or b.status_batal = 0)
                ',
                    [
                        'registrasi_detail_id' => $registrasi_detail_id,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('InformasiPasienComposer query failed: '.$e->getMessage());
                $informasi = null;
            }
            $view->with('pasien', $informasi);
        }
    }
}
