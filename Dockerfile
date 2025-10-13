# Multi stage docker file for the Attendize application layer images

# Use official PHP-FPM image with Debian Bullseye
FROM php:8.1-fpm-bullseye as base

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libxrender1 \
    libgmp-dev \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip gmp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure nginx
RUN echo "daemon off;" >> /etc/nginx/nginx.conf

# Set up code
WORKDIR /usr/share/nginx/html
COPY . .

# Install composer dependencies first
RUN composer install --no-dev --optimize-autoloader --no-interaction

# run chmod files, setup laravel key
RUN chmod -R 755 storage bootstrap/cache \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# The worker container runs the laravel queue in the background
FROM base as worker

CMD ["php", "artisan", "queue:work", "--daemon"]

# The web container runs the HTTP server and connects to all other services in the application stack
FROM base as web

# nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Remove default nginx config
RUN rm -f /etc/nginx/sites-enabled/default

# self-signed ssl certificate for https support
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/nginx-selfsigned.key \
    -out /etc/ssl/certs/nginx-selfsigned.crt \
    -subj "/C=GB/ST=London/L=London/O=NA/CN=localhost" \
    && openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048 \
    && mkdir -p /etc/nginx/snippets

COPY self-signed.conf /etc/nginx/snippets/self-signed.conf
COPY ssl-params.conf /etc/nginx/snippets/ssl-params.conf

# Create startup script
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

# Ports to expose
EXPOSE 80
EXPOSE 443

# Starting nginx and php-fpm
CMD ["/start.sh"]