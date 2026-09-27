<?php

namespace App\Helpers;

use App\Models\Notifikasi;
use App\Models\NotifikasiPenerima;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pusat operasi notifikasi: kirim (fan-out ke penerima) & tutup per user.
 *
 * Semua query MEMGAKAN filter status_batal (null/0 = aktif) sesuai konvensi repo.
 */
class NotifikasiHelper
{
    /**
     * Kirim notifikasi ke penerima berdasarkan tipe target.
     *
     * @param  array{judul:string, pesan:string, tipe_target:string, target_id:?int, prioritas?:string, penerima_ids?:array<int>}  $data
     * @return int jumlah penerima yang dibuat
     */
    public static function kirim(array $data, ?int $pengirimId = null): int
    {
        $notifikasi = new Notifikasi;
        $notifikasi->judul = $data['judul'];
        $notifikasi->pesan = $data['pesan'];
        $notifikasi->tipe_target = $data['tipe_target'];
        $notifikasi->target_id = $data['target_id'] ?? null;
        $notifikasi->prioritas = $data['prioritas'] ?? 'INFO';
        $notifikasi->input_time = now();
        $notifikasi->input_user_id = $pengirimId;
        $notifikasi->status_batal = 0;
        $notifikasi->save();

        // penerima_ids dipakai ketika satu notifikasi menyasar beberapa user sekaligus;
        // bila kosong, penerima diturunkan dari tipe_target.
        $userIds = ! empty($data['penerima_ids'])
            ? array_values(array_unique(array_map('intval', $data['penerima_ids'])))
            : self::penerimaIds($data['tipe_target'], $data['target_id'] ?? null);

        $waktu = now();
        $rows = [];
        foreach ($userIds as $userId) {
            $rows[] = [
                'notifikasi_id' => $notifikasi->notifikasi_id,
                'user_id' => $userId,
                'is_read' => 0,
                'input_time' => $waktu,
                'input_user_id' => $pengirimId,
                'status_batal' => 0,
            ];
        }

        if ($rows) {
            DB::table('notifikasi_penerima')->insert($rows);
        }

        return count($userIds);
    }

    /**
     * Tentukan user_id penerima dari tipe target.
     *
     * @return array<int>
     */
    public static function penerimaIds(string $tipeTarget, ?int $targetId): array
    {
        $aktif = User::where(function ($q) {
            $q->whereNull('status_batal')->orWhere('status_batal', 0);
        });

        if ($tipeTarget === Notifikasi::TIPE_USER) {
            return $aktif->whereIn('user_id', (array) $targetId)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        }

        if ($tipeTarget === Notifikasi::TIPE_BAGIAN) {
            return $aktif->whereIn('pegawai_id', function ($query) use ($targetId) {
                $query->select('pegawai_id')
                    ->from('pegawai')
                    ->where('bagian_id', $targetId)
                    ->where(function ($q) {
                        $q->whereNull('status_batal')->orWhere('status_batal', 0);
                    });
            })->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        }

        // SEMUA
        return $aktif->pluck('user_id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Notifikasi yang belum ditutup oleh user tersebut (untuk lonceng navbar).
     */
    public static function belumDibaca(int $userId)
    {
        return NotifikasiPenerima::belumDibaca()
            ->where('user_id', $userId)
            ->with(['notifikasi' => fn ($q) => $q->aktif()])
            ->get()
            ->filter(fn ($p) => $p->notifikasi !== null)
            ->sortByDesc(fn ($p) => $p->notifikasi->input_time)
            ->values();
    }

    public static function jumlahBelumDibaca(int $userId): int
    {
        return DB::table('notifikasi_penerima as np')
            ->join('notifikasi as n', 'n.notifikasi_id', '=', 'np.notifikasi_id')
            ->where('np.user_id', $userId)
            ->where('np.is_read', 0)
            ->where(function ($q) {
                $q->whereNull('np.status_batal')->orWhere('np.status_batal', 0);
            })
            ->where(function ($q) {
                $q->whereNull('n.status_batal')->orWhere('n.status_batal', 0);
            })
            ->count();
    }

    /**
     * Tutup satu notifikasi milik user tersebut.
     * Mengembalikan false bila notifikasi ini memang bukan untuk user tersebut.
     */
    public static function tutup(int $notifikasiId, int $userId): bool
    {
        $penerima = NotifikasiPenerima::aktif()
            ->where('notifikasi_id', $notifikasiId)
            ->where('user_id', $userId)
            ->first();

        if (! $penerima) {
            return false;
        }

        $penerima->is_read = 1;
        $penerima->dibaca_time = now();
        $penerima->mod_time = now();
        $penerima->mod_user_id = $userId;
        $penerima->save();

        return true;
    }

    /** Tutup semua notifikasi milik user tersebut. */
    public static function tutupSemua(int $userId): int
    {
        $affected = NotifikasiPenerima::belumDibaca()
            ->where('user_id', $userId)
            ->update([
                'is_read' => 1,
                'dibaca_time' => now(),
                'mod_time' => now(),
                'mod_user_id' => $userId,
            ]);

        return (int) $affected;
    }
}
