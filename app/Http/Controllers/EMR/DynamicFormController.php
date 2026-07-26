<?php

namespace App\Http\Controllers\EMR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RegistrasiDetail;

class DynamicFormController extends Controller
{
    public function index($form_name, $registrasi_detail_id, $emr_id = null)
    {
        // 1. Ambil data dasar yang selalu dibutuhkan oleh SEMUA form EMR
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);
        
        // 2. Format folder_name dari $form_name
        $folder_name = Str::slug($form_name, '_'); 

        $extraData = [];

        // 3. Cek apakah ada controller spesifik untuk form ini
        // Contoh: 'pengkajian_awal_keperawatan' -> App\Http\Controllers\EMR\PengkajianAwalKeperawatan\PengkajianAwalKeperawatanController
        $studlyName = Str::studly($form_name);

        $controllerClass = "App\\Http\\Controllers\\EMR\\{$studlyName}\\{$studlyName}Controller";
        
        if (class_exists($controllerClass)) {
            $controller = app()->make($controllerClass);
            
            // Panggil method getData di controller spesifik untuk mengambil logic/data
            if (method_exists($controller, 'index')) {
                // Alternatif: jika menggunakan method index
                $response = app()->call([$controller, 'index'], [
                    'registrasi_detail_id' => $registrasi_detail_id,
                    'emr_id' => $emr_id,
                    'form_name' => $form_name
                ]);
                
                // Jika controller mengembalikan view/redirect sendiri, langsung return
                if ($response instanceof \Illuminate\View\View || $response instanceof \Illuminate\Http\RedirectResponse) {
                    return $response;
                }
            }
        }

        // 4. Cek apakah file index.blade.php ada di dalam folder tersebut
        if (view()->exists("moduls.emr.{$folder_name}.index")) {
            
            // 5. Return view dinamis dari folder tersebut beserta datanya
            return view("moduls.emr.{$folder_name}.index", array_merge([
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $emr_id,
                'form_name' => $form_name,
                'registrasi_detail' => $registrasi_detail
            ], $extraData));
        }

        // 5. Jika folder/file belum dibuat oleh developer, tampilkan halaman under construction
        return view('moduls.emr.unsupported', [
            'form_name' => $form_name,
            'folder_expected' => "resources/views/moduls/emr/{$folder_name}/index.blade.php"
        ]);
    }
}
