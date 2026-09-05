<?php

namespace App\Helpers;

use App\Models\RegistrasiDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pusat seluruh operasi EMR (form, objek, mapping form->objek, serta baca/tulis
 * tabel emr & emr_detail). Menggantikan pemakaian env('FORM_ID_*') / env('OBJEK_ID_*')
 * yang sebelumnya tersebar di controller & blade.
 */
class EmrHelper
{
    // ======================== FORM ========================

    /**
     * Ambil form berdasarkan slug (nama folder/file EMR = form_name di URL).
     */
    public static function formBySlug(?string $slug): ?object
    {
        if (! $slug) {
            return null;
        }

        return DB::table('form')
            ->where('slug', $slug)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();
    }

    public static function formById(int $formId): ?object
    {
        return DB::table('form')
            ->where('form_id', $formId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();
    }

    public static function formIdBySlug(?string $slug): ?int
    {
        $form = static::formBySlug($slug);

        return $form ? (int) $form->form_id : null;
    }

    public static function forms()
    {
        return DB::table('form')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('form_id')
            ->get();
    }

    // ======================== OBJEK & MAPPING ========================

    public static function objeks()
    {
        return DB::table('objek')
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('objek_id')
            ->get();
    }

    /**
     * Mapping form -> objek yang AKTIF (pengganti env('OBJEK_ID_*')).
     * Kembalian: array of stdClass { objek_form_control_id, form_id, objek_id, variabel, nama_objek }.
     */
    public static function objekMap(int $formId): array
    {
        return DB::table('objek_form_control as ofc')
            ->leftJoin('objek as o', function ($join) {
                $join->on('o.objek_id', '=', 'ofc.objek_id')
                    ->where(function ($q) {
                        $q->whereNull('o.status_batal')->orWhere('o.status_batal', 0);
                    });
            })
            ->where('ofc.form_id', $formId)
            ->where(function ($q) {
                $q->whereNull('ofc.status_batal')->orWhere('ofc.status_batal', 0);
            })
            ->select('ofc.*', 'o.nama_objek')
            ->orderBy('ofc.objek_form_control_id')
            ->get()
            ->all();
    }

    /**
     * Daftar variabel (nama field) milik sebuah form.
     */
    public static function objekVariabels(int $formId): array
    {
        return array_values(array_unique(array_filter(array_column(static::objekMap($formId), 'variabel'))));
    }

    /**
     * Ambil objek_id (jika tercatat di mapping form) untuk sebuah variabel/field.
     */
    public static function objekId(int $formId, string $variabel): ?int
    {
        $row = DB::table('objek_form_control')
            ->where('form_id', $formId)
            ->where('variabel', $variabel)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();

        return $row && $row->objek_id ? (int) $row->objek_id : null;
    }

    /**
     * Daftar objek_id (unik) untuk sekumpulan variabel — dipakai query whereIn('objek_id').
     */
    public static function objekIdsByVariabels(int $formId, array $variabels): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($v) => static::objekId($formId, $v),
            $variabels
        ))));
    }

    // ======================== BACA EMR ========================

    /**
     * Riwayat emr sebuah pasien/registrasi (paginate, dilengkapi nama pegawai).
     */
    public static function emrList(int $formId, int $registrasiId, int $perPage = 5)
    {
        return DB::table('emr')
            ->leftJoin('pegawai', function ($join) {
                $join->on('emr.pegawai_id', '=', 'pegawai.pegawai_id')
                    ->where(function ($q) {
                        $q->whereNull('pegawai.status_batal')->orWhere('pegawai.status_batal', 0);
                    });
            })
            ->where('emr.registrasi_id', $registrasiId)
            ->where('emr.form_id', $formId)
            ->where(function ($q) {
                $q->whereNull('emr.status_batal')->orWhere('emr.status_batal', 0);
            })
            ->select('emr.*', 'pegawai.nama_pegawai')
            ->orderBy('emr.tgl_jam', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public static function emrById(int $emrId): ?object
    {
        return DB::table('emr')
            ->where('emr_id', $emrId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();
    }

    /**
     * detail aktif sebuah emr, keyed by variabel (kompatibel data lama objek_id null).
     */
    public static function emrDetailByVariabel(int $emrId): array
    {
        return DB::table('emr_detail')
            ->where('emr_id', $emrId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('value', 'variabel')
            ->all();
    }

    /**
     * detail aktif sebuah emr, keyed by objek_id.
     */
    public static function emrDetailByObjek(int $emrId): array
    {
        return DB::table('emr_detail')
            ->where('emr_id', $emrId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('value', 'objek_id')
            ->all();
    }

    /**
     * emr terbaru (aktif) sebuah form untuk sebuah registrasi_detail.
     */
    public static function latestEmr(int $formId, int $registrasiDetailId): ?object
    {
        return DB::table('emr')
            ->where('form_id', $formId)
            ->where('registrasi_detail_id', $registrasiDetailId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->orderBy('tgl_jam', 'desc')
            ->first();
    }

    /**
     * Nilai beberapa variabel dari emr terbaru sebuah registrasi_detail
     * (untuk ringkasan riwayat lintas form mis. vital sign di SOAP).
     * keyed by variabel.
     */
    public static function latestValuesByVariabel(int $formId, int $registrasiDetailId, array $variabels): array
    {
        $emr = static::latestEmr($formId, $registrasiDetailId);
        if (! $emr) {
            return [];
        }

        return DB::table('emr_detail')
            ->where('emr_id', $emr->emr_id)
            ->whereIn('variabel', $variabels)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('value', 'variabel')
            ->all();
    }

    // ======================== TULIS EMR ========================

    /**
     * Simpan emr baru dari data form (keys = variabel). Hanya field yang
     * tercatat di mapping form yang ikut disimpan; objek_id diambil dari mapping.
     * Mengembalikan emr_id.
     */
    public static function insert(int $formId, array $data, int $registrasiDetailId): int
    {
        $registrasiDetail = RegistrasiDetail::findOrFail($registrasiDetailId);
        $user = Auth::user();
        $now = now();

        return DB::transaction(function () use ($formId, $data, $registrasiDetail, $registrasiDetailId, $user, $now) {
            $emrId = DB::table('emr')->insertGetId([
                'form_id' => $formId,
                'pegawai_id' => $user->pegawai_id ?? null,
                'tgl_jam' => $now,
                'registrasi_detail_id' => $registrasiDetailId,
                'pasien_id' => $registrasiDetail->registrasi->pasien_id,
                'registrasi_id' => $registrasiDetail->registrasi_id,
                'input_time' => $now,
                'input_user_id' => $user->user_id ?? null,
            ], 'emr_id');

            static::storeDetails($emrId, $formId, $data, $now);

            return $emrId;
        });
    }

    public static function update(int $emrId, int $formId, array $data): void
    {
        $emr = static::emrById($emrId);
        if (! $emr) {
            abort(404);
        }

        $user = Auth::user();
        $now = now();

        DB::transaction(function () use ($emrId, $formId, $data, $now, $user) {
            DB::table('emr')
                ->where('emr_id', $emrId)
                ->update([
                    'mod_time' => $now,
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            // Soft-delete detail lama, lalu re-insert yang baru
            DB::table('emr_detail')
                ->where('emr_id', $emrId)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            static::storeDetails($emrId, $formId, $data, $now);
        });
    }

    public static function delete(int $emrId): void
    {
        $emr = static::emrById($emrId);
        if (! $emr) {
            abort(404);
        }

        $user = Auth::user();
        $now = now();

        DB::transaction(function () use ($emrId, $now, $user) {
            DB::table('emr')
                ->where('emr_id', $emrId)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user->user_id ?? null,
                ]);

            DB::table('emr_detail')
                ->where('emr_id', $emrId)
                ->update([
                    'status_batal' => 1,
                    'mod_time' => $now,
                    'mod_user_id' => $user->user_id ?? null,
                ]);
        });
    }

    private static function storeDetails(int $emrId, int $formId, array $data, $now): void
    {
        $user = Auth::user();

        foreach ($data as $variabel => $value) {
            if (! is_string($variabel)) {
                continue;
            }

            DB::table('emr_detail')->insert([
                'emr_id' => $emrId,
                'objek_id' => static::objekId($formId, $variabel),
                'variabel' => $variabel,
                'value' => is_array($value) ? json_encode($value) : $value,
                'input_time' => $now,
                'input_user_id' => $user->user_id ?? null,
            ]);
        }
    }

    // ======================== UTILITAS ========================

    /**
     * Perbaiki data legacy: isi objek_id yang masih NULL sesuai mapping variabel
     * form (dulu tersimpan tanpa objek_id karena env('OBJEK_ID_*') kosong).
     * Idempotent.
     */
    public static function backfillObjekId(int $formId): int
    {
        $emrIds = DB::table('emr')
            ->where('form_id', $formId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->pluck('emr_id')
            ->all();

        $mapping = [];
        foreach (static::objekMap($formId) as $m) {
            if ($m->variabel && $m->objek_id) {
                $mapping[$m->variabel] = (int) $m->objek_id;
            }
        }

        if (! $emrIds || ! $mapping) {
            return 0;
        }

        $affected = 0;
        foreach ($mapping as $variabel => $objekId) {
            $affected += DB::table('emr_detail')
                ->whereIn('emr_id', $emrIds)
                ->where('variabel', $variabel)
                ->whereNull('objek_id')
                ->where(function ($q) {
                    $q->whereNull('status_batal')->orWhere('status_batal', 0);
                })
                ->update(['objek_id' => $objekId]);
        }

        return $affected;
    }
}
