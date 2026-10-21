import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import fs from 'node:fs';
import os from 'node:os';
import dgram from 'node:dgram';

/**
 * Deteksi IP LAN komputer ini secara otomatis.
 *
 * Dipakai untuk mengisi `public/hot` supaya dev server Vite bisa diakses
 * dari device lain (HP / laptop lain) melalui IP, tanpa harus manual
 * mengganti konfigurasi setiap kali alamat IP komputer berubah.
 */
function fallbackIp() {
    const candidates = [];

    for (const addresses of Object.values(os.networkInterfaces())) {
        for (const address of addresses ?? []) {
            const family = typeof address.family === 'string' ? address.family : `IPv${address.family}`;

            if (family !== 'IPv4' || address.internal) {
                continue;
            }

            // Lewati bridge Docker (172.17.0.0/16 s.d. 172.31.0.0/16).
            if (/^172\.(1[7-9]|2\d|3[01])\./.test(address.address)) {
                continue;
            }

            candidates.push(address.address);
        }
    }

    return candidates[0] ?? null;
}

function detectLanIp() {
    return new Promise((resolve) => {
        // "connect" UDP tidak mengirim paket, hanya menentukan route default.
        const socket = dgram.createSocket('udp4');
        let done = false;

        const finish = (ip) => {
            if (done) {
                return;
            }

            done = true;

            try {
                socket.close();
            } catch {
                // socket belum ter-bind, abaikan.
            }

            resolve(ip);
        };

        socket.on('error', () => finish(fallbackIp()));
        socket.connect(53, '8.8.8.8', () => finish(socket.address().address));

        setTimeout(() => finish(fallbackIp()), 1000).unref?.();
    });
}

/**
 * Tulis ulang `public/hot` dengan IP LAN yang terdeteksi.
 *
 * Plugin laravel menulis `public/hot` saat dev server start (berisi host
 * statis). File ini ditimpa agar isinya memakai IP komputer yang sedang
 * dipakai, sehingga asset tetap bisa dimuat dari device lain.
 */
function hotFileWithLanIp(devHost) {
    return {
        name: 'nusamedika:hot-file-lan-ip',
        apply: 'serve',
        configureServer(server) {
            server.httpServer?.once('listening', () => {
                // Beri waktu plugin laravel menulis file lebih dulu.
                setTimeout(() => {
                    const hotFile = 'public/hot';

                    try {
                        fs.writeFileSync(hotFile, `http://${devHost}:${server.config.server.port}`);
                    } catch {
                        // Abaikan bila file tidak bisa ditulis (mis. read-only).
                    }
                }, 100);
            });
        },
    };
}

export default defineConfig(async () => {
    const devHost = process.env.DEV_HOST || (await detectLanIp()) || 'localhost';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
            hotFileWithLanIp(devHost),
        ],
        server: {
            // Dengarkan di semua interface supaya device lain bisa mengakses.
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            // Origin dikosongkan supaya Vite menghasilkan URL aset relatif
            // (mis. /node_modules/.../fa-solid-900.woff2), bukan absolut
            // `http://0.0.0.0:5173/...`. URL relatif ikut host request, jadi
            // webfont (ikon Font Awesome) tetap termuat dari device lain.
            origin: '',
            // Host HMR sengaja dibiarkan kosong: client HMR memakai host dari
            // URL @vite/client, sehingga otomatis mengikuti IP/host yang dipakai
            // device untuk membuka aplikasi.
            cors: {
                // Dev server boleh diakses dari origin mana pun (IP LAN, host
                // lain di jaringan lokal) agar module script tidak diblokir CORS.
                origin: true,
            },
            // Terima Host header apa pun (akses lewat hostname, bukan hanya IP).
            allowedHosts: true,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
