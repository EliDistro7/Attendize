#!/bin/bash
set -e

echo "Starting application initialization..."

# ===== CRITICAL: Configure DNS FIRST before anything else =====
echo "Configuring DNS resolution..."
cat > /etc/resolv.conf << EOF
nameserver 8.8.8.8
nameserver 8.8.4.4
nameserver 1.1.1.1
options timeout:2 attempts:3 rotate
EOF

# Make resolv.conf immutable to prevent overwrites
chattr +i /etc/resolv.conf 2>/dev/null || echo "Note: Cannot make resolv.conf immutable"

# Wait for network to be available
echo "Waiting for network connectivity..."
max_network_tries=30
network_count=0
until ping -c 1 -W 2 8.8.8.8 >/dev/null 2>&1 || [ $network_count -eq $max_network_tries ]; do
  echo "Waiting for network... (attempt $((network_count+1))/$max_network_tries)"
  sleep 2
  network_count=$((network_count+1))
done

if [ $network_count -eq $max_network_tries ]; then
  echo "ERROR: Network connectivity timeout"
  exit 1
fi

echo "Network is ready!"

# Test database hostname resolution
echo "Testing database hostname resolution..."
if nslookup ${DB_HOST:-mysql-309e22d7-club-255.g.aivencloud.com} >/dev/null 2>&1; then
  echo "✓ Database hostname resolved successfully"
else
  echo "ERROR: Cannot resolve database hostname: ${DB_HOST}"
  echo "Attempting to get DNS info..."
  nslookup ${DB_HOST:-mysql-309e22d7-club-255.g.aivencloud.com} || true
  echo "This may be a temporary DNS issue. Attempting to continue..."
fi

# Ensure permissions are correct at runtime
echo "Setting permissions..."
chown -R www-data:www-data /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache
chmod -R 775 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
max_tries=15
count=0
until php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';" 2>/dev/null || [ $count -eq $max_tries ]; do
  echo "Database not ready yet... waiting (attempt $((count+1))/$max_tries)"
  sleep 3
  count=$((count+1))
done

if [ $count -eq $max_tries ]; then
  echo "ERROR: Database connection timeout"
  echo "Debug info:"
  echo "DB_HOST: ${DB_HOST}"
  echo "DB_PORT: ${DB_PORT}"
  echo "DB_DATABASE: ${DB_DATABASE}"
  exit 1
fi

echo "✓ Database connected successfully!"

# Ensure app is not in maintenance mode
echo "Taking application out of maintenance mode..."
php artisan up || echo "App was not in maintenance mode"

# Disable sql_require_primary_key for legacy migrations
echo "Configuring database settings..."
php artisan tinker --execute="
  try {
    DB::statement('SET GLOBAL sql_require_primary_key = 0');
    echo 'Primary key requirement disabled';
  } catch (Exception \$e) {
    echo 'Could not modify sql_require_primary_key: ' . \$e->getMessage();
  }
" 2>/dev/null || echo "Note: sql_require_primary_key setting skipped"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Warning: Migrations failed or already run"

# Seed timezone data using proper seeder
echo "Seeding timezone data..."
php artisan db:seed --class=TimezoneSeeder --force 2>/dev/null || echo "Timezone seeding skipped (may already exist)"

# Seed currency data using proper seeder
echo "Seeding currency data..."
php artisan db:seed --class=CurrencySeeder --force 2>/dev/null || echo "Currency seeding skipped (may already exist)"

# Clear all caches first to prevent stale config issues
echo "Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Run Laravel optimization with runtime environment
echo "Optimizing Laravel..."
php artisan config:cache || echo "Warning: Config cache failed"
php artisan route:cache || echo "Warning: Route cache failed"
php artisan view:cache || echo "Warning: View cache failed"

echo "Application initialization complete!"

# Start services
echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
nginx -g "daemon off;"