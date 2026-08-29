# Panduan Menjalankan NusaMedika dengan Docker

Aplikasi NusaMedika (Laravel + Vite + PostgreSQL) telah siap dijalankan menggunakan **Docker** dan **Docker Compose**. Struktur yang disiapkan menggunakan arsitektur multi-stage build yang otomatis melakukan kompilasi aset frontend (Node 22) dan mengemas aplikasi backend (PHP 8.3 FPM + Nginx + Supervisor) dalam lingkungan Alpine yang ringan dan optimal.

---

## 🏗️ Struktur Konfigurasi Docker

- **`Dockerfile`**: Multi-stage build (build frontend dengan Vite -> build image PHP 8.3 Alpine + Nginx + Supervisor).
- **`docker-compose.yml`**: Orkestrasi 4 layanan utama:
  - **`app`**: Aplikasi utama (Nginx + PHP-FPM) berjalan di port **8000** (`http://localhost:8000`).
  - **`db`**: Database **PostgreSQL 16** berjalan di port **5432**.
  - **`redis`**: Layanan caching, session, dan queue redis berjalan di port **6379**.
  - **`queue`**: Background worker (`php artisan queue:work`) untuk memproses antrian.
- **`docker/`**: Konfigurasi Nginx, PHP (`custom.ini`), Supervisor, dan script `entrypoint.sh`.

---

## 🚀 Cara Menjalankan Aplikasi (Step-by-Step)

### 1. Menjalankan Container (Build & Up)
Pastikan Docker Desktop / Docker Engine sudah aktif, kemudian jalankan perintah berikut di terminal root proyek ini:

```bash
docker compose up --build -d
```

Perintah di atas akan:
1. Membangun aset frontend (`npm run build`).
2. Menginstall dependensi composer & ekstensi PHP yang dibutuhkan (`pdo_pgsql`, `pgsql`, `mbstring`, `gd`, `zip`, `bcmath`, `intl`, `opcache`, `redis`).
3. Menyiapkan `.env` secara otomatis dan menghasilkan `APP_KEY` jika belum ada.
4. Menjalankan container di background.

### 2. Mengecek Status Container
Untuk melihat apakah semua layanan sudah berjalan normal (`Up` / `healthy`):

```bash
docker compose ps
```

### 3. Menjalankan Migrasi Database (Opsional - TIDAK PERLU Jika Database Sudah Ada)
> **Bisa Langsung Akses!** Karena Anda menggunakan database PostgreSQL yang sudah ada di IP Lokal / Server (sesuai `DB_HOST` di `.env` Anda), Anda **TIDAK PERLU** melakukan migrasi apapun. Aplikasi akan langsung terhubung ke database tersebut.

*(Catatan: Langkah migrasi `docker compose exec app php artisan migrate` hanya perlu dilakukan jika Anda ingin membuat tabel dari nol pada database baru)*

### 4. Akses Aplikasi di Browser
Buka browser dan akses ke alamat berikut:
- **Web App**: [http://localhost:8000](http://localhost:8000)

---

## 🛠️ Perintah Berguna Lainnya (Cheat Sheet)

| Kebutuhan | Perintah Terminal |
| :--- | :--- |
| **Melihat Log Aplikasi (Realtime)** | `docker compose logs -f app` |
| **Masuk ke Shell Container Aplikasi** | `docker compose exec -it app bash` |
| **Menjalankan Artisan Command** | `docker compose exec app php artisan [command]` |
| **Membersihkan Cache Laravel** | `docker compose exec app php artisan optimize:clear` |
| **Stop & Matikan Semua Container** | `docker compose down` |
| **Stop & Hapus Data Volume DB** | `docker compose down -v` |

---

## 💻 Mode Pengembangan Lokal (Hot Reload / Live Dev)

Secara default, container memuat build statis yang cocok untuk deployment/testing. Jika Anda ingin melakukan pengeditan kode PHP lokal di Windows dan langsung tercermin di container:

1. Buka file `docker-compose.yml`.
2. Hapus komentar (`#`) pada bagian `volumes:` di dalam service `app`:
   ```yaml
   volumes:
     - ./:/var/www/html
     - app_storage:/var/www/html/storage
   ```
3. Restart container:
   ```bash
   docker compose down
   docker compose up -d
   ```
