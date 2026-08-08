<?php

namespace App\Http\Controllers\EMR\EmrDashboard;

use App\Http\Controllers\Controller;
use App\Models\Registrasi;
use Illuminate\Support\Facades\DB;

class EmrDashboardController extends Controller
{
    public function index($registrasi_detail_id)
    {
        $registrasi_detail = \App\Models\RegistrasiDetail::with('registrasi')->findOrFail($registrasi_detail_id);
        $registrasi = $registrasi_detail->registrasi;

        $profesi_id = session('profesi_id', 1); // Fallback ke profesi ID 1 (Dokter)

        $query = DB::table('header_ehr')
            ->join('form', 'header_ehr.id_dash_menu', '=', 'form.id_dash_menu')
            ->join('akses_ehr', 'form.form_id', '=', 'akses_ehr.form_id')
            ->whereNotNull('form.id_dash_menu')
            ->where('form.id_dash_menu', '<>', '')
            ->whereNull('form.status_batal')
            ->where('akses_ehr.profesi_id', $profesi_id);

        $jenis_rawat = strtolower(trim($registrasi->jenis_rawat));
        if ($jenis_rawat == '1' || $jenis_rawat == 'igd' || $jenis_rawat == env('JENIS_RAWAT_IGD', 'IGD')) {
            $query->where('form.igd', '1');
        } elseif ($jenis_rawat == '2' || $jenis_rawat == 'rj' || $jenis_rawat == env('JENIS_RAWAT_RJ', 'RJ')) {
            $query->where('form.rj', '1');
        } elseif ($jenis_rawat == '3' || $jenis_rawat == 'ri' || $jenis_rawat == env('JENIS_RAWAT_RI', 'RI')) {
            $query->where('form.ri', '1');
        } elseif ($jenis_rawat == '4' || $jenis_rawat == 'mcu' || $jenis_rawat == env('JENIS_RAWAT_MCU', 'MCU')) {
            $query->where('form.mcu', '1');
        }

        $rawMenus = $query->select('header_ehr.nama_menu', 'header_ehr.nama_sub_menu', 'header_ehr.nama_sub_menu_extra', 'form.id_dash_menu')
            ->distinct()
            ->orderBy('header_ehr.nama_menu')
            ->get();

        $ehrMenus = [];
        foreach ($rawMenus as $m) {
            $menu = $m->nama_menu;
            if (! isset($ehrMenus[$menu])) {
                $ehrMenus[$menu] = [];
            }
            if ($m->nama_sub_menu) {
                if (! isset($ehrMenus[$menu][$m->nama_sub_menu])) {
                    $ehrMenus[$menu][$m->nama_sub_menu] = [];
                }
                if ($m->nama_sub_menu_extra) {
                    $ehrMenus[$menu][$m->nama_sub_menu][] = [
                        'nama' => $m->nama_sub_menu_extra,
                        'id' => $m->id_dash_menu,
                    ];
                } else {
                    $ehrMenus[$menu][$m->nama_sub_menu]['id'] = $m->id_dash_menu;
                }
            } else {
                $ehrMenus[$menu]['id'] = $m->id_dash_menu;
            }
        }

        return view('moduls.emr.emr_dashboard.dashboard_pasien', compact('registrasi', 'ehrMenus', 'registrasi_detail_id'));
    }
}
