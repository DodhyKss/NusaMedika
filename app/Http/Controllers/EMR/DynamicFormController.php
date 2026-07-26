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
        // Bisa disesuaikan logicnya jika ada exception tertentu
        $folder_name = Str::slug($form_name, '_'); 

        // Exception untuk SOAP (CPPT) karena foldernya bernama "soap" bukan "cppt_soap"
        if (stripos($form_name, 'soap') !== false || stripos($form_name, 'cppt') !== false) {
            // Karena route asli SOAP masih ada di SoapController, kita bisa redirect ke sana
            // atau langsung panggil controller method-nya jika diinginkan
            return redirect()->route('emr.soap.index', [
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $emr_id
            ]);
        }

        // 3. Cek apakah file index.blade.php ada di dalam folder tersebut
        if (view()->exists("moduls.emr.{$folder_name}.index")) {
            
            $extraData = [];
            
            // 4. Return view dinamis dari folder tersebut
            return view("moduls.emr.{$folder_name}.index", array_merge([
                'registrasi_detail' => $registrasi_detail,
                'emr_id' => $emr_id,
                'form_name' => $form_name
            ], $extraData));
        }

        // 5. Jika folder/file belum dibuat oleh developer, tampilkan halaman under construction
        return view('moduls.emr.unsupported', [
            'form_name' => $form_name,
            'folder_expected' => "resources/views/moduls/emr/{$folder_name}/index.blade.php"
        ]);
    }
}
