<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    /** Target: semua user aktif. */
    public const TIPE_SEMUA = 'SEMUA';

    /** Target: user tertentu (lihat kolom target_id = users.user_id). */
    public const TIPE_USER = 'USER';

    /** Target: semua user yang pegawai-nya berada di satu bagian (target_id = bagian.bagian_id). */
    public const TIPE_BAGIAN = 'BAGIAN';

    public const PRIORITAS = ['INFO', 'PENTING', 'URGENT'];

    protected $table = 'notifikasi';

    protected $primaryKey = 'notifikasi_id';

    public $timestamps = false;

    protected $fillable = [
        'judul',
        'pesan',
        'tipe_target',
        'target_id',
        'prioritas',
        'input_time',
        'input_user_id',
        'mod_time',
        'mod_user_id',
        'status_batal',
    ];

    public function penerima()
    {
        return $this->hasMany(NotifikasiPenerima::class, 'notifikasi_id', 'notifikasi_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'input_user_id', 'user_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });
    }

    /** Label target untuk ditampilkan di daftar riwayat. */
    public function targetLabel(): string
    {
        return match ($this->tipe_target) {
            self::TIPE_USER => 'User: '.($this->penerima()->count() > 0
                ? $this->penerima()->with('user')->get()->pluck('user.nama_pegawai')->filter()->unique()->implode(', ')
                : 'user #'.$this->target_id),
            self::TIPE_BAGIAN => 'Bagian: '.($this->namaBagian() ?? 'bagian #'.$this->target_id),
            default => 'Semua User',
        };
    }

    private function namaBagian(): ?string
    {
        if (! $this->target_id) {
            return null;
        }

        return Bagian::aktif()->where('bagian_id', $this->target_id)->value('nama_bagian');
    }
}
