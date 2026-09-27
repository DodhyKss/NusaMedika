<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ForumTopik extends Model
{
    /** Terbuka untuk seluruh user rumah sakit. */
    public const TIPE_PUBLIK = 'PUBLIK';

    /** Hanya untuk user dengan profesi yang sama. */
    public const TIPE_PROFESI = 'PROFESI';

    /** Hanya untuk user yang bekerja di bagian yang sama. */
    public const TIPE_BAGIAN = 'BAGIAN';

    protected $table = 'forum_topik';

    protected $primaryKey = 'topik_id';

    public $timestamps = false;

    protected $fillable = [
        'judul',
        'isi',
        'tipe',
        'target_id',
        'user_id',
        'terakhir_aktif_at',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function balasan()
    {
        return $this->hasMany(ForumBalasan::class, 'topik_id', 'topik_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    /**
     * Batasi topik yang boleh dilihat user tersebut.
     *
     * PUBLIK selalu boleh. PROFESI/BAGIAN hanya boleh bila target_id cocok dengan
     * profesi/bagian user. Kalau user tidak punya profesi/bagian, cabang tersebut
     * TIDAK ikut ditambahkan — kalau tidak, topik dengan target_id NULL ikut terbaca.
     */
    public function scopeTerlihatUntuk(Builder $query, int $userId): Builder
    {
        $pegawai = DB::table('pegawai')
            ->where('pegawai_id', DB::table('users')->where('user_id', $userId)->value('pegawai_id'))
            ->where(function ($q) {
                $q->whereNull('status_batal')->orWhere('status_batal', 0);
            })
            ->first(['profesi_id', 'bagian_id']);

        $profesiId = $pegawai->profesi_id ?? null;
        $bagianId = $pegawai->bagian_id ?? null;

        return $query->aktif()->where(function ($q) use ($profesiId, $bagianId) {
            $q->where('tipe', self::TIPE_PUBLIK);

            if ($profesiId) {
                $q->orWhere(function ($sub) use ($profesiId) {
                    $sub->where('tipe', self::TIPE_PROFESI)->where('target_id', $profesiId);
                });
            }

            if ($bagianId) {
                $q->orWhere(function ($sub) use ($bagianId) {
                    $sub->where('tipe', self::TIPE_BAGIAN)->where('target_id', $bagianId);
                });
            }
        });
    }

    /** Label cakupan untuk ditampilkan. */
    public function targetLabel(): string
    {
        if ($this->tipe === self::TIPE_PROFESI) {
            $nama = Profesi::where('profesi_id', $this->target_id)->value('nama_profesi');

            return 'Profesi: '.($nama ?: 'profesi #'.$this->target_id);
        }

        if ($this->tipe === self::TIPE_BAGIAN) {
            $nama = Bagian::aktif()->where('bagian_id', $this->target_id)->value('nama_bagian');

            return 'Bagian: '.($nama ?: 'bagian #'.$this->target_id);
        }

        return 'Semua User';
    }
}
