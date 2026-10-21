# Instalasi Manual (tanpa Docker) — SIMRS NusaMedika

Stack yang dipasang manual di host: **Apache 2.4 + PHP 8.5 FPM (+ Redis opsional)**,
dengan **database eksternal** (MySQL/PostgreSQL di luar mesin ini, atau server lain).

> Cara ini dipakai kalau environment **tidak boleh memakai Docker** (mis. server rumah sakit
> dengan kebijakan tertentu, atau development di laptop tanpa Docker Desktop).
> Kalau Docker boleh dipakai, lebih nyaman memakai **§7 README utama** (atau `deploy/README.md`).

> **Port 8081 sama dengan stack Docker.** Jalankan **salah satu** saja:
> `docker compose stop web` bila ingin memakai instalasi manual.

---

## 0. Ringkasan

| Komponen | Versi | Sumber |
| :--- | :--- | :--- |
| Apache | 2.4 | repo Ubuntu |
| PHP | 8.5 (FPM + CLI) | repo Ubuntu (PPA `ondrej/php` belum rilis untuk Ubuntu 26.04) |
| Redis | 8.0 | repo Ubuntu (**opsional**) |
| Composer | 2.x | getcomposer.org |
| Node.js | 22+ | sudah ada di host / `apt install nodejs npm` |
| Database | MySQL 8 / PostgreSQL | **server eksternal** (bukan di host ini) |

Variabel yang dipakai di seluruh panduan ini (sesuaikan bila berbeda):

```bash
export APP_DIR=/home/dodhykurniawan/Documents/NusaMedika/NusaMedika
export PHP_VER=8.5
export PORT=8081
export DEPLOY_USER=dodhykurniawan
```

---

## 1. Install paket sistem

```bash
sudo apt-get update
sudo apt-get install -y --no-install-recommends \
  apache2 curl ca-certificates unzip git \
  redis-server redis-tools \
  php${PHP_VER}-fpm php${PHP_VER}-cli php${PHP_VER}-common \
  php${PHP_VER}-mysql php${PHP_VER}-pgsql php${PHP_VER}-mbstring \
  php${PHP_VER}-xml php${PHP_VER}-curl php${PHP_VER}-zip \
  php${PHP_VER}-bcmath php${PHP_VER}-intl php${PHP_VER}-gd php${PHP_VER}-redis
```

Cek hasilnya:

```bash
php -v
php -m | grep -E "pdo_mysql|pdo_pgsql|mbstring|redis|opcache|gd|intl"
```

### Composer

```bash
if ! command -v composer >/dev/null; then
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm -f /tmp/composer-setup.php
fi
composer --version
```

---

## 2. Konfigurasi Apache

```bash
# Modul yang dibutuhkan (rewrite utk Laravel, proxy_fcgi utk PHP-FPM)
sudo a2enmod rewrite proxy proxy_fcgi headers

# Port HTTP (dilarang di ports.conf, bukan di vhost)
sudo grep -qE "^Listen ${PORT}$" /etc/apache2/ports.conf || \
  echo "Listen ${PORT}" | sudo tee -a /etc/apache2/ports.conf

# VirtualHost
sudo cp deploy/apache/nusamedika-manual.conf /etc/apache2/sites-available/nusamedika.conf
sudo sed -i "s#__APP_DIR__#${APP_DIR}#g; s#__PHP_VER__#${PHP_VER}#g; s#__PORT__#${PORT}#g" \
  /etc/apache2/sites-available/nusamedika.conf

sudo a2ensite nusamedika
sudo apache2ctl configtest     # harus: Syntax OK
```

---

## 3. Konfigurasi PHP-FPM

```bash
# Backup pool bawaan, lalu pakai pool bawaan proyek
sudo cp /etc/php/${PHP_VER}/fpm/pool.d/www.conf /etc/php/${PHP_VER}/fpm/pool.d/www.conf.bak
sudo cp deploy/php/php-fpm-pool-manual.conf /etc/php/${PHP_VER}/fpm/pool.d/www.conf

# Tuning php.ini (FPM + CLI)
sudo cp deploy/php/php.ini /etc/php/${PHP_VER}/fpm/conf.d/99-nusamedika.ini
sudo cp deploy/php/php.ini /etc/php/${PHP_VER}/cli/conf.d/99-nusamedika.ini

# File log FPM (dibuat paket, tapi pastikan ada & writable)
sudo touch /var/log/php${PHP_VER}-fpm.log
sudo chown www-data:adm /var/log/php${PHP_VER}-fpm.log
sudo chmod 640 /var/log/php${PHP_VER}-fpm.log

sudo systemctl enable php${PHP_VER}-fpm
sudo systemctl restart php${PHP_VER}-fpm
```

Cek pool:

```bash
sudo systemctl status php${PHP_VER}-fpm --no-pager
ls -l /run/php/php${PHP_VER}-fpm.sock
```

---

## 4. Permission folder aplikasi

Apache & PHP-FPM berjalan sebagai `www-data`, sedangkan project dimiliki user deploy
(`${DEPLOY_USER}`). Dua hal wajib:

1. `www-data` harus bisa **menelusuri** path sampai `public/` (bit execute, tanpa list).
2. `www-data` harus bisa **menulis** `storage/` dan `bootstrap/cache/`.

```bash
# 1) Izin traverse sepanjang path (aman: hanya execute, tidak bisa dibaca/dilisting)
p="${APP_DIR}"
while [ "$p" != "/" ] && [ -n "$p" ]; do
  sudo chmod o+x "$p" 2>/dev/null || true
  p="$(dirname "$p")"
done

# 2) storage & bootstrap/cache ditulis bersama user deploy + www-data
sudo chgrp -R www-data "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
sudo chmod -R g+rwX "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
sudo find "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" -type d -exec chmod g+s {} +
```

> Kalau `storage/` tetap tidak bisa ditulis (mis. `failed to open stream: Permission denied`),
> jalankan sekali: `sudo chown -R ${DEPLOY_USER}:www-data ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache`.

---

## 5. Konfigurasi `.env`

```bash
cd "${APP_DIR}"
cp .env.example .env     # lewati bila .env sudah ada
```

Isi blok **database eksternal** (tidak ada database di host ini):

```dotenv
DB_CONNECTION=mysql
DB_HOST=153.92.15.19
DB_PORT=3306
DB_DATABASE=u887691424_nusamedka
DB_USERNAME=...
DB_PASSWORD=...
```

Pilih mode cache/session/queue:

```dotenv
# TANPA Redis (default — paling aman)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# DENGAN Redis (lihat langkah 9)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1      # PENTING: 127.0.0.1 (bukan "redis" seperti di Docker)
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Lain-lain yang perlu disesuaikan:

```dotenv
APP_URL=http://localhost:8081
```

Lalu bersihkan cache konfigurasi:

```bash
php artisan config:clear
php artisan cache:clear
```

> **Jangan** `php artisan config:cache` — konstanta SIMRS (`FORM_ID_*`, `OBJEK_ID_*`,
> `JENIS_RAWAT_*`) dibaca via `env()` saat runtime.

---

## 6. Install dependency & build aset

```bash
cd "${APP_DIR}"

# Dependency PHP (jalankan sebagai user deploy, BUKAN root)
composer install --no-interaction --prefer-dist --optimize-autoloader

# Aset frontend
npm install
npm run build
```

> **Gotcha:** `node_modules/` dan `public/build` bawaan Docker biasanya **root-owned**,
> sehingga `npm install` gagal dengan `EACCES`. Perbaiki dulu:
>
> ```bash
> sudo chown -R ${DEPLOY_USER}:${DEPLOY_USER} node_modules public/build
> ```

Symlink storage publik:

```bash
php artisan storage:link
```

---

## 7. Jalankan service

```bash
sudo systemctl enable apache2
sudo systemctl enable php${PHP_VER}-fpm
sudo systemctl restart apache2

# Redis hanya perlu kalau .env memakai driver redis
sudo systemctl enable --now redis-server
```

Cek:

```bash
sudo systemctl status apache2 php${PHP_VER}-fpm redis-server --no-pager
```

---

## 8. Queue worker & scheduler (opsional)

Aplikasi tidak punya job antrean, jadi worker boleh dilewati. Bila memakai Redis untuk
antrean, aktifkan unit systemd-nya:

```bash
sudo cp deploy/systemd/nusamedika-queue.service /etc/systemd/system/
sudo cp deploy/systemd/nusamedika-scheduler.service /etc/systemd/system/
sudo cp deploy/systemd/nusamedika-scheduler.timer /etc/systemd/system/

sudo sed -i "s#__APP_DIR__#${APP_DIR}#g; s#__USER__#${DEPLOY_USER}#g; s#__GROUP__#${DEPLOY_USER}#g" \
  /etc/systemd/system/nusamedika-queue.service \
  /etc/systemd/system/nusamedika-scheduler.service \
  /etc/systemd/system/nusamedika-scheduler.timer

sudo systemctl daemon-reload
sudo systemctl enable --now nusamedika-queue.service
sudo systemctl enable --now nusamedika-scheduler.timer
```

Alternatif tanpa systemd (cron):

```bash
(crontab -l 2>/dev/null; echo "* * * * * cd ${APP_DIR} && /usr/bin/php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

---

## 9. Mengaktifkan / mematikan Redis

Redis **opsional**. Yang menentukan dipakai/tidaknya Redis adalah `.env`.

**Tanpa Redis** (default) — tidak perlu service Redis sama sekali:

```dotenv
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

```bash
sudo systemctl stop redis-server
```

**Dengan Redis**:

```bash
# 1) konfigurasi
sudo cat deploy/redis/redis-manual.conf >> /etc/redis/redis.conf
sudo systemctl restart redis-server
redis-cli ping          # harus: PONG

# 2) .env
#    CACHE_STORE=redis
#    SESSION_DRIVER=redis
#    QUEUE_CONNECTION=redis
#    REDIS_HOST=127.0.0.1
```

```bash
php artisan config:clear && php artisan cache:clear
```

Penggunaan database Redis:

| Store | Connection | Redis DB |
| :--- | :--- | :--- |
| Cache | `cache` | 1 |
| Session | `default` | 0 |
| Queue | `default` | 0 |

Bersihkan:

```bash
redis-cli -n 0 FLUSHDB   # session + queue
redis-cli -n 1 FLUSHDB   # cache
```

---

## 10. Verifikasi

```bash
# 1) Apache → PHP-FPM → Laravel
curl -I http://localhost:8081/login          # expect: HTTP/1.1 200 OK

# 2) Koneksi database eksternal
php artisan migrate:status                  # harus tampil daftar migrasi

# 3) Redis (bila aktif)
redis-cli ping                              # PONG

# 4) Pool FPM jalan sebagai www-data
ps -u www-data -f php-fpm | head

# 5) Log
sudo tail -f /var/log/apache2/nusamedika-error.log
tail -f storage/logs/laravel.log
```

Login memakai kredensial seeder: `superadmin` / `admin`.

---

## 11. Troubleshooting

| Masalah | Penyebab & Solusi |
| :--- | :--- |
| `403 Forbidden` | `www-data` tidak bisa menelusuri path. Ulangi langkah 4 (chmod o+x pada rantai path). |
| `500` + `Permission denied` di `storage/` | `storage/` & `bootstrap/cache/` belum di-grup-kan ke `www-data`. Ulangi langkah 4. |
| `502 Bad Gateway` | PHP-FPM mati: `sudo systemctl status php${PHP_VER}-fpm`. |
| Halaman menampilkan source PHP | `mod_proxy_fcgi` belum aktif: `sudo a2enmod proxy_fcgi && sudo systemctl reload apache2`. |
| `Address already in use` | Port 8081 dipakai container Docker: `docker compose stop web` (atau pilih port lain). |
| `Class "Redis" not found` | Ekstensi phpredis belum terpasang: `sudo apt-get install php${PHP_VER}-redis`. |
| `Connection refused` ke Redis | Service Redis mati atau `REDIS_HOST` salah. Di mode manual host Redis = `127.0.0.1` (bukan `redis`). |
| `composer install` "Permission denied" | `vendor/` root-owned (bawaan Docker): `sudo chown -R ${DEPLOY_USER}:${DEPLOY_USER} vendor storage bootstrap/cache`. |
| `npm install` `EACCES` | `node_modules/` root-owned: `sudo chown -R ${DEPLOY_USER}:${DEPLOY_USER} node_modules public/build`. |
| Asset tidak termuat | Jalankan `npm run build`; kalau memakai dev server, jalankan `npm run dev` (port 5173) atau hapus `public/hot`. |
| Perubahan `.env` tidak berpengaruh | `php artisan config:clear && php artisan cache:clear`, lalu `sudo systemctl restart php${PHP_VER}-fpm`. |
| Data jam meleset 8 jam | Server DB berjalan UTC sedangkan app `Asia/Makassar` (WITA). Tulis timestamp dari PHP (`now()`), jangan `NOW()` di SQL. |

---

## 12. Uninstall / kembali ke Docker

```bash
sudo systemctl disable --now nusamedika-queue.service nusamedika-scheduler.timer
sudo rm -f /etc/systemd/system/nusamedika-{queue.service,scheduler.service,scheduler.timer}
sudo systemctl daemon-reload

sudo a2dissite nusamedika
sudo rm -f /etc/apache2/sites-available/nusamedika.conf
sudo sed -i "/^Listen ${PORT}$/d" /etc/apache2/ports.conf
sudo systemctl reload apache2

# kembalikan ke Docker
docker compose up -d --build
```

---

## Ringkasan perintah harian

```bash
cd "${APP_DIR}"
php artisan <command>                      # artisan
composer install                           # update dependency
npm run build                              # build aset
sudo systemctl restart php${PHP_VER}-fpm   # restart setelah ubah .env
sudo tail -f /var/log/apache2/nusamedika-error.log
```
