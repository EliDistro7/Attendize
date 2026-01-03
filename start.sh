#!/bin/bash
set -e

echo "Starting application initialization on Render..."

# Render-specific: Don't override DNS, use platform DNS
echo "Using Render's DNS configuration..."

# Ensure permissions are correct at runtime
echo "Setting permissions..."
chown -R www-data:www-data /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache
chmod -R 775 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache

# Render-specific: Wait a bit for network to stabilize
echo "Waiting for Render network initialization..."
sleep 5

# Display connection info for debugging
echo "Database connection details:"
echo "  Host: ${DB_HOST:-not set}"
echo "  Port: ${DB_PORT:-not set}"
echo "  Database: ${DB_DATABASE:-not set}"
echo "  Username: ${DB_USERNAME:-not set}"

# Wait for database to be ready with increased timeout for Render
echo "Connecting to database (this may take longer on Render free tier)..."
max_tries=30
count=0
connection_success=false

while [ $count -lt $max_tries ]; do
  if php artisan tinker --execute="
    try {
      \$pdo = DB::connection()->getPdo();
      echo 'SUCCESS';
      exit(0);
    } catch (Exception \$e) {
      echo 'FAILED: ' . \$e->getMessage();
      exit(1);
    }
  " 2>&1 | grep -q "SUCCESS"; then
    connection_success=true
    break
  fi
  
  count=$((count+1))
  echo "  Attempt $count/$max_tries - Waiting for database..."
  sleep 5
done

if [ "$connection_success" = true ]; then
  echo "✓ Database connected successfully!"
else
  echo "⚠ WARNING: Could not connect to database after $max_tries attempts"
  echo "Common Render issues:"
  echo "  1. External database on free tier may be blocked"
  echo "  2. Check if Aiven IP is whitelisted in Render"
  echo "  3. Verify all DB_* environment variables are set correctly"
  echo "  4. Consider using Render's PostgreSQL instead of external MySQL"
  echo ""
  echo "App will start but may not function correctly without database."
fi

# Ensure app is not in maintenance mode
echo "Taking application out of maintenance mode..."
php artisan up 2>/dev/null || echo "App was not in maintenance mode"

# Skip database-dependent operations if connection failed
if [ "$connection_success" = true ]; then
  # Disable sql_require_primary_key for legacy migrations
  echo "Configuring database settings..."
  php artisan tinker --execute="
    try {
      DB::statement('SET GLOBAL sql_require_primary_key = 0');
      echo 'Primary key requirement disabled';
    } catch (Exception \$e) {
      echo 'Note: Could not modify sql_require_primary_key (may require SUPER privilege)';
    }
  " 2>/dev/null || true

  # Run migrations
  echo "Running database migrations..."
  php artisan migrate --force 2>&1 || echo "⚠ Migrations skipped or failed"

  # Seed timezone data
  echo "Seeding timezone data..."
  php artisan db:seed --class=TimezoneSeeder --force 2>&1 || echo "⚠ Timezone seeding skipped"

  # Seed currency data
  echo "Seeding currency data..."
  php artisan db:seed --class=CurrencySeeder --force 2>&1 || echo "⚠ Currency seeding skipped"
else
  echo "⚠ Skipping database operations due to connection failure"
fi

# Clear all caches
echo "Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Run Laravel optimization
echo "Optimizing Laravel..."
php artisan config:cache 2>&1 || echo "⚠ Config cache skipped"
php artisan route:cache 2>&1 || echo "⚠ Route cache skipped"
php artisan view:cache 2>&1 || echo "⚠ View cache skipped"

echo ""
echo "=========================================="
echo "Application initialization complete!"
echo "=========================================="
echo ""

# Start services
echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g "daemon off;"