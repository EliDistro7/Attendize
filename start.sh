#!/bin/bash
set -e

echo "Starting application initialization..."

# Ensure permissions are correct at runtime
echo "Setting permissions..."
chown -R www-data:www-data /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache
chmod -R 775 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
max_tries=30
count=0
until php artisan db:monitor --max-attempts=1 2>/dev/null || [ $count -eq $max_tries ]; do
  echo "Database not ready yet... waiting (attempt $((count+1))/$max_tries)"
  sleep 2
  count=$((count+1))
done

if [ $count -eq $max_tries ]; then
  echo "Warning: Could not verify database connection, proceeding anyway..."
fi

# Ensure app is not in maintenance mode
echo "Taking application out of maintenance mode..."
php artisan up || echo "App was not in maintenance mode"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Warning: Migrations failed or already run"

# Insert critical timezone data directly using SQL
echo "Ensuring timezone data exists..."
php artisan tinker --execute="
  if (DB::table('timezones')->count() === 0) {
    DB::table('timezones')->insert([
      ['id' => 1, 'name' => 'UTC', 'location' => 'UTC', 'diff_from_gtm' => '+00:00'],
      ['id' => 2, 'name' => 'America/New_York', 'location' => 'America/New_York', 'diff_from_gtm' => '-05:00'],
      ['id' => 30, 'name' => 'Africa/Dar_es_Salaam', 'location' => 'Africa/Dar_es_Salaam', 'diff_from_gtm' => '+03:00'],
    ]);
    echo 'Timezones inserted successfully';
  } else {
    echo 'Timezones already exist: ' . DB::table('timezones')->count();
  }
" 2>/dev/null || echo "Timezone insert failed, trying alternative..."

# Fallback: Try inserting just one timezone if the above fails
php artisan tinker --execute="
  try {
    if (DB::table('timezones')->where('id', 1)->doesntExist()) {
      DB::table('timezones')->insert(['id' => 1, 'name' => 'UTC', 'location' => 'UTC']);
      echo 'UTC timezone created';
    }
  } catch (Exception \$e) {
    echo 'Timezone already exists or error: ' . \$e->getMessage();
  }
" 2>/dev/null || true

# Insert currency data
echo "Ensuring currency data exists..."
php artisan tinker --execute="
  if (DB::table('currencies')->count() === 0) {
    DB::table('currencies')->insert([
      ['id' => 1, 'code' => 'USD', 'symbol' => '\$', 'name' => 'US Dollar'],
      ['id' => 2, 'code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
      ['id' => 3, 'code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
    ]);
    echo 'Currencies inserted successfully';
  } else {
    echo 'Currencies already exist: ' . DB::table('currencies')->count();
  }
" 2>/dev/null || true

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