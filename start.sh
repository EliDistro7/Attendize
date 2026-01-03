#!/bin/bash
set -e

echo "Starting application initialization..."

# Ensure permissions are correct at runtime
echo "Setting permissions..."
chown -R www-data:www-data /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache
chmod -R 775 /usr/share/nginx/html/storage /usr/share/nginx/html/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
max_tries=10
count=0
until php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected';" 2>/dev/null || [ $count -eq $max_tries ]; do
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

# Inspect and insert timezone data
echo "Ensuring timezone data exists..."
php artisan tinker --execute="
  try {
    \$count = DB::table('timezones')->count();
    if (\$count === 0) {
      // Get actual column names
      \$columns = DB::getSchemaBuilder()->getColumnListing('timezones');
      echo 'Timezone columns: ' . implode(', ', \$columns) . PHP_EOL;
      
      // Insert with only existing columns
      DB::table('timezones')->insert([
        ['id' => 1, 'name' => 'UTC', 'location' => 'UTC'],
        ['id' => 2, 'name' => 'America/New_York', 'location' => 'America/New_York'],
        ['id' => 30, 'name' => 'Africa/Dar_es_Salaam', 'location' => 'Africa/Dar_es_Salaam'],
      ]);
      echo 'Timezones inserted successfully';
    } else {
      echo 'Timezones already exist (' . \$count . ' records)';
    }
  } catch (Exception \$e) {
    echo 'Timezone setup failed: ' . \$e->getMessage();
  }
" 2>/dev/null || echo "Timezone insert skipped"

# Inspect and insert currency data
echo "Ensuring currency data exists..."
php artisan tinker --execute="
  try {
    \$count = DB::table('currencies')->count();
    if (\$count === 0) {
      // Get actual column names
      \$columns = DB::getSchemaBuilder()->getColumnListing('currencies');
      echo 'Currency columns: ' . implode(', ', \$columns) . PHP_EOL;
      
      // Insert with only existing columns
      DB::table('currencies')->insert([
        ['id' => 1, 'code' => 'USD', 'title' => 'US Dollar'],
        ['id' => 2, 'code' => 'EUR', 'title' => 'Euro'],
        ['id' => 3, 'code' => 'GBP', 'title' => 'Pound Sterling'],
      ]);
      echo 'Currencies inserted successfully';
    } else {
      echo 'Currencies already exist (' . \$count . ' records)';
    }
  } catch (Exception \$e) {
    echo 'Currency setup failed: ' . \$e->getMessage();
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