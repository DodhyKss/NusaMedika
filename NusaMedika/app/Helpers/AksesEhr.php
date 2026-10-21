<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AksesEhr
{
    /**
     * Profesi aktif user: dari session (fallback dashboard), lalu dari
     * pegawai terautentikasi, terakhir default dokter (1).
     */
    public static function profesiId(): int
    {
        $fromSession = (int) session('profesi_id', 0);
        if ($fromSession > 0) {
            return $fromSession;
        }

        $user = Auth::user();
        if ($user && $user->pegawai_id) {
            $profesiId = (int) DB::table('pegawai')
                ->where('pegawai_id', $user->pegawai_id)
                ->value('profesi_id');

            if ($profesiId > 0) {
                return $profesiId;
            }
        }

        return 1;
    }

    /**
     * Ambil flag akses CRUD untuk sebuah form berdasarkan profesi aktif
     * (dari session). Hanya baris akses aktif (status_batal != 1 OR NULL).
     */
    public static function flags(int $formId): array
    {
        $row = DB::table('akses_ehr')
            ->where('profesi_id', static::profesiId())
            ->where('form_id', $formId)
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first();

        return [
            'create' => $row && (int) $row->akses_create === 1,
            'read' => $row && (int) $row->akses_read === 1,
            'update' => $row && (int) $row->akses_update === 1,
            'delete' => $row && (int) $row->akses_delete === 1,
        ];
    }

    public static function can(int $formId, string $action): bool
    {
        $flags = static::flags($formId);

        return $flags[$action] ?? false;
    }
}
