# Multi stage docker file for the Attendize application layer images

# Use official PHP-FPM image with Debian Bullseye - UPGRADED TO 8.3
FROM php:8.3-fpm-bullseye as base

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

# Set up code
WORKDIR /usr/share/nginx/html
COPY . .

# Install composer dependencies first
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=php --no-scripts

# FIXED: Set proper permissions for Laravel storage directories
# Create necessary directories if they don't exist
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

# Set ownership to www-data (default PHP-FPM user)
RUN chown -R www-data:www-data storage bootstrap/cache

# Set proper permissions (775 = owner+group can write, others can read)
RUN chmod -R 775 storage bootstrap/cache

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

# Configure nginx to run as www-data user
RUN sed -i 's/user\s*nginx;/user www-data;/' /etc/nginx/nginx.conf || \
    sed -i '1iuser www-data;' /etc/nginx/nginx.conf

# Create startup script that runs migrations, seeders, and cache commands with runtime .env
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting application initialization..."\n\
\n\
# Ensure permissions are correct at runtime\n\
echo "Setting permissions..."\n\
chown -R www-data:www-data /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache\n\
chmod -R 775 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database connection..."\n\
max_tries=30\n\
count=0\n\
until php artisan db:monitor --max-attempts=1 2>/dev/null || [ $count -eq $max_tries ]; do\n\
  echo "Database not ready yet... waiting (attempt $((count+1))/$max_tries)"\n\
  sleep 2\n\
  count=$((count+1))\n\
done\n\
\n\
if [ $count -eq $max_tries ]; then\n\
  echo "Warning: Could not verify database connection, proceeding anyway..."\n\
fi\n\
\n\
# Ensure app is not in maintenance mode\n\
echo "Taking application out of maintenance mode..."\n\
php artisan up || echo "App was not in maintenance mode"\n\
\n\
# Run migrations\n\
echo "Running database migrations..."\n\
php artisan migrate --force || echo "Warning: Migrations failed or already run"\n\
\n\
# Run seeders - CRITICAL for foreign key constraints\n\
echo "Seeding essential database tables..."\n\
echo "Seeding timezones..."\n\
php artisan db:seed --force --class=TimezonesTableSeeder || {\n\
  echo "ERROR: Timezone seeder failed! Attempting direct insert..."\n\
  php artisan tinker --execute="DB::table('\''timezones'\'')->insert(['\''id'\'' => 1, '\''name'\'' => '\''UTC'\'', '\''location'\'' => '\''UTC'\'']);" || true\n\
}\n\
\n\
echo "Seeding currencies..."\n\
php artisan db:seed --force --class=CurrenciesTableSeeder || echo "Warning: Currencies seeder failed"\n\
\n\
echo "Seeding countries..."\n\
php artisan db:seed --force --class=CountriesTableSeeder || echo "Warning: Countries seeder failed"\n\
\n\
# Verify critical data exists\n\
echo "Verifying timezone data..."\n\
php artisan tinker --execute="echo '\''Timezones in DB: '\'' . DB::table('\''timezones'\'')->count();" || true\n\
\n\
# Ensure app is not in maintenance mode\n\
echo "Taking application out of maintenance mode..."\n\
php artisan up 2>/dev/null || echo "App was not in maintenance mode"\n\
\n\
# Clear all caches first to prevent stale config issues\n\
echo "Clearing caches..."\n\
php artisan config:clear 2>/dev/null || true\n\
php artisan route:clear 2>/dev/null || true\n\
php artisan view:clear 2>/dev/null || true\n\
php artisan cache:clear 2>/dev/null || true\n\
\n\
# Run Laravel optimization with runtime environment\n\
echo "Optimizing Laravel..."\n\
php artisan config:cache || echo "Warning: Config cache failed"\n\
php artisan route:cache || echo "Warning: Route cache failed"\n\
php artisan view:cache || echo "Warning: View cache failed"\n\
\n\
echo "Application initialization complete!"\n\
\n\
# Start services\n\
echo "Starting PHP-FPM..."\n\
php-fpm -D\n\
\n\
echo "Starting Nginx..."\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

# Ports to expose
EXPOSE 80
EXPOSE 443

# Starting nginx and php-fpm
CMD ["/start.sh"]