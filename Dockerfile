# Menggunakan image PHP CLI (bukan FPM) karena kita akan memakai artisan serve
FROM php:8.3-cli

# Install dependencies yang dibutuhkan sistem operasi Debian (basis dari php:8.3-cli)
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    postgresql-client \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dibutuhkan Laravel
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory ke folder aplikasi kita
WORKDIR /var/www/html

# Catatan: Kita tidak melakukan COPY atau composer install di sini karena
# saat development, folder lokal komputer kita akan dimounting langsung 
# ke dalam container (bisa dilihat di bagian "volumes" docker-compose.yml).
