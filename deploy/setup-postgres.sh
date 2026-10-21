#!/usr/bin/env bash
# =============================================================================
#  Setup PostgreSQL lokal untuk SIMRS NusaMedika  (jalankan sekali, sebagai root)
#
#    sudo bash deploy/setup-postgres.sh
#
#  Yang dilakukan:
#   1. Memastikan service postgresql aktif
#   2. Backup otomatis (pg_dump) bila database sudah berisi tabel
#   3. Membuat role + database aplikasi (idempotent)
#   4. Menambah aturan pg_hba untuk jaringan Docker (172.16.0.0/12)
#   5. Reload konfigurasi & menampilkan ringkasan
#
#  Variabel yang bisa dioverride:
#    PG_VERSION=18  DB_NAME=meditech  DB_USER=meditech  DB_PASS=meditech
#    DOCKER_SUBNET=172.16.0.0/12   BACKUP_DIR=/var/backups
# =============================================================================
set -euo pipefail

DB_NAME="${DB_NAME:-meditech}"
DB_USER="${DB_USER:-meditech}"
DB_PASS="${DB_PASS:-meditech}"
DOCKER_SUBNET="${DOCKER_SUBNET:-172.16.0.0/12}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups}"

log()  { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m[!] %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31m[ERROR] %s\033[0m\n' "$*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || die "jalankan dengan sudo: sudo bash deploy/setup-postgres.sh"

command -v psql >/dev/null || die "psql belum terpasang. Install postgresql + postgresql-client dulu."

# ---------------------------------------------------------------------------
log "1/5 Pastikan service PostgreSQL aktif"
PG_VERSION="$(pg_lsclusters -h 2>/dev/null | awk 'NR==1{print $1}')"
[ -n "$PG_VERSION" ] || die "tidak ada cluster PostgreSQL terdeteksi (pg_lsclusters kosong)"
PG_CLUSTER="$(pg_lsclusters -h | awk 'NR==1{print $1" "$2}')"
echo "Cluster terdeteksi: $PG_CLUSTER (versi $PG_VERSION)"
systemctl enable postgresql >/dev/null 2>&1 || true
systemctl restart postgresql
systemctl is-active --quiet postgresql || die "service postgresql tidak aktif"

psql_super() { sudo -u postgres psql -v ON_ERROR_STOP=1 "$@"; }

# ---------------------------------------------------------------------------
log "2/5 Backup database bila sudah berisi data"
DB_EXISTS="$(psql_super -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" || true)"
if [ "$DB_EXISTS" = "1" ]; then
    TABEL_COUNT="$(psql_super -d "${DB_NAME}" -tAc "SELECT count(*) FROM information_schema.tables WHERE table_schema='public'" || echo 0)"
    echo "Database '${DB_NAME}' ada, berisi ${TABEL_COUNT} tabel."
    if [ "${TABEL_COUNT}" -gt 0 ]; then
        mkdir -p "$BACKUP_DIR"
        BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}-$(date +%Y%m%d-%H%M%S).dump"
        sudo -u postgres pg_dump -Fc "${DB_NAME}" > "$BACKUP_FILE"
        echo "Backup: $BACKUP_FILE"
    fi
else
    echo "Database '${DB_NAME}' belum ada."
fi

# ---------------------------------------------------------------------------
log "3/5 Membuat role & database aplikasi"
ROLE_EXISTS="$(psql_super -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" || true)"
if [ "$ROLE_EXISTS" = "1" ]; then
    IS_SUPER="$(psql_super -tAc "SELECT rolsuper FROM pg_roles WHERE rolname='${DB_USER}'" || echo f)"
    if [ "$IS_SUPER" = "t" ]; then
        echo "Role '${DB_USER}' sudah ada (superuser) → password TIDAK diubah."
    else
        echo "Role '${DB_USER}' sudah ada → sinkronkan password."
        psql_super -c "ALTER ROLE ${DB_USER} WITH LOGIN PASSWORD '${DB_PASS}';" >/dev/null
    fi
else
    psql_super -c "CREATE ROLE ${DB_USER} WITH LOGIN PASSWORD '${DB_PASS}';" >/dev/null
    echo "Role '${DB_USER}' dibuat."
fi

if [ "$DB_EXISTS" = "1" ]; then
    psql_super -c "ALTER DATABASE ${DB_NAME} OWNER TO ${DB_USER};" >/dev/null
    echo "Database '${DB_NAME}' → owner ${DB_USER}."
else
    psql_super -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER} ENCODING 'UTF8';" >/dev/null
    echo "Database '${DB_NAME}' dibuat."
fi

# ---------------------------------------------------------------------------
log "4/5 Aturan pg_hba untuk jaringan Docker (${DOCKER_SUBNET})"
HBA_FILE="$(psql_super -tAc "SHOW hba_file" | tr -d '[:space:]')"
[ -n "$HBA_FILE" ] || die "gagal membaca lokasi pg_hba.conf"
echo "pg_hba.conf : $HBA_FILE"

RULE="host ${DB_NAME} ${DB_USER} ${DOCKER_SUBNET} scram-sha-256"
if grep -qF "${DB_NAME} ${DB_USER} ${DOCKER_SUBNET}" "$HBA_FILE"; then
    echo "Aturan untuk Docker sudah ada."
else
    # Sisipkan di baris pertama (pg_hba memakai aturan PERTAMA yang cocok)
    TMP_FILE="$(mktemp)"
    {
        echo "# NusaMedika: akses dari container Docker (ditambahkan $(date '+%Y-%m-%d %H:%M:%S'))"
        echo "${RULE}"
        echo ""
        cat "$HBA_FILE"
    } > "$TMP_FILE"
    cat "$TMP_FILE" > "$HBA_FILE"
    rm -f "$TMP_FILE"
    echo "Aturan ditambahkan: ${RULE}"
fi

psql_super -c "SELECT pg_reload_conf();" >/dev/null
echo "Konfigurasi di-reload."

# ---------------------------------------------------------------------------
log "5/5 Ringkasan & uji koneksi"
sudo -u postgres psql -c "SELECT version();" | head -3
echo "Database:"
sudo -u postgres psql -tAc "SELECT datname FROM pg_database WHERE datistemplate = false ORDER BY 1;"
echo "Tabel di '${DB_NAME}':"
psql_super -d "${DB_NAME}" -tAc "SELECT count(*) FROM information_schema.tables WHERE table_schema='public'"
echo "Migrasi yang sudah jalan:"
psql_super -d "${DB_NAME}" -tAc "SELECT count(*) FROM migrations" 2>/dev/null || echo "0 (tabel migrations belum ada)"

echo
echo "Uji koneksi dengan kredensial aplikasi:"
if PGPASSWORD="${DB_PASS}" psql -h 127.0.0.1 -U "${DB_USER}" -d "${DB_NAME}" -tAc "SELECT 'OK koneksi TCP 127.0.0.1'" 2>/dev/null; then
    echo
else
    warn "Gagal konek via TCP 127.0.0.1 — periksa aturan pg_hba untuk host lokal."
fi

cat <<EOF

==============================================================================
 SETUP POSTGRESQL SELESAI
==============================================================================
  DB_CONNECTION=pgsql
  DB_HOST=host.docker.internal      # dari container Docker (pakai extra_hosts host-gateway)
  DB_HOST=127.0.0.1                 # dari host / instalasi manual
  DB_PORT=5432
  DB_DATABASE=${DB_NAME}
  DB_USERNAME=${DB_USER}
  DB_PASSWORD=${DB_PASS}

  Langkah berikutnya (di proyek):
    docker compose up -d --force-recreate app web
    docker compose exec app php artisan migrate --force
    docker compose exec app php artisan db:seed --force
    docker compose exec app php artisan migrate:status
==============================================================================
EOF
