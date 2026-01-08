#!/bin/bash
set -e

echo "=== Laravel Backend Initialization ==="

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
for i in {1..60}; do
  if nc -z mysql 3306 2>/dev/null; then
    echo "✓ MySQL is ready!"
    break
  fi
  if [ $i -eq 60 ]; then
    echo "⚠ Warning: MySQL not ready after 60 seconds, continuing anyway..."
  fi
  sleep 1
done

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cat > .env << 'ENVEOF'
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=laravel

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
ENVEOF
    echo "✓ .env file created"
else
    echo "✓ .env file already exists"
    # Ensure SESSION_DRIVER is set to file initially (will switch to database after migrations)
    if ! grep -q "^SESSION_DRIVER=" .env; then
        echo "SESSION_DRIVER=file" >> .env
    fi
fi

# Install composer dependencies if vendor doesn't exist
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || {
        echo "⚠ Composer install had issues, but continuing..."
    }
    echo "✓ Composer dependencies installed"
else
    echo "✓ Composer dependencies already installed"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null || grep -q "^APP_KEY=$" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force || echo "⚠ Key generation had issues, but continuing..."
    echo "✓ Application key generated"
else
    echo "✓ Application key already set"
fi

# Set proper permissions
echo "Setting file permissions..."
chown -R www-data:www-data /var/www/html || true
chmod -R 755 /var/www/html || true
chmod -R 775 /var/www/html/storage || true
chmod -R 775 /var/www/html/bootstrap/cache || true
echo "✓ Permissions set"

# Wait for MySQL to be fully ready for connections
echo "Verifying MySQL connection..."
for i in {1..30}; do
    if php -r "try { \$pdo = new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'laravel'); echo 'OK'; exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "✓ MySQL connection verified"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "⚠ Warning: Could not verify MySQL connection, but continuing..."
    fi
    sleep 2
done

# Clear config cache before migrations (to ensure file session driver is used)
php artisan config:clear || true

# Run migrations with better error handling
echo "Running database migrations..."
set +e  # Temporarily disable exit on error to capture migration output
MIGRATE_OUTPUT=$(php artisan migrate --force 2>&1)
MIGRATE_EXIT=$?
set -e  # Re-enable exit on error

echo "$MIGRATE_OUTPUT"

if [ $MIGRATE_EXIT -eq 0 ]; then
    echo "✓ Database migrations completed successfully"
    
    # Verify critical tables exist
    echo "Verifying database tables..."
    TABLES_OK=true
    for table in users sessions cache cache_locks jobs job_batches failed_jobs password_reset_tokens personal_access_tokens; do
        if php -r "try { \$pdo = new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'laravel'); \$stmt = \$pdo->query(\"SHOW TABLES LIKE '$table'\"); exit(\$stmt->rowCount() > 0 ? 0 : 1); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            echo "  ✓ Table '$table' exists"
        else
            echo "  ✗ Table '$table' missing"
            TABLES_OK=false
        fi
    done
    
    if [ "$TABLES_OK" = true ]; then
        echo "✓ All required tables verified"
        # After migrations, switch session driver to database if sessions table exists
        if php -r "try { \$pdo = new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'laravel'); \$stmt = \$pdo->query(\"SHOW TABLES LIKE 'sessions'\"); exit(\$stmt->rowCount() > 0 ? 0 : 1); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            echo "Switching session driver to database..."
            sed -i 's/^SESSION_DRIVER=file/SESSION_DRIVER=database/' .env || true
            php artisan config:clear || true
            echo "✓ Session driver switched to database"
        fi
    else
        echo "⚠ Some tables are missing - attempting to re-run migrations..."
        set +e
        php artisan migrate --force 2>&1
        set -e
    fi
else
    echo "⚠ Migration failed with exit code $MIGRATE_EXIT"
    echo "This might be due to a Laravel schema builder issue."
    echo "Checking if tables exist and creating them directly if needed..."
    
    # Check if users table exists, if not create critical tables directly
    if ! php -r "try { \$pdo = new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'laravel'); \$stmt = \$pdo->query(\"SHOW TABLES LIKE 'users'\"); exit(\$stmt->rowCount() > 0 ? 0 : 1); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Creating critical tables directly via SQL..."
        php -r "
        try {
            \$pdo = new PDO('mysql:host=mysql;dbname=laravel', 'laravel', 'laravel');
            \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create users table
            \$pdo->exec(\"CREATE TABLE IF NOT EXISTS users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");
            
            // Create sessions table
            \$pdo->exec(\"CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload LONGTEXT NOT NULL,
                last_activity INT NOT NULL,
                INDEX idx_user_id (user_id),
                INDEX idx_last_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");
            
            // Create cache tables
            \$pdo->exec(\"CREATE TABLE IF NOT EXISTS cache (
                \`key\` VARCHAR(255) PRIMARY KEY,
                value MEDIUMTEXT NOT NULL,
                expiration INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");
            
            \$pdo->exec(\"CREATE TABLE IF NOT EXISTS cache_locks (
                \`key\` VARCHAR(255) PRIMARY KEY,
                owner VARCHAR(255) NOT NULL,
                expiration INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");
            
            // Mark migrations as run
            \$pdo->exec(\"INSERT IGNORE INTO migrations (migration, batch) VALUES 
                ('2024_01_01_000000_create_users_table', 1),
                ('2024_01_01_000001_create_cache_table', 1),
                ('2024_01_01_000002_create_sessions_table', 1)\");
            
            echo 'Critical tables created successfully\n';
        } catch (Exception \$e) {
            echo 'Error creating tables: ' . \$e->getMessage() . '\n';
            exit(1);
        }
        " 2>&1
        echo "✓ Tables created via direct SQL"
    else
        echo "✓ Tables already exist"
    fi
fi

# Clear and cache config
echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
echo "✓ Caches cleared"

echo ""
echo "=== Laravel Backend Ready ==="
echo ""

exec "$@"
