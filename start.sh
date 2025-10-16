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

# Disable sql_require_primary_key for legacy migrations
echo "Configuring database settings..."
php artisan tinker --execute="
  try {
    DB::statement('SET GLOBAL sql_require_primary_key = 0');
    echo 'Primary key requirement disabled';
  } catch (Exception \$e) {
    echo 'Could not modify sql_require_primary_key (might not be needed): ' . \$e->getMessage();
  }
" 2>/dev/null || echo "Note: sql_require_primary_key setting skipped"

# Also try via direct MySQL connection if available
mysql -h\${DB_HOST:-db} -u\${DB_USERNAME:-root} -p\${DB_PASSWORD} -e "SET GLOBAL sql_require_primary_key = 0;" 2>/dev/null || true

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Warning: Migrations failed or already run"

# Insert critical timezone data - ensure ID 1 exists for foreign key
echo "Ensuring timezone data exists..."
php artisan tinker --execute="
  // Check schema first
  \$columns = Schema::getColumnListing('timezones');
  echo 'Timezone columns: ' . implode(', ', \$columns) . PHP_EOL;
  
  // Try to get existing timezone with id=1
  \$tz1 = DB::table('timezones')->where('id', 1)->first();
  
  if (!\$tz1) {
    echo 'Creating timezone with id=1...' . PHP_EOL;
    try {
      // Insert only the columns that exist
      \$data = ['id' => 1];
      
      if (in_array('name', \$columns)) \$data['name'] = 'UTC';
      if (in_array('location', \$columns)) \$data['location'] = 'UTC';
      if (in_array('diff_from_gtm', \$columns)) \$data['diff_from_gtm'] = '+00:00';
      if (in_array('diff_from_gmt', \$columns)) \$data['diff_from_gmt'] = '+00:00';
      
      DB::table('timezones')->insert(\$data);
      echo 'Timezone id=1 created successfully' . PHP_EOL;
    } catch (Exception \$e) {
      echo 'Error creating timezone: ' . \$e->getMessage() . PHP_EOL;
    }
  } else {
    echo 'Timezone id=1 already exists' . PHP_EOL;
  }
  
  // Also create id=2 and id=30 if they don't exist
  if (!DB::table('timezones')->where('id', 2)->exists()) {
    \$data = ['id' => 2];
    if (in_array('name', \$columns)) \$data['name'] = 'America/New_York';
    if (in_array('location', \$columns)) \$data['location'] = 'America/New_York';
    if (in_array('diff_from_gtm', \$columns)) \$data['diff_from_gtm'] = '-05:00';
    if (in_array('diff_from_gmt', \$columns)) \$data['diff_from_gmt'] = '-05:00';
    DB::table('timezones')->insert(\$data);
    echo 'Timezone id=2 created' . PHP_EOL;
  }
  
  if (!DB::table('timezones')->where('id', 30)->exists()) {
    \$data = ['id' => 30];
    if (in_array('name', \$columns)) \$data['name'] = 'Africa/Dar_es_Salaam';
    if (in_array('location', \$columns)) \$data['location'] = 'Africa/Dar_es_Salaam';
    if (in_array('diff_from_gtm', \$columns)) \$data['diff_from_gtm'] = '+03:00';
    if (in_array('diff_from_gmt', \$columns)) \$data['diff_from_gmt'] = '+03:00';
    DB::table('timezones')->insert(\$data);
    echo 'Timezone id=30 created' . PHP_EOL;
  }
  
  echo 'Total timezones: ' . DB::table('timezones')->count() . PHP_EOL;
" 2>/dev/null || echo "Warning: Could not ensure timezone data"

# Insert currency data
echo "Ensuring currency data exists..."
php artisan tinker --execute="
  if (DB::table('currencies')->count() === 0) {
    DB::table('currencies')->insert([
      ['id' => 1, 'code' => 'USD', 'symbol' => '\

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
],
      ['id' => 2, 'code' => 'EUR', 'symbol' => '€'],
      ['id' => 3, 'code' => 'GBP', 'symbol' => '£'],
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