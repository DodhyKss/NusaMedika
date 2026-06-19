<?php

namespace App\Http\View\Composers;

use App\Models\RegistrasiDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InformasiPasienComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $registrasi_detail_id = request()->route('registrasi_detail_id');
        if ($registrasi_detail_id) {
            $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->find($registrasi_detail_id);
            
            if ($registrasi_detail) {
                // Masukkan ke object agar bisa dipanggil di blade
                $registrasi_detail->diagnosa_awal = 'Belum ada Diagnosa';
            }
            $pasien = ($registrasi_detail && $registrasi_detail->registrasi) ? $registrasi_detail->registrasi->pasien : null;
            $view->with('registrasi_detail', $registrasi_detail);
            $view->with('pasien', $pasien);
        }
    }
}
