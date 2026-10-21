# Deployment SIMRS NusaMedika — Docker (Apache + PHP-FPM + Redis)

> Instalasi **tanpa Docker** (langsung di host: Apache + PHP-FPM + Redis opsional) ada di
> [`deploy/README-MANUAL.md`](README-MANUAL.md).

| Service | Peran | Port host |
|---|---|---|
| `web` | Apache 2.4 (`mod_rewrite` + `mod_proxy_fcgi`) | **8081** → container 80 |
| `app` | PHP 8.3 FPM (Laravel + `artisan`), uid 1000 | internal `app:9000` |
| `redis` | cache / session / queue — **opsional** (profile `redis`) | internal `redis:6379` |
| `queue` | `artisan queue:work` — ikut profile `redis` | — |
| `vite` | dev server Vite (node) | host network, 5173 |

> **Database tidak ada di Docker.** Koneksi MySQL/PostgreSQL **eksternal** dibaca
> sepenuhnya dari `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
> `DB_PASSWORD`). Tidak ada service `db` di `docker-compose.yml`.

---

## 1. Menjalankan

```bash
# Tanpa Redis (default): cache=file, session=file, queue=sync
docker compose up -d --build
docker compose ps

# Dengan Redis
docker compose --profile redis up -d --build
```

Akses aplikasi: <http://localhost:8081>

---

## 2. Redis (opsional)

Redis **tidak wajib**. Yang menentukan dipakai/tidaknya Redis adalah `.env`:

```dotenv
# TANPA Redis
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# DENGAN Redis (container harus dijalankan dengan --profile redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_CLIENT=phpredis
```

Penggunaan database Redis (dalam container yang sama, DB berbeda):

| Store | Connection | Redis DB |
|---|---|---|
| Cache | `cache` | 1 |
| Session | `default` | 0 |
| Queue | `default` | 0 |

Cek & bersihkan:

```bash
docker compose exec redis redis-cli ping
docker compose exec redis redis-cli -n 0 FLUSHDB   # session + queue
docker compose exec redis redis-cli -n 1 FLUSHDB   # cache
```

Driver `database` **tidak** bisa dipakai untuk cache/session/queue: tabel
`cache`, `sessions`, dan `jobs` tidak ada di migrasi project ini.

---

## 3. Database eksternal

Semua di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=153.92.15.19
DB_PORT=3306
DB_DATABASE=u887691424_nusamedka
DB_USERNAME=...
DB_PASSWORD=...
```

Ganti server DB? cukup ubah `.env`, lalu:

```bash
docker compose up -d --force-recreate app web
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

> **Jangan** `php artisan config:cache` — aplikasi membaca `env('FORM_ID_*')` /
> `env('OBJEK_ID_*')` saat runtime (lihat `AGENTS.md` § EMR dynamic forms).

Perintah database yang pernah dipakai (service `db` sudah dihapus):

```bash
# mysql client dari container app (tidak ada lagi service db)
docker compose exec app php artisan migrate:status
docker compose exec app php artisan migrate --force
```

---

## 4. Command harian

```bash
docker compose exec app php artisan <command>       # artisan
docker compose exec app composer install           # update dependency
docker compose exec app vendor/bin/pint             # format kode
docker compose exec vite npm run build              # build asset produksi
docker compose logs -f web                          # log Apache
docker compose logs -f app                          # log PHP-FPM
docker compose restart app web                      # restart setelah ubah .env
```

Semua command artisan tetap lewat container `app` (tidak ada PHP di host).

---

## 5. Catatan penting

- Container `app` berjalan sebagai **uid 1000** (user host) agar file yang dibuat
  dari dalam container (cache, log, `artisan`) bisa diedit dari host tanpa `sudo`.
- `web` (Apache) berjalan sebagai root di dalam container supaya bisa bind port 80;
  penulisan file aplikasi tetap terjadi di container `app` (uid 1000).
- **Timezone**: `config/app.php` = `Asia/Makassar` (UTC+8). Tulis timestamp dari
  PHP (`now()`), jangan `NOW()` di SQL — DB luar bisa berjalan di UTC.
- Kalau `public/hot` ada (vite dev aktif), asset diambil dari `http://localhost:5173`.
  Untuk production build: `docker compose exec vite npm run build` lalu hapus
  `public/hot`.
