# =============================================================================
#  NusaMedika - Image Docker
#   target `fpm`     : PHP 8.3 FPM (Aplikasi + queue worker)
#   target `apache`  : Apache 2.4 + mod_proxy_fcgi (web server)
#
#  Catatan:
#   - Database TIDAK ada di dalam Docker. Koneksi MySQL/PostgreSQL eksternal
#     hanya dibaca dari file .env (DB_HOST, DB_PORT, ...).
#   - Redis adalah OPSIONAL (docker compose --profile redis up -d).
#   - Container `app` dijalankan sebagai uid 1000 agar file yang dibuat dari
#     dalam container (artisan, cache, log) tetap milik user host.
# =============================================================================

# ---------- Base: PHP FPM + Apache2 + ekstensi + Composer ----------
FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    apache2 \
    curl \
    git \
    unzip \
    tzdata \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Ekstensi PHP (phpredis dipakai hanya bila driver redis diaktifkan di .env)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql mysqlnd \
    pgsql pdo_pgsql \
    mbstring exif pcntl bcmath gd zip intl opcache \
    redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# =============================================================================
#  Target FPM — aplikasi Laravel & queue worker
# =============================================================================
FROM base AS fpm

COPY deploy/php/php.ini /usr/local/etc/php/conf.d/99-nusamedika.ini

# PHP-FPM harus bisa jalan sebagai user non-root (uid 1000):
#   - pid file ditulis ke /tmp
#   - error log diarahkan ke stderr container
RUN sed -i \
        -e 's#^pid = .*#pid = /tmp/php-fpm.pid#' \
        -e 's#^;error_log = .*#error_log = /proc/self/fd/2#' \
        -e 's#^error_log = .*#error_log = /proc/self/fd/2#' \
        /usr/local/etc/php-fpm.conf \
    && sed -i \
        -e 's#^listen = .*#listen = 0.0.0.0:9000#' \
        -e 's#^;?listen\.backlog = .*#listen.backlog = 511#' \
        -e 's#^pm.max_children = .*#pm.max_children = 30#' \
        -e 's#^pm.start_servers = .*#pm.start_servers = 5#' \
        -e 's#^pm.min_spare_servers = .*#pm.min_spare_servers = 3#' \
        -e 's#^pm.max_spare_servers = .*#pm.max_spare_servers = 8#' \
        -e 's#^pm.max_requests = .*#pm.max_requests = 500#' \
        -e 's#^;clear_env = no#clear_env = no#' \
        /usr/local/etc/php-fpm.d/www.conf

# =============================================================================
#  Target Apache — web server, meneruskan .php ke PHP-FPM service `app`
# =============================================================================
FROM base AS apache

COPY deploy/apache/nusamedika-docker.conf /etc/apache2/sites-available/nusamedika.conf

RUN a2enmod rewrite proxy proxy_fcgi proxy_http headers \
    && a2dissite 000-default \
    && a2ensite nusamedika \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && ln -sf /dev/stdout /var/log/apache2/access.log \
    && ln -sf /dev/stderr /var/log/apache2/error.log \
    && rm -f /var/log/apache2/*.log \
    && mkdir -p /var/run/apache2 /var/lock/apache2 \
    && chmod 755 /var/run/apache2 /var/lock/apache2

EXPOSE 80

# /etc/apache2/envvars mendefinisikan APACHE_RUN_USER/GROUP/PID_FILE/RUN_DIR
CMD ["/bin/sh", "-c", ". /etc/apache2/envvars && exec apache2 -DFOREGROUND"]
