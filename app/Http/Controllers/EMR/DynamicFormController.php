<?php

namespace App\Http\Controllers\EMR;

use App\Helpers\AksesEhr;
use App\Helpers\EmrHelper;
use App\Http\Controllers\Controller;
use App\Models\RegistrasiDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DynamicFormController extends Controller
{
    /**
     * Tampilkan form EMR dinamis untuk sebuah pasien/registrasi_detail.
     * Controller khusus (App\Http\Controllers\EMR\{Studly}\{Studly}Controller)
     * menang bila ada; jika tidak, fallback ke view moduls.EMR.{slug}.index
     * dengan data generik dari EmrHelper (form, objek_map, emr_data).
     */
    public function index($form_name, $registrasi_detail_id, $emr_id = null)
    {
        $registrasi_detail = RegistrasiDetail::with('registrasi.pasien')->findOrFail($registrasi_detail_id);

        $slug = Str::slug($form_name, '_');
        $form = EmrHelper::formBySlug($slug);

        // Gate akses (read) bila form tercatat di tabel form
        if ($form) {
            abort_unless(AksesEhr::can((int) $form->form_id, 'read'), 403);
            $aksesCrud = AksesEhr::flags((int) $form->form_id);
            $riwayat = EmrHelper::emrList((int) $form->form_id, (int) $registrasi_detail->registrasi_id);

            if (!$emr_id && !($aksesCrud['create'] ?? false) && $riwayat->isNotEmpty()) {
                return redirect()->route('emr.dynamic.index', [
                    'form_name' => $form_name,
                    'registrasi_detail_id' => $registrasi_detail_id,
                    'emr_id' => $riwayat->first()->emr_id,
                    'action' => 'view'
                ]);
            }
        }

        // 1. Controller spesifik bila ada (SOAP, Pengkajian Awal, dst.)
        $studlyName = Str::studly($form_name);
        $controllerClass = "App\\Http\\Controllers\\EMR\\{$studlyName}\\{$studlyName}Controller";

        if (class_exists($controllerClass)) {
            $controller = app()->make($controllerClass);

            if (method_exists($controller, 'index')) {
                $response = app()->call([$controller, 'index'], [
                    'registrasi_detail_id' => $registrasi_detail_id,
                    'emr_id' => $emr_id,
                    'form_name' => $form_name,
                ]);

                if ($response instanceof View || $response instanceof RedirectResponse) {
                    return $response;
                }
            }
        }

        // 2. Fallback generik: tampilkan detail data yang dibutuhkan view form
        if (view()->exists("moduls.EMR.{$slug}.index")) {
            $aksesCrud = $form ? AksesEhr::flags((int) $form->form_id) : AksesEhr::flags(0);

            return view("moduls.EMR.{$slug}.index", [
                'registrasi_detail_id' => $registrasi_detail_id,
                'emr_id' => $emr_id,
                'form_name' => $form_name,
                'registrasi_detail' => $registrasi_detail,
                'emr_form' => $form,
                'objek_map' => $form ? EmrHelper::objekMap((int) $form->form_id) : [],
                'emr_data' => $emr_id ? EmrHelper::emrDetailByVariabel((int) $emr_id) : EmrHelper::wrapData([]),
                'riwayat' => $form ? EmrHelper::emrList((int) $form->form_id, (int) $registrasi_detail->registrasi_id) : collect(),
                'aksesCrud' => $aksesCrud,
                'isEdit' => (bool) $emr_id,
                'isView' => request('action') === 'view',
            ]);
        }

        // 3. Belum dibuat: halaman under construction
        return view('moduls.EMR.Unsupported', [
            'form_name' => $form_name,
            'folder_expected' => "resources/views/moduls/EMR/{$slug}/index.blade.php",
        ]);
    }

    /**
     * Simpan EMR generik (route tunggal untuk SEMUA form baru:
     * POST /emr/form-store/{form_name}/{registrasi_detail_id}).
     * Delegasikan ke controller spesifik (SOAP, Pengkajian Awal) bila ada.
     */
    public function store(Request $request, $form_name, $registrasi_detail_id)
    {
        return $this->delegateToSpecificController($form_name, $request, 'store', [
            'registrasi_detail_id' => $registrasi_detail_id,
        ]);
    }

    /**
     * Ubah EMR generik.
     */
    public function update(Request $request, $form_name, $registrasi_detail_id, $emr_id)
    {
        return $this->delegateToSpecificController($form_name, $request, 'update', [
            'registrasi_detail_id' => $registrasi_detail_id,
            'emr_id' => $emr_id,
        ]);
    }

    /**
     * Batalkan EMR generik (soft delete).
     */
    public function destroy($form_name, $registrasi_detail_id, $emr_id)
    {
        return $this->delegateToSpecificController($form_name, null, 'destroy', [
            'registrasi_detail_id' => $registrasi_detail_id,
            'emr_id' => $emr_id,
        ]);
    }

    /**
     * Panggil method store/update/destroy pada controller spesifik
     * (App\Http\Controllers\EMR\{Studly}\{Studly}Controller) bila ada.
     * Jika tidak, fallback ke EmrHelper generik.
     */
    private function delegateToSpecificController(string $form_name, ?Request $request, string $method, array $params): RedirectResponse
    {
        $slug = Str::slug($form_name, '_');
        $form = EmrHelper::formBySlug($slug);
        abort_unless($form, 404);

        $studlyName = Str::studly($form_name);
        $controllerClass = "App\\Http\\Controllers\\EMR\\{$studlyName}\\{$studlyName}Controller";

        // Gate akses
        $actionGate = match ($method) {
            'store' => 'create',
            'update' => 'update',
            'destroy' => 'delete',
            default => 'read',
        };
        abort_unless(AksesEhr::can((int) $form->form_id, $actionGate), 403);

        // Coba delegasikan ke controller spesifik
        if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
            $controller = app()->make($controllerClass);

            $callParams = array_merge($params, $request ? ['request' => $request] : []);
            $response = app()->call([$controller, $method], $callParams);

            if ($response instanceof RedirectResponse) {
                return $response;
            }
        }

        // Fallback generik: pakai EmrHelper langsung
        return $this->genericCrud($form_name, $request, $method, $params);
    }

    private function genericCrud(string $form_name, ?Request $request, string $method, array $params): RedirectResponse
    {
        $slug = Str::slug($form_name, '_');
        $form = EmrHelper::formBySlug($slug);

        return match ($method) {
            'store' => $this->genericStore($form, $request, $params),
            'update' => $this->genericUpdate($form, $request, $params),
            'destroy' => $this->genericDestroy($form, $params),
            default => redirect()->route('emr.dynamic.index', ['form_name' => $form_name, 'registrasi_detail_id' => $params['registrasi_detail_id']])
                ->with('error', 'Method tidak dikenal'),
        };
    }

    private function genericStore($form, Request $request, array $params): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);
        EmrHelper::insert((int) $form->form_id, $data, (int) $params['registrasi_detail_id']);

        return redirect()->route('emr.dynamic.index', ['form_name' => $form->slug, 'registrasi_detail_id' => $params['registrasi_detail_id']])
            ->with('success', 'Data '.$form->nama_form.' berhasil disimpan.');
    }

    private function genericUpdate($form, Request $request, array $params): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);
        EmrHelper::update((int) $params['emr_id'], (int) $form->form_id, $data);

        return redirect()->route('emr.dynamic.index', ['form_name' => $form->slug, 'registrasi_detail_id' => $params['registrasi_detail_id']])
            ->with('success', 'Data '.$form->nama_form.' berhasil diperbarui.');
    }

    private function genericDestroy($form, array $params): RedirectResponse
    {
        EmrHelper::delete((int) $params['emr_id']);

        return redirect()->route('emr.dynamic.index', ['form_name' => $form->slug, 'registrasi_detail_id' => $params['registrasi_detail_id']])
            ->with('success', 'Data '.$form->nama_form.' berhasil dihapus.');
    }
}
