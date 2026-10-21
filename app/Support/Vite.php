<?php

namespace App\Support;

use Illuminate\Foundation\Vite as BaseVite;

/**
 * Vite dengan host dev-server yang mengikuti host request.
 *
 * Pada mode hot, Laravel membaca file `public/hot` untuk mengetahui address
 * dev server Vite. File tersebut berisi host statis (mis. `localhost`),
 * sehingga halaman yang dibuka dari device lain melalui IP tidak bisa
 * memuat CSS/JS (browser requesting ke localhost device itu sendiri).
 *
 * Class ini menulis ulang host pada `public/hot` menjadi host yang sedang
 * dipakai request, sehingga otomatis mengikuti IP yang diketik user
 * (localhost, IP LAN, hostname, dll) tanpa konfigurasi tambahan.
 */
class Vite extends BaseVite
{
    /**
     * Get the path to a given asset when running in HMR mode.
     */
    protected function hotAsset($asset)
    {
        $base = rtrim(file_get_contents($this->hotFile()), '/');

        return $this->devServerBase($base).'/'.$asset;
    }

    /**
     * Ganti host dev server dengan host request, port tetap dipertahankan.
     */
    protected function devServerBase(string $base): string
    {
        if (app()->runningInConsole()) {
            return $base;
        }

        $host = request()->getHost();

        if ($host === '') {
            return $base;
        }

        $parts = parse_url($base);

        if (! isset($parts['host'])) {
            return $base;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port;
    }
}
