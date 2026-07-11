# Stage 1: Build Frontend Assets
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

# Copy all application files so Tailwind v4 @source and Rolldown can scan templates and vendor imports correctly
COPY . .
RUN npm run build

# Stage 2: Production PHP-FPM & Nginx environment
FROM php:8.3-fpm-alpine AS app

# Install runtime utilities only
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    postgresql-client \
    tzdata \
    bash

# Install PHP extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache redis

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- DOCKER LAYER CACHING OPTIMIZATION ---
# Copy ONLY composer.json and composer.lock first so dependency downloads are cached permanently across builds
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-autoloader --no-scripts

# Configure Nginx & PHP & Supervisor
COPY docker/nginx/app.conf /etc/nginx/http.d/default.conf
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application files AFTER vendor is installed
COPY . .

# Copy built assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Generate optimized autoloader and set proper permissions
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy and setup entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
